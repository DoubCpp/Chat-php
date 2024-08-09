<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; 
$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

markMessagesAsRead($user_id, $other_user_id);

$messages = getPrivateMessages($user_id, $other_user_id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat Privé</title>
    <link rel="stylesheet" href="../assets/css/chat.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/private.js"></script>
</head>
<body>
    
    <h1 id="top"><a class="arrow" href="user_list.php">⬅</a> Chat Privé avec <?php echo getUsername($other_user_id); ?></h1> 
        <?php foreach ($messages as $message): ?>
            <li><?php echo htmlspecialchars($message['username']) . ': ' . htmlspecialchars($message['message_content']); ?></li>
        <?php endforeach; ?>
    </ul>
    <form id="message-form" method="post">
        <textarea id="message_content" name="message_content" placeholder="Votre message ici" required></textarea>
        <button type="submit">Envoyer</button>
        <input type="hidden" id="other_user_id" name="other_user_id" value="<?php echo $other_user_id; ?>">
    </form>
    <p><a href="chat.php">Accéder à la messagerie</a></p>
</body>
</html>
