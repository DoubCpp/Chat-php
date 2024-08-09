<?php
require_once 'db.php';

function checkSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("Location: user/login.php");
        exit();
    }
}

function getUserById($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getAllUsers() {
    global $conn;
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateUserMuteStatus($user_id, $is_muted) {
    global $conn;
    $sql = "UPDATE users SET is_muted = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_muted, $user_id);
    return $stmt->execute();
}

function getMessages() {
    global $conn;
    $sql = "SELECT m.*, u.username, u.is_admin, u.gender FROM messages m INNER JOIN users u ON m.user_id = u.id ORDER BY m.created_at ASC";
    $result = $conn->query($sql);
    $messages = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    } else {
        error_log("Erreur SQL: " . $conn->error);
    }
    return $messages;
}

function addMessage($user_id, $message_content) {
    global $conn;
    $sql = "INSERT INTO messages (user_id, message_content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $message_content);
    return $stmt->execute();
}

function deleteMessage($message_id) {
    global $conn;
    $sql = "DELETE FROM messages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    return $stmt->execute();
}

function getPrivateMessages($user_id, $other_user_id) {
    global $conn;
    $sql = "SELECT pm.id, pm.message_content, pm.created_at, u.username FROM private_messages pm INNER JOIN users u ON pm.sender_id = u.id WHERE (pm.sender_id = ? AND pm.receiver_id = ?) OR (pm.sender_id = ? AND pm.receiver_id = ?) ORDER BY pm.created_at ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iiii", $user_id, $other_user_id, $other_user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'id' => $row['id'],
                'username' => htmlspecialchars($row['username']),
                'message_content' => htmlspecialchars($row['message_content']),
                'created_at' => $row['created_at']
            ];
        }
        $stmt->close();
        return $messages;
    } else {
        return ['error' => 'Database error: ' . $conn->error];
    }
}

function addPrivateMessage($sender_id, $receiver_id, $message_content) {
    global $conn;
    $sql = "INSERT INTO private_messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_content);
    return $stmt->execute();
}

function getUsername($user_id) {
    global $conn;
    $sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['username'];
    } else {
        return "Utilisateur inconnu";
    }
}

function getConversations($user_id) {
    global $conn;
    $sql = "SELECT DISTINCT u.id as user_id, u.username,
            (SELECT COUNT(*) FROM private_messages WHERE receiver_id = ? AND sender_id = u.id AND is_read = 0) AS unread_count
            FROM private_messages pm
            INNER JOIN users u ON (pm.sender_id = u.id OR pm.receiver_id = u.id)
            WHERE (pm.sender_id = ? OR pm.receiver_id = ?) AND u.id != ?
            GROUP BY u.id, u.username
            ORDER BY unread_count DESC"; 
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function markMessagesAsRead($user_id, $other_user_id) {
    global $conn;
    $sql = "UPDATE private_messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $other_user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function convertUrlsToLinks($text) {
    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    return preg_replace($reg_exUrl, "<a href='$0' target='_blank'>$0</a>", htmlspecialchars($text));
}


?>
