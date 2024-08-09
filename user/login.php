<?php
require_once '../includes/db.php'; 

session_start();

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
        
        if ($row['is_admin'] == 1) {
            $_SESSION['is_admin'] = true;
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/chat.php");
        }
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];

            setcookie("user_id", $row['id'], time() + (86400 * 30), "/");

            if ($row['is_admin'] == 1) {
                $_SESSION['is_admin'] = true;
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/chat.php");
            }
            exit();
        } else {
            $login_error = "Nom d'utilisateur ou mot de passe incorrect";
        }
    } else {
        $login_error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="og:title" content="Douub.eu" />
    <meta property="og:description" content="Chat de discussion 100% anonyme" />
    <meta property="og:url" content="https://douub.eu/" />
    <title>Connexion</title>
    <link rel="stylesheet" href="../assets/css/logs.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
</head>
<body>
    <div class="container">
    <h1>Connexion</h1>
    <?php if (isset($login_error)) : ?>
        <p><?php echo $login_error; ?></p>
    <?php endif; ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
    </form>
    <p>Vous n'avez pas de compte ? <a href="register.php">Inscrivez-vous ici</a></p>
    </div>
</body>
</html>
