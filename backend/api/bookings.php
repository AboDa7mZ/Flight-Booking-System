<?php
/**
 * Bookings API
 * Handles flight booking operations
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT");

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
    if(isset($_GET['action']) && $_GET['action'] == 'create') {
        if(!empty($data->user_id) && !empty($data->flight_id) && 
           !empty($data->number_of_passengers) && !empty($data->seat_class)) {
            
            $booking->user_id = $data->user_id;
            $booking->flight_id = $data->flight_id;
            $booking->number_of_passengers = $data->number_of_passengers;
            $booking->seat_class = $data->seat_class;
            $booking->total_amount = $data->total_amount;
            $booking->travel_date = $data->travel_date;
            $booking->special_requests = isset($data->special_requests) ? $data->special_requests : null;
            
            $booking_id = $booking->createBooking();
            
            if($booking_id) {
                http_response_code(201);
                echo json_encode(array(
                    "message" => "Booking created successfully.",
                    "booking_id" => $booking_id,
                    "booking_reference" => $booking->booking_reference
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Unable to create booking. Please check seat availability."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete booking data."));
        }
    }
    else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid action."));
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
