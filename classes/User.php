<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, role FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function create($username, $password, $email, $role = 'developer') {
        $query = "INSERT INTO " . $this->table_name . " (username, password, email, role) VALUES (:username, :password, :email, :role)";
        $stmt = $this->conn->prepare($query);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":role", $role);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT id, username, email, role FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getAllDevelopers() {
        $query = "SELECT id, username, email FROM " . $this->table_name . " WHERE role = 'developer' ORDER BY username ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getDevelopers() {
        try {
            $sql = "SELECT id, username, full_name, email FROM users WHERE role = 'developer' ORDER BY COALESCE(full_name, username) ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Developers retrieved: " . print_r($result, true));
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting developers: " . $e->getMessage());
            return [];
        }
    }

    public function updateProfile($user_id, $data) {
        $query = "UPDATE users SET 
                username = :username,
                email = :email,
                full_name = :full_name";
        
        if (isset($data['profile_picture'])) {
            $query .= ", profile_picture = :profile_picture";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":full_name", $data['full_name']);
        $stmt->bindParam(":id", $user_id);
        
        if (isset($data['profile_picture'])) {
            $stmt->bindParam(":profile_picture", $data['profile_picture']);
        }
        
        return $stmt->execute();
    }

    public function updatePassword($user_id, $current_password, $new_password) {
        // First verify current password
        $query = "SELECT password FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($current_password, $user['password'])) {
            return false;
        }
        
        // Update to new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $user_id);
        
        return $stmt->execute();
    }

    public function read($id) {
        try {
            $sql = "SELECT id, username, email, full_name, role, profile_picture FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                error_log("No user found with ID: " . $id);
                return false;
            }
            
            // Ensure all fields exist, even if null
            $result['full_name'] = $result['full_name'] ?? null;
            $result['profile_picture'] = $result['profile_picture'] ?? null;
            
            error_log("User data retrieved: " . print_r($result, true));
            return $result;
        } catch (PDOException $e) {
            error_log("Error reading user: " . $e->getMessage());
            return false;
        }
    }
} 