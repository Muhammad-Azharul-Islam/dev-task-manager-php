<?php
session_start();
require_once '../classes/Project.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = $_POST['id'] ?? null;

    if(!$project_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Project ID is required']);
        exit();
    }

    $project = new Project();
    $result = $project->delete($project_id);
    
    if($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete project']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 