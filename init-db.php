<?php

try {
    /* - - - - - - - - - - - - - - -
       TABLE users
       - - - - - - - - - - - - - - - */
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) DEFAULT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    totp_secret TEXT DEFAULT NULL,
    totp_expire DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY email (email)
    ) ENGINE=InnoDB
     DEFAULT CHARSET=utf8mb4
     COLLATE=utf8mb4_general_ci;
");

    /* - - - - - - - - - - - - - - -
       PROCEDURE
       - - - - - - - - - - - - - - - */
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM information_schema.ROUTINES
        WHERE ROUTINE_SCHEMA = DATABASE()
          AND ROUTINE_NAME = 'CleanExpiredCodes'
          AND ROUTINE_TYPE = 'PROCEDURE'
    ");

    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            CREATE PROCEDURE CleanExpiredCodes()
            BEGIN
                UPDATE users
                SET totp_secret = NULL,
                    totp_expire = NULL
                WHERE totp_expire IS NOT NULL
                  AND totp_expire < NOW();
            END;
        ");
    }

    /* - - - - - - - - - - - - - - -
       Exécute la procédure toutes les minutes
       - - - - - - - - - - - - - - - */

    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM information_schema.EVENTS
        WHERE EVENT_SCHEMA = DATABASE()
          AND EVENT_NAME = 'clean_codes_event'
    ");

    if ($stmt->fetchColumn() == 0) {

        // Active l'event scheduler
        $pdo->exec("SET GLOBAL event_scheduler = ON");

        $pdo->exec("
            CREATE EVENT clean_codes_event
            ON SCHEDULE EVERY 1 MINUTE
            DO CALL CleanExpiredCodes();
        ");
    }

    echo "<script>console.log('Initialisation terminée');</script>";
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
