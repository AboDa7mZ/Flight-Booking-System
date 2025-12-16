<?php
/**
 * Search Flights API
 * Search for flights by departure and destination cities
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';

if (empty($from) || empty($to)) {
    echo json_encode([
        'success' => false,
        'message' => 'Both from and to cities are required'
    ]);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Search flights where route contains both cities
    $query = "SELECT f.*, 
              u.name as company_name,
              GROUP_CONCAT(i.city ORDER BY i.sequence_order SEPARATOR ' → ') as route
              FROM flights f
              INNER JOIN users u ON f.company_id = u.user_id
              LEFT JOIN flight_itinerary i ON f.flight_id = i.flight_id
              WHERE f.flight_id IN (
                  SELECT DISTINCT fi1.flight_id 
                  FROM flight_itinerary fi1
                  INNER JOIN flight_itinerary fi2 ON fi1.flight_id = fi2.flight_id
                  WHERE LOWER(fi1.city) LIKE LOWER(:from)
                  AND LOWER(fi2.city) LIKE LOWER(:to)
                  AND fi1.sequence_order < fi2.sequence_order
              )
              GROUP BY f.flight_id
              ORDER BY f.fees ASC";
    
    $stmt = $db->prepare($query);
    $from_pattern = '%' . $from . '%';
    $to_pattern = '%' . $to . '%';
    $stmt->bindParam(':from', $from_pattern);
    $stmt->bindParam(':to', $to_pattern);
    $stmt->execute();
    
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'flights' => $flights,
        'count' => count($flights),
        'search' => [
            'from' => $from,
            'to' => $to
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
