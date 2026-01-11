<?php
// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// CORS headers must be first
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['company_id']) || !isset($data['flight_name']) || !isset($data['flight_code']) || 
    !isset($data['fees']) || !isset($data['max_passengers']) || !isset($data['route'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$company_id = $data['company_id'];
$flight_name = trim($data['flight_name']);
$flight_code = trim($data['flight_code']);
$fees = floatval($data['fees']);
$max_passengers = intval($data['max_passengers']);
$route = $data['route'];

// Validate data
if (empty($flight_name) || empty($flight_code)) {
    echo json_encode(['success' => false, 'message' => 'Flight name and code cannot be empty']);
    exit;
}

if ($fees <= 0) {
    echo json_encode(['success' => false, 'message' => 'Fees must be greater than 0']);
    exit;
}

if ($max_passengers <= 0) {
    echo json_encode(['success' => false, 'message' => 'Maximum passengers must be greater than 0']);
    exit;
}

if (count($route) < 2) {
    echo json_encode(['success' => false, 'message' => 'Route must have at least 2 cities']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db === null) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Check if flight code already exists
    $check_query = "SELECT flight_id FROM flights WHERE flight_code = :flight_code";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':flight_code', $flight_code);
    $check_stmt->execute();
    
    if ($check_stmt->fetch()) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Flight code already exists']);
        exit;
    }
    
    // Insert flight
    $insert_flight = "INSERT INTO flights (company_id, flight_name, flight_code, fees, max_passengers, registered_passengers, pending_passengers, is_completed) 
                      VALUES (:company_id, :flight_name, :flight_code, :fees, :max_passengers, 0, 0, 0)";
    $flight_stmt = $db->prepare($insert_flight);
    $flight_stmt->bindParam(':company_id', $company_id);
    $flight_stmt->bindParam(':flight_name', $flight_name);
    $flight_stmt->bindParam(':flight_code', $flight_code);
    $flight_stmt->bindParam(':fees', $fees);
    $flight_stmt->bindParam(':max_passengers', $max_passengers);
    $flight_stmt->execute();
    
    $flight_id = $db->lastInsertId();
    
    // Insert route stops
    $insert_stop = "INSERT INTO flight_itinerary (flight_id, city, sequence_order, start_datetime, end_datetime) 
                    VALUES (:flight_id, :city, :sequence_order, :start_datetime, :end_datetime)";
    $stop_stmt = $db->prepare($insert_stop);
    
    foreach ($route as $stop) {
        if (!isset($stop['start_datetime']) || !isset($stop['end_datetime'])) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'Missing datetime for route stops']);
            exit;
        }
        
        $city = trim($stop['city']);
        $sequence = intval($stop['sequence_order']);
        $start_datetime = $stop['start_datetime'];
        $end_datetime = $stop['end_datetime'];
        
        $stop_stmt->bindParam(':flight_id', $flight_id);
        $stop_stmt->bindParam(':city', $city);
        $stop_stmt->bindParam(':sequence_order', $sequence);
        $stop_stmt->bindParam(':start_datetime', $start_datetime);
        $stop_stmt->bindParam(':end_datetime', $end_datetime);
        $stop_stmt->execute();
    }
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Flight created successfully',
        'flight_id' => $flight_id
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
