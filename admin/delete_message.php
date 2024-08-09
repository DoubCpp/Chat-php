<?php
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];

    $sql_delete = "DELETE FROM messages WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $message_id);

    if ($stmt_delete->execute()) {
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        echo "Erreur lors de la suppression du message : " . $conn->error;
    }
} else {
    echo "ID de message non spécifié.";
    exit();
}
?>
