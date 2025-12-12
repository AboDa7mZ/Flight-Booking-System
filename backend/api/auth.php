<?php
/**
 * Authentication API
 * Handles user login and registration
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$user = new User($db);

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if($method == "POST") {
    // Check which action
    if(isset($_GET['action'])) {
        $action = $_GET['action'];
        
        // REGISTER
        if($action == "register") {
            // Validate input
            if(!empty($data->first_name) && !empty($data->last_name) && 
               !empty($data->email) && !empty($data->password)) {
                
                // Check if email already exists
                $user->email = $data->email;
                if($user->emailExists()) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Email already exists."));
                    exit();
                }
                
                // Validate password strength
                if(strlen($data->password) < 8) {
                    http_response_code(400);
                    echo json_encode(array("message" => "Password must be at least 8 characters long."));
                    exit();
                }
                
                // Set user properties
                $user->first_name = $data->first_name;
                $user->last_name = $data->last_name;
                $user->email = $data->email;
                $user->password = $data->password;
                $user->phone = isset($data->phone) ? $data->phone : null;
                $user->date_of_birth = isset($data->date_of_birth) ? $data->date_of_birth : null;
                $user->gender = isset($data->gender) ? $data->gender : null;
                $user->nationality = isset($data->nationality) ? $data->nationality : null;
                
                // Create user
                if($user->register()) {
                    http_response_code(201);
                    echo json_encode(array(
                        "message" => "User registered successfully.",
                        "user_id" => $user->user_id,
                        "email" => $user->email
                    ));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Unable to register user."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Incomplete data. Please provide all required fields."));
            }
        }
        
        // LOGIN
        else if($action == "login") {
            if(!empty($data->email) && !empty($data->password)) {
                $user->email = $data->email;
                $user->password = $data->password;
                
                if($user->login()) {
                    // Generate session token (simple implementation)
                    $session_token = bin2hex(random_bytes(32));
                    
                    // Store session in database
                    $session_query = "INSERT INTO user_sessions 
                                    (user_id, session_token, ip_address, expires_at, is_active) 
                                    VALUES (:user_id, :session_token, :ip_address, 
                                            DATE_ADD(NOW(), INTERVAL 24 HOUR), TRUE)";
                    $session_stmt = $db->prepare($session_query);
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $session_stmt->bindParam(":user_id", $user->user_id);
                    $session_stmt->bindParam(":session_token", $session_token);
                    $session_stmt->bindParam(":ip_address", $ip_address);
                    $session_stmt->execute();
                    
                    http_response_code(200);
                    echo json_encode(array(
                        "message" => "Login successful.",
                        "user_id" => $user->user_id,
                        "first_name" => $user->first_name,
                        "last_name" => $user->last_name,
                        "email" => $user->email,
                        "user_role" => $user->user_role,
                        "token" => $session_token
                    ));
                } else {
                    http_response_code(401);
                    echo json_encode(array("message" => "Invalid email or password."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Please provide email and password."));
            }
        }
        
        // LOGOUT
        else if($action == "logout") {
            $headers = getallheaders();
            if(isset($headers['Authorization'])) {
                $token = str_replace('Bearer ', '', $headers['Authorization']);
                
                $query = "UPDATE user_sessions SET is_active = FALSE 
                         WHERE session_token = :token";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":token", $token);
                
                if($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(array("message" => "Logout successful."));
                } else {
                    http_response_code(500);
                    echo json_encode(array("message" => "Logout failed."));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "No authorization token provided."));
            }
        }
        
        else {
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "No action specified."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method not allowed."));
}
?>
