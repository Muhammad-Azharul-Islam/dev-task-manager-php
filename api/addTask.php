<?php
session_start();
require_once '../classes/Task.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task = new Task();
    
    $task->project_id = $_POST['project_id'] ?? '';
    $task->task_desc = $_POST['task_desc'] ?? '';
    $task->assigned_to = $_POST['assigned_to'] ?? null;
    $task->status = $_POST['status'] ?? 'To-Do';

    if(empty($task->project_id) || empty($task->task_desc)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    $result = $task->create();
    
    if($result) {
        echo json_encode(['success' => true, 'id' => $result]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create task']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 