<?php
/**
 * Company Flights API
 * Get all flights for a specific company
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$company_id = isset($_GET['company_id']) ? intval($_GET['company_id']) : 0;

if ($company_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Company ID required']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT f.*, 
              GROUP_CONCAT(CONCAT(i.city) ORDER BY i.sequence_order SEPARATOR ' → ') as route
              FROM flights f
              LEFT JOIN flight_itinerary i ON f.flight_id = i.flight_id
              WHERE f.company_id = :company_id
              GROUP BY f.flight_id
              ORDER BY f.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':company_id', $company_id);
    $stmt->execute();
    
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'flights' => $flights,
        'count' => count($flights)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
