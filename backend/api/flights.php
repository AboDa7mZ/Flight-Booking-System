<?php
/**
 * Flights API
 * Handles flight search and information retrieval
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");

include_once '../config/database.php';
include_once '../models/Flight.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$flight = new Flight($db);

$method = $_SERVER['REQUEST_METHOD'];

if($method == "GET") {
    // SEARCH FLIGHTS
    if(isset($_GET['action']) && $_GET['action'] == 'search') {
        $departure_airport_id = isset($_GET['from']) ? $_GET['from'] : null;
        $arrival_airport_id = isset($_GET['to']) ? $_GET['to'] : null;
        $departure_date = isset($_GET['date']) ? $_GET['date'] : null;
        $seat_class = isset($_GET['class']) ? $_GET['class'] : 'economy';
        $passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
        
        if($departure_airport_id && $arrival_airport_id && $departure_date) {
            $flights = $flight->searchFlights(
                $departure_airport_id,
                $arrival_airport_id,
                $departure_date,
                $seat_class,
                $passengers
            );
            
            if($flights) {
                http_response_code(200);
                echo json_encode(array(
                    "count" => count($flights),
                    "flights" => $flights
                ));
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "No flights found."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Missing search parameters."));
        }
    }
    
    // GET FLIGHT BY ID
    else if(isset($_GET['id'])) {
        $flight_id = $_GET['id'];
        
        try {
            // Query for single flight with company info
            $query = "SELECT 
                        f.flight_id,
                        f.flight_name,
                        f.flight_code,
                        f.fees,
                        f.max_passengers,
                        f.registered_passengers,
                        f.pending_passengers,
                        f.is_completed,
                        f.company_id,
                        u.name as company_name
                      FROM flights f
                      INNER JOIN users u ON f.company_id = u.user_id
                      WHERE f.flight_id = :flight_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':flight_id', $flight_id);
            $stmt->execute();
            
            $flight_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($flight_data) {
                // Get route from flight_itinerary
                $route_query = "SELECT city 
                               FROM flight_itinerary 
                               WHERE flight_id = :flight_id 
                               ORDER BY sequence_order";
                $route_stmt = $db->prepare($route_query);
                $route_stmt->bindParam(':flight_id', $flight_id);
                $route_stmt->execute();
                $cities = $route_stmt->fetchAll(PDO::FETCH_COLUMN);
                
                // Build route string
                $flight_data['route'] = implode(' - ', $cities);
                
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "flight" => $flight_data
                ));
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Flight not found."
                ));
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(array(
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ));
        }
    }
    
    // GET ALL FLIGHTS (Admin)
    else if(isset($_GET['action']) && $_GET['action'] == 'all') {
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
        
        $flights = $flight->getAllFlights($page, $limit);
        
        http_response_code(200);
        echo json_encode(array(
            "count" => count($flights),
            "page" => $page,
            "flights" => $flights
        ));
    }
    
    // GET POPULAR ROUTES
    else if(isset($_GET['action']) && $_GET['action'] == 'popular') {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $routes = $flight->getPopularRoutes($limit);
        
        http_response_code(200);
        echo json_encode(array(
            "count" => count($routes),
            "routes" => $routes
        ));
    }
    
    else {
        http_response_code(400);
        echo json_encode(array("message" => "Invalid request."));
    }
}

else if($method == "POST") {
    // CREATE FLIGHT (Admin only)
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->flight_number) && !empty($data->airline_id) && 
       !empty($data->departure_airport_id) && !empty($data->arrival_airport_id) &&
       !empty($data->departure_time) && !empty($data->arrival_time)) {
        
        $flight->flight_number = $data->flight_number;
        $flight->airline_id = $data->airline_id;
        $flight->aircraft_id = isset($data->aircraft_id) ? $data->aircraft_id : null;
        $flight->departure_airport_id = $data->departure_airport_id;
        $flight->arrival_airport_id = $data->arrival_airport_id;
        $flight->departure_time = $data->departure_time;
        $flight->arrival_time = $data->arrival_time;
        $flight->base_price_economy = $data->base_price_economy;
        $flight->base_price_business = $data->base_price_business;
        $flight->base_price_first_class = $data->base_price_first_class;
        $flight->available_seats_economy = $data->available_seats_economy;
        $flight->available_seats_business = $data->available_seats_business;
        $flight->available_seats_first_class = $data->available_seats_first_class;
        
        $new_flight_id = $flight->createFlight();
        
        if($new_flight_id) {
            http_response_code(201);
            echo json_encode(array(
                "message" => "Flight created successfully.",
                "flight_id" => $new_flight_id
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("message" => "Unable to create flight."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Incomplete data."));
    }
}

else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>
