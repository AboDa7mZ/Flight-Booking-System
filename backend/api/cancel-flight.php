<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['flight_id']) || !isset($data['company_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$flight_id = $data['flight_id'];
$company_id = $data['company_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Verify flight belongs to company
    $verify_query = "SELECT flight_id, is_completed FROM flights WHERE flight_id = :flight_id AND company_id = :company_id";
    $verify_stmt = $db->prepare($verify_query);
    $verify_stmt->bindParam(':flight_id', $flight_id);
    $verify_stmt->bindParam(':company_id', $company_id);
    $verify_stmt->execute();
    $flight = $verify_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$flight) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Flight not found or you do not have permission']);
        exit;
    }
    
    if ($flight['is_completed'] == 1) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Flight is already completed/cancelled']);
        exit;
    }
    
    // Get all passengers who paid via account (pending or registered)
    $passengers_query = "SELECT b.passenger_id, b.amount_paid, b.payment_type
                        FROM bookings b
                        WHERE b.flight_id = :flight_id 
                        AND b.status IN ('pending', 'registered')
                        AND b.payment_type = 'account'";
    $passengers_stmt = $db->prepare($passengers_query);
    $passengers_stmt->bindParam(':flight_id', $flight_id);
    $passengers_stmt->execute();
    $passengers = $passengers_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $refunded_count = 0;
    
    // Refund each passenger
    foreach ($passengers as $passenger) {
        $refund_query = "UPDATE users 
                        SET account_balance = account_balance + :amount 
                        WHERE user_id = :passenger_id";
        $refund_stmt = $db->prepare($refund_query);
        $refund_stmt->bindParam(':amount', $passenger['amount_paid']);
        $refund_stmt->bindParam(':passenger_id', $passenger['passenger_id']);
        $refund_stmt->execute();
        $refunded_count++;
    }
    
    // Update all bookings to cancelled
    $cancel_bookings = "UPDATE bookings 
                       SET status = 'cancelled' 
                       WHERE flight_id = :flight_id 
                       AND status IN ('pending', 'registered')";
    $cancel_stmt = $db->prepare($cancel_bookings);
    $cancel_stmt->bindParam(':flight_id', $flight_id);
    $cancel_stmt->execute();
    
    // Mark flight as completed
    $complete_flight = "UPDATE flights 
                       SET is_completed = 1,
                           registered_passengers = 0,
                           pending_passengers = 0
                       WHERE flight_id = :flight_id";
    $complete_stmt = $db->prepare($complete_flight);
    $complete_stmt->bindParam(':flight_id', $flight_id);
    $complete_stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Flight cancelled successfully',
        'refunded_count' => $refunded_count
    ]);
    
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
