<?php
/**
 * Bookings API
 * Handles flight booking operations
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../config/database.php';
include_once '../models/Booking.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$booking = new Booking($db);

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

if($method == "GET") {
    // GET BOOKING BY ID
    if(isset($_GET['id'])) {
        $booking_id = $_GET['id'];
        $booking_data = $booking->getBookingById($booking_id);
        
        if($booking_data) {
            http_response_code(200);
            echo json_encode($booking_data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Booking not found."));
        }
    }
    
    // GET BOOKING BY REFERENCE
    else if(isset($_GET['reference'])) {
        $booking_reference = $_GET['reference'];
        $booking_data = $booking->getBookingByReference($booking_reference);
        
        if($booking_data) {
            http_response_code(200);
            echo json_encode($booking_data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Booking not found."));
        }
    }
    
    // GET USER BOOKINGS
    else if(isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        
        $bookings = $booking->getUserBookings($user_id, $page, $limit);
        
        http_response_code(200);
        echo json_encode(array(
            "count" => count($bookings),
            "page" => $page,
            "bookings" => $bookings
        ));
    }
    
    // GET BOOKING STATS (Admin)
    else if(isset($_GET['action']) && $_GET['action'] == 'stats') {
        $stats = $booking->getBookingStats();
        
        http_response_code(200);
        echo json_encode($stats);
    }
    
    else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid request."));
    }
}

else if($method == "POST") {
    // CREATE BOOKING
    if(!empty($data->passenger_id) && !empty($data->flight_id) && 
       !empty($data->payment_type) && !empty($data->amount_paid)) {
        
        try {
            // Start transaction
            $db->beginTransaction();
            
            // Insert booking
            $query = "INSERT INTO bookings 
                      (passenger_id, flight_id, booking_date, amount_paid, payment_type, status) 
                      VALUES 
                      (:passenger_id, :flight_id, NOW(), :amount_paid, :payment_type, 'pending')";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':passenger_id', $data->passenger_id);
            $stmt->bindParam(':flight_id', $data->flight_id);
            $stmt->bindParam(':amount_paid', $data->amount_paid);
            $stmt->bindParam(':payment_type', $data->payment_type);
            
            if($stmt->execute()) {
                $booking_id = $db->lastInsertId();
                
                // Get flight details including company_id
                $flightQuery = "SELECT company_id, fees FROM flights WHERE flight_id = :flight_id";
                $flightStmt = $db->prepare($flightQuery);
                $flightStmt->bindParam(':flight_id', $data->flight_id);
                $flightStmt->execute();
                $flight = $flightStmt->fetch(PDO::FETCH_ASSOC);
                
                // Update flight pending passengers count
                $updateQuery = "UPDATE flights 
                               SET pending_passengers = pending_passengers + 1 
                               WHERE flight_id = :flight_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':flight_id', $data->flight_id);
                $updateStmt->execute();
                
                // If payment from account, deduct balance from passenger
                if($data->payment_type === 'account') {
                    $balanceQuery = "UPDATE users 
                                    SET account_balance = account_balance - :amount 
                                    WHERE user_id = :user_id";
                    $balanceStmt = $db->prepare($balanceQuery);
                    $balanceStmt->bindParam(':amount', $data->amount_paid);
                    $balanceStmt->bindParam(':user_id', $data->passenger_id);
                    $balanceStmt->execute();
                }
                
                // Add money to company's account
                if($flight && isset($flight['company_id'])) {
                    $companyBalanceQuery = "UPDATE users 
                                           SET account_balance = account_balance + :amount 
                                           WHERE user_id = :company_id";
                    $companyBalanceStmt = $db->prepare($companyBalanceQuery);
                    $companyBalanceStmt->bindParam(':amount', $data->amount_paid);
                    $companyBalanceStmt->bindParam(':company_id', $flight['company_id']);
                    $companyBalanceStmt->execute();
                }
                
                $db->commit();
                
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Booking created successfully.",
                    "booking_id" => $booking_id
                ));
            } else {
                $db->rollBack();
                http_response_code(500);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Unable to create booking."
                ));
            }
        } catch(Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(array(
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ));
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Incomplete booking data."
        ));
    }
}

else if($method == "PUT") {
    // CANCEL BOOKING
    if(isset($_GET['action']) && $_GET['action'] == 'cancel') {
        if(!empty($data->booking_id) && !empty($data->user_id)) {
            if($booking->cancelBooking($data->booking_id, $data->user_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Booking cancelled successfully."));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Unable to cancel booking."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing booking_id or user_id."));
        }
    }
    
    // UPDATE PAYMENT STATUS
    else if(isset($_GET['action']) && $_GET['action'] == 'payment') {
        if(!empty($data->booking_id) && !empty($data->payment_status)) {
            if($booking->updatePaymentStatus($data->booking_id, $data->payment_status)) {
                http_response_code(200);
                echo json_encode(array("message" => "Payment status updated successfully."));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Unable to update payment status."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing required data."));
        }
    }
    
    // ACCEPT PENDING PASSENGER
    else if(isset($_GET['action']) && $_GET['action'] == 'accept') {
        if(!empty($data->booking_id)) {
            // Update booking status to registered
            $updateQuery = "UPDATE bookings SET status = 'registered' WHERE booking_id = :booking_id AND status = 'pending'";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bindParam(':booking_id', $data->booking_id);
            if($updateStmt->execute() && $updateStmt->rowCount() > 0) {
                // Update flight counts
                $flightUpdate = "UPDATE flights f
                                 JOIN bookings b ON f.flight_id = b.flight_id
                                 SET f.pending_passengers = f.pending_passengers - 1,
                                     f.registered_passengers = f.registered_passengers + 1
                                 WHERE b.booking_id = :booking_id";
                $flightStmt = $db->prepare($flightUpdate);
                $flightStmt->bindParam(':booking_id', $data->booking_id);
                $flightStmt->execute();
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "Passenger registered successfully."));
            } else {
                http_response_code(400);
                echo json_encode(array("success" => false, "message" => "Unable to register passenger. Maybe already registered?"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Missing booking_id."));
        }
    }
    
    else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid action."));
    }
}

else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>
