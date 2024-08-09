<?php
session_start();

function logout() {

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    setcookie("user_id", "", time() - 3600, "/");
    setcookie("is_18_or_over", "", time() - 3600, "/");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['age_verification'])) {
    if ($_POST['age_verification'] == 'yes') {
        $_SESSION['is_18_or_over'] = true;
        setcookie("is_18_or_over", "true", time() + 86400, "/"); // 86400 = 24 heures
        header("Location: chat.php");
        exit();
    } else {
        logout(); 
        header("Location: login.php"); 
        exit();
    }
}

if (isset($_COOKIE['is_18_or_over']) && $_COOKIE['is_18_or_over'] == 'true') {
    $_SESSION['is_18_or_over'] = true;
    header("Location: chat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Douub.eu - Vérification d'âge</title>
    <link rel="stylesheet" href="../assets/css/chat.css">
</head>
<body>
    <div id="age-verification-modal">
        <h1>Vérification d'âge</h1>
        <p>Avez-vous 18 ans ou plus ?</p>
        <form method="post">
            <button type="submit" name="age_verification" value="yes">Oui</button>
            <button type="submit" name="age_verification" value="no">Non</button>
        </form>
    </div>
</body>
</html>
