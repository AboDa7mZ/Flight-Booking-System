<?php
/**
 * User Registration API
 * Handles registration for both Company and Passenger accounts
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required_fields = ['user_type', 'name', 'email', 'tel', 'password'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required field: ' . $field
        ]);
        exit();
    }
}

$user_type = $input['user_type'];
$name = trim($input['name']);
$email = trim($input['email']);
$tel = trim($input['tel']);
$password = $input['password'];

// Validate user type
if (!in_array($user_type, ['company', 'passenger'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user type. Must be company or passenger'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit();
}

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if email already exists
    $check_query = "SELECT user_id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered'
        ]);
        exit();
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Set initial account balance
    $initial_balance = ($user_type === 'company') ? 10000.00 : 500.00;
    
    // Insert new user
    $insert_query = "INSERT INTO users (user_type, name, email, password, tel, account_balance) 
                     VALUES (:user_type, :name, :email, :password, :tel, :account_balance)";
    
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':user_type', $user_type);
    $insert_stmt->bindParam(':name', $name);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':password', $password_hash);
    $insert_stmt->bindParam(':tel', $tel);
    $insert_stmt->bindParam(':account_balance', $initial_balance);
    
    if ($insert_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user_type' => $user_type
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
