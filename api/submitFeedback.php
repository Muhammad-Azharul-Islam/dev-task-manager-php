<?php
require_once '../classes/Feedback.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback = new Feedback();
    
    $feedback->name = $_POST['name'] ?? '';
    $feedback->email = $_POST['email'] ?? '';
    $feedback->message = $_POST['message'] ?? '';
    $feedback->project_id = $_POST['project_id'] ?? null;

    if(empty($feedback->name) || empty($feedback->email) || empty($feedback->message) || empty($feedback->project_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    if(!filter_var($feedback->email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit();
    }

    $result = $feedback->create();
    
    if($result) {
        echo json_encode(['success' => true, 'id' => $result]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to submit feedback']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
} 