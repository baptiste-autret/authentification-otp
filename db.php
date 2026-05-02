<?php

require __DIR__ . '/env/env.php';   // Charger notre loader .env
loadEnv(__DIR__ . '/env/.env');      // Charger le fichier .env

$host   = $_ENV["DB_HOST"];
$dbname = $_ENV["DB_NAME"];
$user   = $_ENV["DB_USER"];
$pass   = $_ENV["DB_PASS"];

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (Exception $e) {
    die("Erreur connexion BDD : " . $e->getMessage());
}
