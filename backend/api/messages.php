<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if($method == "GET") {
        // Get conversations list
        if(isset($_GET['user_id']) && !isset($_GET['company_id'])) {
            $user_id = $_GET['user_id'];
            
            // First, check if user is company or passenger
            $userTypeQuery = "SELECT user_type FROM users WHERE user_id = :user_id";
            $userTypeStmt = $db->prepare($userTypeQuery);
            $userTypeStmt->bindParam(':user_id', $user_id);
            $userTypeStmt->execute();
            $userType = $userTypeStmt->fetchColumn();
            
            // Return conversations with the other party's info
            $query = "SELECT DISTINCT
                        CASE 
                            WHEN m.sender_id = :user_id THEN m.receiver_id 
                            ELSE m.sender_id 
                        END as other_user_id,
                        u.name as other_user_name,
                        u.user_type as other_user_type,
                        (SELECT message 
                         FROM messages 
                         WHERE (sender_id = :user_id2 AND receiver_id = other_user_id)
                            OR (sender_id = other_user_id AND receiver_id = :user_id3)
                         ORDER BY sent_at DESC 
                         LIMIT 1) as last_message
                      FROM messages m
                      INNER JOIN users u ON (
                        CASE 
                            WHEN m.sender_id = :user_id4 THEN m.receiver_id 
                            ELSE m.sender_id 
                        END = u.user_id
                      )
                      WHERE m.sender_id = :user_id5 OR m.receiver_id = :user_id6
                      GROUP BY other_user_id, u.name, u.user_type
                      ORDER BY (SELECT MAX(sent_at) 
                                FROM messages 
                                WHERE (sender_id = :user_id7 AND receiver_id = other_user_id)
                                   OR (sender_id = other_user_id AND receiver_id = :user_id8)) DESC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':user_id2', $user_id);
            $stmt->bindParam(':user_id3', $user_id);
            $stmt->bindParam(':user_id4', $user_id);
            $stmt->bindParam(':user_id5', $user_id);
            $stmt->bindParam(':user_id6', $user_id);
            $stmt->bindParam(':user_id7', $user_id);
            $stmt->bindParam(':user_id8', $user_id);
            $stmt->execute();
            
            $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format response based on user type
            $formattedConversations = array_map(function($conv) use ($userType) {
                if ($userType === 'company') {
                    return [
                        'passenger_id' => $conv['other_user_id'],
                        'passenger_name' => $conv['other_user_name'],
                        'last_message' => $conv['last_message']
                    ];
                } else {
                    return [
                        'company_id' => $conv['other_user_id'],
                        'company_name' => $conv['other_user_name'],
                        'last_message' => $conv['last_message']
                    ];
                }
            }, $conversations);
            
            echo json_encode([
                'success' => true,
                'conversations' => $formattedConversations
            ]);
        }
        // Get messages for specific conversation
        else if(isset($_GET['user_id']) && isset($_GET['company_id'])) {
            $user_id = $_GET['user_id'];
            $company_id = $_GET['company_id'];
            
            $query = "SELECT 
                        message_id,
                        sender_id,
                        receiver_id,
                        message,
                        sent_at as created_at
                      FROM messages
                      WHERE (sender_id = :user_id AND receiver_id = :company_id)
                         OR (sender_id = :company_id2 AND receiver_id = :user_id2)
                      ORDER BY sent_at ASC";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':company_id', $company_id);
            $stmt->bindParam(':user_id2', $user_id);
            $stmt->bindParam(':company_id2', $company_id);
            $stmt->execute();
            
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
        }
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required parameters'
            ]);
        }
    }
    else if($method == "POST") {
        // Send message
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->sender_id) && !empty($data->receiver_id) && !empty($data->message_text)) {
            $query = "INSERT INTO messages (sender_id, receiver_id, message, sent_at) 
                      VALUES (:sender_id, :receiver_id, :message, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':sender_id', $data->sender_id);
            $stmt->bindParam(':receiver_id', $data->receiver_id);
            $stmt->bindParam(':message', $data->message_text);
            
            if($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'message_id' => $db->lastInsertId()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send message'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Missing required data'
            ]);
        }
    }
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
