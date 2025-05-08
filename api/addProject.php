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
    $project = new Project();
    
    $project->title = $_POST['title'] ?? '';
    $project->client_name = $_POST['client_name'] ?? '';
    $project->description = $_POST['description'] ?? '';
    $project->status = $_POST['status'] ?? 'Pending';
    $project->start_date = $_POST['start_date'] ?? null;
    $project->end_date = $_POST['end_date'] ?? null;

    if(empty($project->title) || empty($project->client_name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    $result = $project->create();
    
    if($result) {
        echo json_encode(['success' => true, 'id' => $result]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create project']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 