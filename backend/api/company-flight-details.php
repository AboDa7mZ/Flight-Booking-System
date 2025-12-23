<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_GET['flight_id'])) {
    echo json_encode(['success' => false, 'message' => 'Flight ID is required']);
    exit;
}

$flight_id = $_GET['flight_id'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Get flight details
    $flight_query = "SELECT f.*, u.name as company_name
                     FROM flights f
                     INNER JOIN users u ON f.company_id = u.user_id
                     WHERE f.flight_id = :flight_id";
    $flight_stmt = $db->prepare($flight_query);
    $flight_stmt->bindParam(':flight_id', $flight_id);
    $flight_stmt->execute();
    
    $flight = $flight_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$flight) {
        echo json_encode(['success' => false, 'message' => 'Flight not found']);
        exit;
    }
    
    // Get route
    $route_query = "SELECT city 
                   FROM flight_itinerary 
                   WHERE flight_id = :flight_id 
                   ORDER BY sequence_order";
    $route_stmt = $db->prepare($route_query);
    $route_stmt->bindParam(':flight_id', $flight_id);
    $route_stmt->execute();
    $cities = $route_stmt->fetchAll(PDO::FETCH_COLUMN);
    $flight['route'] = implode(' - ', $cities);
    
    // Get pending passengers
    $pending_query = "SELECT b.*, u.name, u.email
                      FROM bookings b
                      INNER JOIN users u ON b.passenger_id = u.user_id
                      WHERE b.flight_id = :flight_id AND b.status = 'pending'
                      ORDER BY b.booking_date DESC";
    $pending_stmt = $db->prepare($pending_query);
    $pending_stmt->bindParam(':flight_id', $flight_id);
    $pending_stmt->execute();
    $flight['pending_passengers_list'] = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get registered passengers
    $registered_query = "SELECT b.*, u.name, u.email
                         FROM bookings b
                         INNER JOIN users u ON b.passenger_id = u.user_id
                         WHERE b.flight_id = :flight_id AND b.status = 'registered'
                         ORDER BY b.booking_date DESC";
    $registered_stmt = $db->prepare($registered_query);
    $registered_stmt->bindParam(':flight_id', $flight_id);
    $registered_stmt->execute();
    $flight['registered_passengers_list'] = $registered_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'flight' => $flight
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
