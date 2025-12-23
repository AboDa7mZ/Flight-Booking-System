<?php
/**
 * Update User Profile API
 * Updates user profile information
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$user_id = $_POST['user_id'] ?? '';
$name = $_POST['name'] ?? '';

if (empty($user_id) || empty($name)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID and name are required'
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Handle photo upload for passengers
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/photos/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            $fileName = 'photo_' . $user_id . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $photoPath = 'uploads/photos/' . $fileName;
            }
        }
    }
    
    // Handle logo upload for companies
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/logos/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            $fileName = 'logo_' . $user_id . '_' . time() . '.' . $fileExtension;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $logoPath = 'uploads/logos/' . $fileName;
            }
        }
    }
    
    // Build update query dynamically based on provided fields
    $updateFields = ['name = :name'];
    $params = [':name' => $name, ':user_id' => $user_id];
    
    if (isset($_POST['tel'])) {
        $updateFields[] = 'tel = :tel';
        $params[':tel'] = $_POST['tel'];
    }
    
    if (isset($_POST['bio'])) {
        $updateFields[] = 'bio = :bio';
        $params[':bio'] = $_POST['bio'];
    }
    
    if (isset($_POST['address'])) {
        $updateFields[] = 'address = :address';
        $params[':address'] = $_POST['address'];
    }
    
    if ($photoPath) {
        $updateFields[] = 'photo = :photo';
        $params[':photo'] = $photoPath;
    }
    
    if ($logoPath) {
        $updateFields[] = 'logo_img = :logo_img';
        $params[':logo_img'] = $logoPath;
    }
    
    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = :user_id";
    
    $stmt = $db->prepare($query);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'logo' => $logoPath
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update profile'
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
