<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../user/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    $sql_delete_user = "DELETE FROM users WHERE id = ?";
    $stmt_delete_user = $conn->prepare($sql_delete_user);

    if ($stmt_delete_user === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }

    $stmt_delete_user->bind_param("i", $user_id);

    if ($stmt_delete_user->execute()) {
        echo "Utilisateur supprimé avec succès.";
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        echo "Erreur lors de la suppression de l'utilisateur : " . $stmt_delete_user->error;
    }
} else {
    header("Location: ../admin/dashboard.php");
    exit();
}
?>
