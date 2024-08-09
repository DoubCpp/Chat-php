<?php
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql_update = "UPDATE users SET is_muted = 1 WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $user_id);

    if ($stmt_update->execute()) {
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        echo "Erreur lors de la mise à jour de l'utilisateur : " . $stmt_update->error;
    }
} else {
    echo "ID d'utilisateur non spécifié.";
    exit();
}
?>
