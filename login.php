<?php
require "db.php";

try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (PDOException $e) {
    // Si ça plante, la table n'existe pas → on l'initialise
    require_once "init-db.php";
}


$message = "";
$etape = "login";
$email = "";
$token = "";

date_default_timezone_set('Europe/Paris');

if (!empty($_POST["login"]) && !empty($_POST["password"])) {

    $email = $_POST["login"];
    $password = trim($_POST["password"]);

    // Récupération de l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérification du mot de passe
    if ($user && password_verify($password, $user["password"])) {

        $now = date("Y-m-d H:i:s");

        // 1️⃣ Vérifier s'il existe déjà un code valide
        if (!empty($user["totp_secret"]) && $user["totp_expire"] > $now) {

            // Code existant encore valide
            $code = $user["totp_secret"];
        } else {

            // 2️⃣ Générer un nouveau code
            $code = random_int(100000, 999999);
            $expire = date("Y-m-d H:i:s", time() + 300);

            $stmt = $pdo->prepare("
            UPDATE users 
            SET totp_secret = ?, totp_expire = ? 
            WHERE id = ?
        ");
            $stmt->execute([$code, $expire, $user["id"]]);
        }

        // Affichage du code
        $message = "<p style='color:green;font-weight:bold;'>Votre code : $code</p>";
        $etape = "code";
    } else {
        $message = "<span class='error'>Email / Mot de passe incorrects</span>";
    }
}
?>






<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./ressources/style.css">
    <link rel="icon" type="image/x-icon" href="ressources/icon-page.ico">
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="max-width: 420px; width: 100%">
        <h2 class="text-center mb-3">Connexion</h2>

        <?= $message ?>

        <?php if ($etape === "login"): ?>

            <form method="post" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Email :</label>
                    <input type="email" name="login" class="form-control" required>
                </div>

                <div>
                    <label class="form-label">Mot de passe :</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="register.php" class="text-primary">Aucun compte ?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

        <?php endif; ?>

        <?php if ($etape === "code"): ?>

            <form action="verify.php" method="post" class="d-flex flex-column gap-3 mt-3">
                <div>
                    <label class="form-label">Code à entrer :</label>
                    <input type="text" name="codeVerif" class="form-control" required>
                </div>

                <input type="hidden" name="email" value="<?= $email ?>">

                <button type="submit" class="btn btn-success w-100">Vérifier</button>
            </form>

        <?php endif; ?>

    </div>

</body>

</html>