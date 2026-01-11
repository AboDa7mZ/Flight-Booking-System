<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get passenger ID from query parameter
    $passenger_id = isset($_GET['passenger_id']) ? $_GET['passenger_id'] : null;
    
    if (!$passenger_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Passenger ID is required'
        ]);
        exit();
    }
    
    // Query to get all bookings for the passenger with flight details
    $query = "SELECT 
                b.booking_id,
                b.passenger_id,
                b.flight_id,
                b.booking_date,
                b.amount_paid,
                b.payment_type,
                b.status,
                f.flight_name,
                f.flight_code,
                f.is_completed,
                f.company_id,
                f.max_passengers,
                f.registered_passengers,
                f.pending_passengers,
                u.name as company_name,
                (SELECT start_datetime FROM flight_itinerary WHERE flight_id = f.flight_id ORDER BY sequence_order ASC LIMIT 1) as departure_time,
                (SELECT end_datetime FROM flight_itinerary WHERE flight_id = f.flight_id ORDER BY sequence_order DESC LIMIT 1) as arrival_time
              FROM bookings b
              INNER JOIN flights f ON b.flight_id = f.flight_id
              INNER JOIN users u ON f.company_id = u.user_id
              WHERE b.passenger_id = :passenger_id
              ORDER BY b.booking_date DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':passenger_id', $passenger_id);
    $stmt->execute();
    
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get route for each booking
    foreach ($bookings as &$booking) {
        $route_query = "SELECT city 
                       FROM flight_itinerary 
                       WHERE flight_id = :flight_id 
                       ORDER BY sequence_order";
        $route_stmt = $db->prepare($route_query);
        $route_stmt->bindParam(':flight_id', $booking['flight_id']);
        $route_stmt->execute();
        $cities = $route_stmt->fetchAll(PDO::FETCH_COLUMN);
        $booking['route'] = implode(' - ', $cities);
    }
    
    echo json_encode([
        'success' => true,
        'bookings' => $bookings,
        'count' => count($bookings)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
