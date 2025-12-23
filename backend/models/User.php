<?php
/**
 * User Model
 * Flight Booking System
 */

class User {
    private $conn;
    private $table_name = "users";
    
    // Object properties
    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $password_hash;
    public $phone;
    public $date_of_birth;
    public $gender;
    public $passport_number;
    public $nationality;
    public $address;
    public $city;
    public $country;
    public $postal_code;
    public $user_role;
    public $account_status;
    public $created_at;
    public $updated_at;
    public $last_login;
    
    /**
     * Constructor
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Register new user
     */
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET first_name = :first_name,
                      last_name = :last_name,
                      email = :email,
                      password_hash = :password_hash,
                      phone = :phone,
                      date_of_birth = :date_of_birth,
                      gender = :gender,
                      nationality = :nationality,
                      user_role = 'customer'";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":nationality", $this->nationality);
        
        if($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Login user
     */
    public function login() {
        $query = "SELECT user_id, first_name, last_name, email, password_hash, 
                        user_role, account_status 
                  FROM " . $this->table_name . " 
                  WHERE email = :email AND account_status = 'active'
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row && password_verify($this->password, $row['password_hash'])) {
            $this->user_id = $row['user_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->user_role = $row['user_role'];
            
            // Update last login
            $this->updateLastLogin();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->gender = $row['gender'];
            $this->passport_number = $row['passport_number'];
            $this->nationality = $row['nationality'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->country = $row['country'];
            $this->postal_code = $row['postal_code'];
            $this->user_role = $row['user_role'];
            $this->account_status = $row['account_status'];
            $this->created_at = $row['created_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . " 
                  SET first_name = :first_name,
                      last_name = :last_name,
                      phone = :phone,
                      date_of_birth = :date_of_birth,
                      gender = :gender,
                      passport_number = :passport_number,
                      nationality = :nationality,
                      address = :address,
                      city = :city,
                      country = :country,
                      postal_code = :postal_code
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        
        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":passport_number", $this->passport_number);
        $stmt->bindParam(":nationality", $this->nationality);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":country", $this->country);
        $stmt->bindParam(":postal_code", $this->postal_code);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = NOW() 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
    }
    
    /**
     * Check if email exists
     */
    public function emailExists() {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE email = :email 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Change password
     */
    public function changePassword($old_password, $new_password) {
        // Verify old password
        $query = "SELECT password_hash FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!password_verify($old_password, $row['password_hash'])) {
            return false;
        }
        
        // Update password
        $query = "UPDATE " . $this->table_name . " 
                  SET password_hash = :password_hash 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password_hash", $new_password_hash);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Get all users (admin only)
     */
    public function getAllUsers($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT user_id, first_name, last_name, email, phone, 
                        user_role, account_status, created_at, last_login 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete user account
     */
    public function deleteAccount() {
        $query = "UPDATE " . $this->table_name . " 
                  SET account_status = 'inactive' 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        
        return $stmt->execute();
    }
}
?>
