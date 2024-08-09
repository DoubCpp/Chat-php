<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
require_once '../includes/db.php';
require_once '../includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['is_18_or_over']) && (!isset($_COOKIE['is_18_or_over']) || $_COOKIE['is_18_or_over'] != 'true')) {
    header("Location: age_verif.php");
    exit();
} else if (isset($_COOKIE['is_18_or_over']) && $_COOKIE['is_18_or_over'] == 'true') {
    $_SESSION['is_18_or_over'] = true;
}

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    error_log("User session not found. Redirecting to login.php");
    header("Location: login.php");
    exit();
}

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['is_admin'] = $row['is_admin'];
    } else {
        error_log("Invalid user cookie. Redirecting to login.php");
        header("Location: login.php");
        exit();
    }
} else {
    $user_id = $_SESSION['user_id'];
}

error_log("User logged in: " . $user_id);

$user = getUserById($user_id); 
$messages = getMessages(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request received");
    if ($user['is_muted'] != 1) { 
        if (isset($_POST['message_content'])) {
            $message_content = trim($_POST['message_content']);

            error_log("User ID: " . $user_id);
            error_log("Message Content: " . $message_content);

            if (!isset($_SESSION['last_message_time']) || (time() - $_SESSION['last_message_time']) >= 2) {
                $_SESSION['last_message_time'] = time();

                if (!empty($message_content)) {
                    $sql = "INSERT INTO messages (user_id, message_content) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("is", $user_id, $message_content);

                        if ($stmt->execute()) {
                            error_log("Message sent successfully.");
                            echo json_encode(['status' => 'success']);
                        } else {
                            error_log("Error sending message: " . $stmt->error);
                            echo json_encode(['status' => 'error', 'message' => 'Error sending message: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        error_log("SQL query preparation failed: " . $conn->error);
                        echo json_encode(['status' => 'error', 'message' => 'SQL query preparation failed: ' . $conn->error]);
                    }
                } else {
                    error_log("Empty or invalid message.");
                    echo json_encode(['status' => 'error', 'message' => 'Empty or invalid message.']);
                }
                exit();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Vous devez attendre 2 secondes avant d\'envoyer un autre message.']);
                exit();
            }
        } else {
            error_log("message_content not set in POST.");
            echo json_encode(['status' => 'error', 'message' => 'Empty or invalid message.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Vous êtes muet et ne pouvez pas envoyer de message.']);
        exit();
    }
}

$unreadConversations = getConversations($user_id);
$hasUnread = false;  
foreach ($unreadConversations as $conversation) {
    if ($conversation['unread_count'] > 0) {
        $hasUnread = true;  
        break;
    }
}

$is_muted = $user['is_muted'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Douub.eu</title>
    <link rel="stylesheet" href="../assets/css/chat.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</head>
<body>
    <h1>Douub.eu</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>! <a href="logout.php">Déconnexion</a></p>

    <h2>Messagerie</h2>
    <div id="notifications"></div>
    <ul id="message-list"> </ul>

    <?php if ($is_muted != 1) : ?>
    <form id="message-form" method="post">
        <textarea id="message_content" name="message_content" placeholder="Ton message ici" required></textarea>
        <button type="submit">Envoyer</button>
    </form>
    <p>
    <a href="user_list.php">Accéder à la messagerie privée</a>
    <?php if ($hasUnread || !empty($mentions)): ?>
        <span style="color: red; font-size: 20px;">•</span>
    <?php endif; ?>
</p>
    <?php else : ?>
    <p id="mute-message">Vous êtes muet et ne pouvez pas envoyer de message.</p>
    <?php endif; ?>
</body>
</html>