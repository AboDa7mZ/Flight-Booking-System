<?php
/**
 * Get Available Cities API
 * Returns unique cities from flight itineraries
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT DISTINCT city 
              FROM flight_itinerary 
              ORDER BY city ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $cities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cities[] = $row['city'];
    }
    
    echo json_encode([
        'success' => true,
        'cities' => $cities,
        'count' => count($cities)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
