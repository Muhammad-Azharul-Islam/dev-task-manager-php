<?php
require_once __DIR__ . '/../config/database.php';

class Feedback {
    private $conn;
    private $table_name = "feedback";

    public $id;
    public $name;
    public $email;
    public $message;
    public $status;
    public $created_at;
    public $project_id;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                (name, email, message, status, created_at, project_id)
                VALUES
                (:name, :email, :message, 'New', NOW(), :project_id)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":project_id", $this->project_id);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function read($id = null) {
        if($id) {
            $query = "SELECT f.*, p.title as project_title 
                    FROM " . $this->table_name . " f
                    LEFT JOIN projects p ON f.project_id = p.id
                    WHERE f.id = :id
                    LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
        } else {
            $query = "SELECT f.*, p.title as project_title 
                    FROM " . $this->table_name . " f
                    LEFT JOIN projects p ON f.project_id = p.id
                    ORDER BY f.created_at DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function getFeedbackByProject($project_id) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE project_id = :project_id
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getUnreadCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
                WHERE status = 'New'";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row['count'];
    }

    public function getRecentFeedback($limit = 5) {
        $query = "SELECT f.*, p.title as project_title 
                FROM " . $this->table_name . " f
                LEFT JOIN projects p ON f.project_id = p.id
                ORDER BY f.created_at DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
} 