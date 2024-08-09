<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['is_muted' => 0]); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user = getUserById($user_id);

echo json_encode(['is_muted' => $user['is_muted']]);
?>
