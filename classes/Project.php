<?php
require_once __DIR__ . '/../config/database.php';

class Project {
    private $conn;
    private $table_name = "projects";

    public $id;
    public $title;
    public $client_name;
    public $status;
    public $start_date;
    public $end_date;
    public $description;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                (title, client_name, status, start_date, end_date, description) 
                VALUES 
                (:title, :client_name, :status, :start_date, :end_date, :description)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function read($id = null) {
        if($id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY start_date DESC";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET title = :title,
                    client_name = :client_name,
                    status = :status,
                    start_date = :start_date,
                    end_date = :end_date,
                    description = :description
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":description", $this->description);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . "
                WHERE title LIKE :keyword
                OR client_name LIKE :keyword
                OR description LIKE :keyword
                ORDER BY start_date DESC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function canEdit($user_role) {
        // Only admin can edit projects
        return $user_role === 'admin';
    }

    public function canDelete($user_role) {
        // Only admin can delete projects
        return $user_role === 'admin';
    }

    public function canCreate($user_role) {
        // Only admin can create projects
        return $user_role === 'admin';
    }

    public function getProjectsByUser($user_id) {
        $query = "SELECT DISTINCT p.* FROM " . $this->table_name . " p 
                  INNER JOIN tasks t ON p.id = t.project_id 
                  WHERE t.assigned_to = :user_id 
                  ORDER BY p.title";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalProjectsByUser($user_id) {
        $query = "SELECT COUNT(DISTINCT p.id) as total 
                FROM projects p 
                INNER JOIN tasks t ON p.id = t.project_id 
                WHERE t.assigned_to = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllProjects() {
        try {
            $sql = "SELECT * FROM projects ORDER BY title ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all projects: " . $e->getMessage());
            return [];
        }
    }
} 