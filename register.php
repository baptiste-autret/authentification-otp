<?php
require "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["login"]);
    $password = trim($_POST["password"]);
    $confirm = trim($_POST["confirm_password"]);
    $username = trim($_POST["username"]);

    // ----------------------------
    // VALIDATIONS
    // ----------------------------
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif (empty($username)) {
        $error = "Le nom d\'utilisateur est obligatoire.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {

        // Vérifier si l'email existe déjà
        $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            // Vérifier si le username existe déjà
            $checkUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $checkUser->execute([$username]);

            if ($checkUser->rowCount() > 0) {
                $error = "Ce nom d\'utilisateur est déjà pris.";
            } else {
                // INSERTION DANS LA BDD
                $sql = "INSERT INTO users (email, username, password) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                try {
                    $stmt->execute([$email, $username, password_hash(trim($password), PASSWORD_DEFAULT)]);
                    $success = "Compte créé avec succès !";

                    // (ajoute ici sendOtp si tu veux)
                } catch (Exception $e) {
                    $error = "Erreur : " . $e->getMessage();
                }
            }
        }
    }
}
?>






<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Créer un compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./ressources/style.css">
    <link rel="icon" type="image/x-icon" href="ressources/icon-page.ico">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="max-width: 420px; width: 100%">
        <h2 class="text-center mb-4">Créer un compte</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="d-flex flex-column gap-3">
            <div>
                <label class="form-label">Nom d'utilisateur :</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div>
                <label class="form-label">Email :</label>
                <input type="email" name="login" class="form-control" required>
            </div>

            <div>
                <label class="form-label">Mot de passe :</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div>
                <label class="form-label">Confirmer le mot de passe :</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <a href="login.php" class="text-primary">Déjà un compte ?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Créer mon compte</button>
        </form>
    </div>

</body>

</html>