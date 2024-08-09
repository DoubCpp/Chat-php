<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $message_content = $_POST['message_content'];

    function handleMentions($message_content) {
        preg_match_all('/@(\w+)/', $message_content, $matches);
        foreach ($matches[0] as $mention) {
            $message_content = str_replace($mention, '<span class="mention">' . $mention . '</span>', $message_content);
        }
        return [$matches[1], $message_content];
    }

    list($mentioned_users, $highlighted_message) = handleMentions($message_content);

    $sql = "INSERT INTO messages (user_id, message_content) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $highlighted_message);

    if ($stmt->execute()) {
        $message_id = $stmt->insert_id;

        foreach ($mentioned_users as $username) {
            $mentioned_user = getUserByUsername($username);
            if ($mentioned_user) {

                $sql = "INSERT INTO mentions (message_id, mentioned_user_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $message_id, $mentioned_user['id']);
                $stmt->execute();
            }
        }

        echo json_encode(['status' => 'success']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'envoi du message : ' . $conn->error]);
        exit();
    }
}
?>
