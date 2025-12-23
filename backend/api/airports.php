<?php
/**
 * Airports API
 * Handles airport data retrieval
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if($method == "GET") {
    // GET ALL AIRPORTS
    if(!isset($_GET['id'])) {
        $query = "SELECT airport_id, airport_code, airport_name, city, country 
                  FROM airports 
                  WHERE is_active = TRUE 
                  ORDER BY city, airport_name";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $airports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode(array(
            "count" => count($airports),
            "airports" => $airports
        ));
    }
    // GET AIRPORT BY ID
    else {
        $airport_id = $_GET['id'];
        
        $query = "SELECT * FROM airports WHERE airport_id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $airport_id);
        $stmt->execute();
        
        $airport = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($airport) {
            http_response_code(200);
            echo json_encode($airport);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Airport not found."));
        }
    }
}
else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>
