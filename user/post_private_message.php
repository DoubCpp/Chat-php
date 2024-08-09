<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_POST['other_user_id']) ? intval($_POST['other_user_id']) : 0;
$message_content = isset($_POST['message_content']) ? trim($_POST['message_content']) : '';

if ($other_user_id == 0 || empty($message_content)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

if (addPrivateMessage($user_id, $other_user_id, $message_content)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message']);
}
?>
