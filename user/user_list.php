<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$users = getAllUsers(); 
$conversations = getConversations($user_id); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Liste des utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/chat.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script>
        function filterUsers() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const userList = document.getElementById('userList');
            const users = userList.getElementsByTagName('li');
            let visibleCount = 0; 
            
            for (let i = 0; i < users.length; i++) {
                const username = users[i].textContent || users[i].innerText;
                if (username.toLowerCase().indexOf(searchInput) > -1) {
                    users[i].style.display = "";
                    visibleCount++; 
                } else {
                    users[i].style.display = "none";
                }
            }

            document.getElementById('noResults').style.display = (visibleCount === 0) ? "" : "none";
        }
    </script>
</head>
<body>
    <h1><a class="arrow" href="chat.php">⬅</a> Liste des Utilisateurs</h1>

    <input type="text" id="searchInput" onkeyup="filterUsers()" placeholder="Rechercher des utilisateurs...">
    <p id="noResults" style="display:none;">Aucun résultat.</p>

    <ul id="userList">
        <?php foreach ($users as $user): ?>
            <?php if ($user['id'] != $user_id): ?>
                <li><?php echo htmlspecialchars($user['username']); ?> - <a href="private_chat.php?user_id=<?php echo $user['id']; ?>">Démarrer conversation</a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <h2>Conversations ouvertes</h2>
        <?php if (!empty($conversations)): ?>
            <ul id="conversationsContainer">
    <?php foreach ($conversations as $conversation): ?>
        <li>
            <a href="private_chat.php?user_id=<?php echo $conversation['user_id']; ?>">
                <?php echo htmlspecialchars($conversation['username']); ?>
                <?php if ($conversation['unread_count'] > 0): ?>
                    <span style="color: red; font-size: 20px;">•</span>
                <?php endif; ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
        <?php else: ?>
            <p>Aucune conversation ouverte.</p>
        <?php endif; ?>
    <p><a href="chat.php">Accéder à la messagerie</a></p>
</body>
</html>
