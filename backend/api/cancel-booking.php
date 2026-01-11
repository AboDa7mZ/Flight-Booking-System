<?php
/**
 * Cancel Booking API - Passenger cancels their own booking
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    http_response_code(500);
    echo json_encode(array("success" => false, "message" => "Database connection failed."));
    exit();
}

// Only handle POST requests
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array("success" => false, "message" => "Method not allowed. Use POST."));
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
if(empty($data->booking_id) || empty($data->user_id)) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false, 
        "message" => "Missing required fields: booking_id and user_id are required."
    ));
    exit();
}

$booking_id = intval($data->booking_id);
$user_id = intval($data->user_id);

try {
    // Start transaction
    $db->beginTransaction();
    
    // Get booking details
    $query = "SELECT b.*, f.flight_id 
              FROM bookings b
              JOIN flights f ON b.flight_id = f.flight_id
              WHERE b.booking_id = :booking_id 
              AND b.passenger_id = :user_id 
              AND b.status != 'cancelled'";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':booking_id', $booking_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$booking) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(array(
            "success" => false, 
            "message" => "Booking not found or already cancelled."
        ));
        exit();
    }
    
    // Update booking status to cancelled
    $updateBooking = "UPDATE bookings 
                      SET status = 'cancelled' 
                      WHERE booking_id = :booking_id";
    
    $updateStmt = $db->prepare($updateBooking);
    $updateStmt->bindParam(':booking_id', $booking_id);
    
    if(!$updateStmt->execute()) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array(
            "success" => false, 
            "message" => "Failed to cancel booking."
        ));
        exit();
    }
    
    // Update flight pending passengers count
    $updateFlight = "UPDATE flights 
                     SET pending_passengers = GREATEST(pending_passengers - 1, 0)
                     WHERE flight_id = :flight_id";
    
    $flightStmt = $db->prepare($updateFlight);
    $flightStmt->bindParam(':flight_id', $booking['flight_id']);
    $flightStmt->execute();
    
    // Refund amount if paid from account
    $refund_amount = floatval($booking['amount_paid']);
    $new_balance = null;
    
    if($booking['payment_type'] === 'account' && $refund_amount > 0) {
        $updateBalance = "UPDATE users 
                         SET account_balance = account_balance + :amount 
                         WHERE user_id = :user_id";
        
        $balanceStmt = $db->prepare($updateBalance);
        $balanceStmt->bindParam(':amount', $refund_amount);
        $balanceStmt->bindParam(':user_id', $user_id);
        $balanceStmt->execute();
        
        // Get new balance
        $getBalance = "SELECT account_balance FROM users WHERE user_id = :user_id";
        $balStmt = $db->prepare($getBalance);
        $balStmt->bindParam(':user_id', $user_id);
        $balStmt->execute();
        $result = $balStmt->fetch(PDO::FETCH_ASSOC);
        $new_balance = $result['account_balance'];
    }
    
    // Commit transaction
    $db->commit();
    
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "message" => "Booking cancelled successfully.",
        "refund_amount" => $refund_amount,
        "new_balance" => $new_balance
    ));
    
} catch(Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ));
}
?>
