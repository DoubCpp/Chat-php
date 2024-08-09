<?php
require_once '../includes/db.php';

session_start(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $gender = $_POST['gender']; 

    if (strlen($username) > 15 || preg_match('/\s/', $username)) {
        $register_error = "Le pseudonyme ne doit pas dépasser 15 caractères et ne doit pas contenir d'espaces.";
    } else {
        $sql_check_username = "SELECT id FROM users WHERE username = ?";
        $stmt_check_username = $conn->prepare($sql_check_username);
        $stmt_check_username->bind_param("s", $username);
        $stmt_check_username->execute();
        $stmt_check_username->store_result();

        if ($stmt_check_username->num_rows > 0) {
            $register_error = "Ce pseudonyme est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, password, gender, is_admin) VALUES (?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashed_password, $gender);

            if ($stmt->execute()) {
                $last_id = $stmt->insert_id;

                $_SESSION['user_id'] = $last_id;
                $_SESSION['username'] = $username;
                $_SESSION['gender'] = $gender; 

                setcookie("user_id", $last_id, time() + (86400 * 30), "/");

                header("Location: chat.php");
                exit();
            } else {
                $register_error = "Erreur lors de l'inscription : " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>
    <link rel="stylesheet" href="../assets/css/logs.css">
    <link rel="shortcut icon" href="../assets/images/logo.ico" type="image/x-icon">
    <script>
        function validateUsername(input) {
            if (input.value.length > 15) {
                input.setCustomValidity('Le pseudonyme ne doit pas dépasser 15 caractères.');
            } else if (/\s/.test(input.value)) {
                input.setCustomValidity('Le pseudonyme ne doit pas contenir d\'espaces.');
            } else {
                input.setCustomValidity('');
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        <?php if (isset($register_error)) : ?>
            <p class="error"><?php echo $register_error; ?></p>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" placeholder="Nom d'utilisateur" maxlength="15" oninput="validateUsername(this)" required>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" placeholder="Mot de passe" required>
            <label for="gender">Je suis:</label>
            <select id="gender" name="gender" required>
                <option value="homme">Un garçon</option>
                <option value="femme">Une fille</option>
            </select>
            <button type="submit">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
    </div>
</body>
</html>
