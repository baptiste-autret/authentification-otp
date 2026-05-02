<?php
require "db.php";

$message = "";
$success = false;

if (!empty($_POST["codeVerif"]) && !empty($_POST["email"])) {

    $email = $_POST["email"];
    $codeInput = trim($_POST["codeVerif"]);

    // On récupère l'utilisateur et son code TOTP
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $user["totp_secret"]) {

        $codeDB = $user["totp_secret"]; // Le code stocké

        if ($codeInput === $codeDB) {

            // Si c’est OK, on supprime le code
            $stmt = $pdo->prepare("UPDATE users SET totp_secret = NULL WHERE id = ?");
            $stmt->execute([$user["id"]]);

            $success = true;
        } else {
            $message = "Code incorrect";
        }
    } else {
        $message = "Aucun code pour cet utilisateur";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Vérification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./ressources/style.css">
    <link rel="icon" type="image/x-icon" href="ressources/icon-page.ico">
</head>

<body>
    <div class="card shadow-sm border-0 p-4 text-center">

        <?php if ($success): ?>

            <div class="mb-3">
                <span class="fs-1 text-success">✔</span>
            </div>

            <h3 class="text-success mb-3">Connexion réussie</h3>

            <p class="text-muted mb-4">
                Vous êtes maintenant connecté.
            </p>

            <a href="./login.php" class="btn btn-outline-danger w-100">
                Se déconnecter
            </a>

        <?php else: ?>

            <div class="mb-3">
                <span class="fs-1 text-danger">✖</span>
            </div>

            <h3 class="text-danger mb-3">Une erreur est survenue</h3>

            <p class="text-danger mb-4">
                <?= htmlspecialchars($message) ?>
            </p>

            <button onclick="history.back()" class="btn btn-outline-secondary w-100">
                Réessayer
            </button>

        <?php endif; ?>

    </div>

</body>

</html>