<?php
require_once __DIR__ . '/../config/database.php';

class Task {
    private $conn;
    private $table_name = "tasks";

    public $id;
    public $project_id;
    public $task_desc;
    public $assigned_to;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                (project_id, task_desc, assigned_to, status, created_at, updated_at) 
                VALUES 
                (:project_id, :task_desc, :assigned_to, :status, NOW(), NOW())";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":project_id", $data['project_id']);
        $stmt->bindParam(":task_desc", $data['task_desc']);
        $stmt->bindParam(":assigned_to", $data['assigned_to']);
        $stmt->bindParam(":status", $data['status']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read($id = null) {
        if($id) {
            $query = "SELECT t.*, u.username as assigned_to_name, p.title as project_title 
                    FROM " . $this->table_name . " t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    LEFT JOIN projects p ON t.project_id = p.id
                    WHERE t.id = :id";
            
            // If user is not admin, only show their own tasks
            if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
                $query .= " AND t.assigned_to = :user_id";
            }
            
            $query .= " LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
            }
        } else {
            $query = "SELECT t.*, u.username as assigned_to_name, p.title as project_title 
                    FROM " . $this->table_name . " t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    LEFT JOIN projects p ON t.project_id = p.id";
            
            // If user is not admin, only show their own tasks
            if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
                $query .= " WHERE t.assigned_to = :user_id";
            }
            
            $query .= " ORDER BY t.created_at DESC";
            $stmt = $this->conn->prepare($query);
            
            if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
                $stmt->bindParam(":user_id", $_SESSION['user_id']);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET project_id = :project_id,
                    task_desc = :task_desc,
                    assigned_to = :assigned_to,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":project_id", $this->project_id);
        $stmt->bindParam(":task_desc", $this->task_desc);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);

        return $stmt->execute();
    }

    public function assignTask($id, $user_id) {
        $query = "UPDATE " . $this->table_name . "
                SET assigned_to = :user_id,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getTasksByUser($user_id, $project_filter = '', $status_filter = '') {
        try {
            $sql = "SELECT t.*, p.title as project_title, u.username as assigned_to_name 
                    FROM tasks t 
                    LEFT JOIN projects p ON t.project_id = p.id 
                    LEFT JOIN users u ON t.assigned_to = u.id 
                    WHERE t.assigned_to = :user_id";
            
            $params = [':user_id' => $user_id];
            $conditions = [];

            if (!empty($project_filter)) {
                $conditions[] = "t.project_id = :project_id";
                $params[':project_id'] = $project_filter;
            }

            if (!empty($status_filter)) {
                $conditions[] = "t.status = :status";
                $params[':status'] = $status_filter;
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY t.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("User tasks query result for user $user_id: " . print_r($result, true));
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting tasks by user: " . $e->getMessage());
            return [];
        }
    }

    public function getTasksByProject($project_id) {
        $query = "SELECT t.*, u.username as assigned_to_name, p.title as project_title 
                FROM " . $this->table_name . " t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.project_id = :project_id";
        
        // If user is not admin, only show their own tasks
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
            $query .= " AND t.assigned_to = :user_id";
        }
        
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTasksByStatus($status) {
        $query = "SELECT t.*, u.username as assigned_to_name, p.title as project_title 
                FROM " . $this->table_name . " t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.status = :status";
        
        // If user is not admin, only show their own tasks
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
            $query .= " AND t.assigned_to = :user_id";
        }
        
        $query .= " ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        
        if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
            $stmt->bindParam(":user_id", $_SESSION['user_id']);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTasksByUserAndStatus($user_id, $status) {
        $query = "SELECT t.*, u.username as assigned_to_name, p.title as project_title 
                FROM " . $this->table_name . " t
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN projects p ON t.project_id = p.id
                WHERE t.assigned_to = :user_id AND t.status = :status
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function canEditTask($user_id, $user_role) {
        // Admin can edit any task
        if ($user_role === 'admin') {
            return true;
        }
        
        // Developer can only edit their own tasks
        $task = $this->read($this->id);
        if (empty($task)) {
            return false;
        }
        return $task[0]['assigned_to'] == $user_id;
    }

    public function canDeleteTask($user_id, $user_role) {
        // Only admin can delete tasks
        return $user_role === 'admin';
    }

    public function canAssignTask($user_role) {
        // Only admin can assign tasks
        return $user_role === 'admin';
    }

    public function getTasksByStatusAndProject($status, $project_id) {
        $sql = "SELECT t.*, p.title as project_title, u.username as assigned_to_name 
                FROM tasks t 
                LEFT JOIN projects p ON t.project_id = p.id 
                LEFT JOIN users u ON t.assigned_to = u.id 
                WHERE t.status = ? AND t.project_id = ?
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$status, $project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksByUserStatusAndProject($user_id, $status, $project_id) {
        $sql = "SELECT t.*, p.title as project_title, u.username as assigned_to_name 
                FROM tasks t 
                LEFT JOIN projects p ON t.project_id = p.id 
                LEFT JOIN users u ON t.assigned_to = u.id 
                WHERE t.assigned_to = ? AND t.status = ? AND t.project_id = ?
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $status, $project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTasksByUserAndProject($user_id, $project_id) {
        $sql = "SELECT t.*, p.title as project_title, u.username as assigned_to_name 
                FROM tasks t 
                LEFT JOIN projects p ON t.project_id = p.id 
                LEFT JOIN users u ON t.assigned_to = u.id 
                WHERE t.assigned_to = ? AND t.project_id = ?
                ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id, $project_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalTasksByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE assigned_to = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getCompletedTasksByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE assigned_to = :user_id AND status = 'Done'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getInProgressTasksByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE assigned_to = :user_id AND status = 'In Progress'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getTodoTasksByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE assigned_to = :user_id AND status = 'To-Do'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getAllTasks($project_filter = '', $status_filter = '', $developer_filter = '') {
        try {
            $sql = "SELECT t.*, p.title as project_title, u.username as assigned_to_name 
                    FROM tasks t 
                    LEFT JOIN projects p ON t.project_id = p.id 
                    LEFT JOIN users u ON t.assigned_to = u.id";
            
            $params = [];
            $conditions = [];

            if (!empty($project_filter)) {
                $conditions[] = "t.project_id = :project_id";
                $params[':project_id'] = $project_filter;
            }

            if (!empty($status_filter)) {
                $conditions[] = "t.status = :status";
                $params[':status'] = $status_filter;
            }

            if (!empty($developer_filter)) {
                $conditions[] = "t.assigned_to = :developer_id";
                $params[':developer_id'] = $developer_filter;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY t.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("All tasks query result: " . print_r($result, true));
            return $result;
        } catch (PDOException $e) {
            error_log("Error getting all tasks: " . $e->getMessage());
            return [];
        }
    }
} 