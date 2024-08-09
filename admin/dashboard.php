<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../user/login.php");
    exit();
}

$sql_messages = "SELECT * FROM messages";
$result_messages = $conn->query($sql_messages);

if ($result_messages === false) {
    die("Erreur SQL lors de la récupération des messages : " . $conn->error);
}

$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

if ($result_users === false) {
    die("Erreur SQL lors de la récupération des utilisateurs : " . $conn->error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'mute' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        $sql_mute_user = "UPDATE users SET is_muted = 1 WHERE id = ?";
        $stmt_mute_user = $conn->prepare($sql_mute_user);

        if ($stmt_mute_user === false) {
            die("Erreur de préparation de la requête : " . $conn->error);
        }
        
        $stmt_mute_user->bind_param("i", $user_id);

        if ($stmt_mute_user->execute()) {
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            echo "Erreur lors du mutage de l'utilisateur : " . $stmt_mute_user->error;
        }
    } elseif ($_POST['action'] == 'delete_user' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];

        $sql_delete_messages = "DELETE FROM messages WHERE user_id = ?";
        $stmt_delete_messages = $conn->prepare($sql_delete_messages);

        if ($stmt_delete_messages === false) {
            die("Erreur de préparation de la requête : " . $conn->error);
        }
        
        $stmt_delete_messages->bind_param("i", $user_id);

        if ($stmt_delete_messages->execute()) {
            $sql_delete_user = "DELETE FROM users WHERE id = ?";
            $stmt_delete_user = $conn->prepare($sql_delete_user);
            
            if ($stmt_delete_user === false) {
                die("Erreur de préparation de la requête : " . $conn->error);
            }
            
            $stmt_delete_user->bind_param("i", $user_id);
    
            if ($stmt_delete_user->execute()) {
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                echo "Erreur lors de la suppression de l'utilisateur : " . $stmt_delete_user->error;
            }
        } else {
            echo "Erreur lors de la suppression des messages de l'utilisateur : " . $stmt_delete_messages->error;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'unmute' && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql_unmute_user = "UPDATE users SET is_muted = 0 WHERE id = ?";
    $stmt_unmute_user = $conn->prepare($sql_unmute_user);

    if ($stmt_unmute_user === false) {
        die("Erreur de préparation de la requête : " . $conn->error);
    }
    
    $stmt_unmute_user->bind_param("i", $user_id);

    if ($stmt_unmute_user->execute()) {
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        echo "Erreur lors du démutage de l'utilisateur : " . $stmt_unmute_user->error;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord administrateur</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
</head>
<body>
    <h1>Tableau de bord administrateur</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> ! <a href="../user/logout.php">Déconnexion</a></p>

    <h2>Liste des messages</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Contenu</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php if ($result_messages->num_rows > 0) : ?>
            <?php while ($row = $result_messages->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo isset($row['message_content']) ? htmlspecialchars($row['message_content']) : 'Contenu non disponible'; ?></td>
                    <td><?php echo isset($row['created_at']) ? htmlspecialchars($row['created_at']) : 'Date non disponible'; ?></td>
                    <td>
                        <form method="post" action="delete_message.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?');">
                            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="4">Aucun message trouvé.</td>
            </tr>
        <?php endif; ?>
    </table>

    <h2>Liste des utilisateurs</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nom d'utilisateur</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result_users->fetch_assoc()) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo $row['is_muted'] == 1 ? 'Muet' : 'Actif'; ?></td>
                <td>
                    <?php if ($row['is_muted'] == 1) : ?>
                        <a href="dashboard.php?action=unmute&id=<?php echo $row['id']; ?>"><button type="button">Unmuter</button></a>
                    <?php else : ?>
                        <form method="post" action="dashboard.php">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="mute">
                            <button type="submit">Mute</button>
                        </form>
                        <form method="post" action="dashboard.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="action" value="delete_user">
                            <button type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a href="../user/chat.php" class="button">Accéder au chat</a>

</body>
</html>
