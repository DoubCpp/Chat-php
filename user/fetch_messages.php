<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../includes/db.php';
require_once '../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$user_id = $_SESSION['user_id'];
$messages = getMessages(); 

foreach ($messages as $message) {
    $userClass = $message['is_admin'] == 1 ? 'admin-user' : '';
    $genderClass = '';
    if (!empty($message['gender'])) {
        $genderClass = ($message['gender'] == 'homme') ? 'male' : (($message['gender'] == 'femme') ? 'female' : '');
    }
    $mentioned = strpos($message['message_content'], '@' . $_SESSION['username']) !== false;
    $messageContent = $mentioned ? preg_replace('/@' . $_SESSION['username'] . '/', '<span class="mention">@' . $_SESSION['username'] . '</span>', $message['message_content']) : $message['message_content'];
    $messageContent = convertUrlsToLinks($messageContent); 

    echo '<li>';
    echo '<strong class="' . htmlspecialchars($userClass . ' ' . $genderClass) . '">' . htmlspecialchars($message['username']) . ':</strong> '; 
    echo nl2br($messageContent);
    echo '<br>';
    echo '<small>' . date("d/m/Y H:i:s", strtotime($message['created_at'])) . '</small>';
    echo '</li>';
}
?>
