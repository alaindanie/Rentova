<?php

/**
 * Contrôleur frontal — toutes les requêtes passent par ici.
 */
require __DIR__ . '/../app/Core/bootstrap.php';

/* Si la base de données n'existe pas encore, on propose l'installation. */
try {
    \App\Core\Database::db();
} catch (PDOException $e) {
    if (APP_DEBUG) {
        echo '<div style="font-family:system-ui;max-width:560px;margin:80px auto;line-height:1.7">'
           . '<h2 style="margin-bottom:6px">Base de données non initialisée</h2>'
           . '<p>' . e($e->getMessage()) . '</p>'
           . '<p>Lancez l\'installation automatique : <a href="' . BASE_URL . '../install.php" '
           . 'style="display:inline-block;margin-top:8px;padding:10px 18px;background:#2563eb;color:#fff;border-radius:8px;text-decoration:none">'
           . 'Installer l\'application</a></p></div>';
    } else {
        echo 'Base de données non initialisée.';
    }
    exit;
}

require __DIR__ . '/../config/routes.php';
