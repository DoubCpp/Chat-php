<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

if (!isset($_SESSION['user_id']) || !isset($_GET['other_user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = intval($_GET['other_user_id']);

$messages = getPrivateMessages($user_id, $other_user_id);

echo json_encode($messages);
?>
