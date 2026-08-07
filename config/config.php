<?php

/**
 * Configuration globale de l'application
 * ------------------------------------------------------------------
 * Constantes : chemins, base de données, sécurité, options d'affichage
 * Modifiez ici uniquement vos identifiants MySQL (XAMPP par défaut).
 */

/* ------------------------- Base de données ------------------------- */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'rentova_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/* ------------------------- Chemins & URLs ------------------------- */
define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('VIEWS', APP . '/Views');
define('PUBLIC_DIR', ROOT . '/public');

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '' || $scriptDir === '.') {
    $scriptDir = '';
}
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $scriptDir . '/');
define('ASSETS', BASE_URL . 'assets/');
define('IMG', ASSETS . 'img/');

/* ------------------------- Sécurité & session ------------------------- */
define('APP_NAME', 'Rentova');
define('APP_TAGLINE', 'Location professionnelle d\'équipements');
define('SESSION_NAME', 'equiploc_session');
define('CSRF_KEY', 'csrf_token_equiploc');

/* ------------------------- Options ------------------------- */
define('TAUX_TVA', 20);                      // Taux de TVA en %
define('FRAIS_CAUTION', 50);                 // Caution par location (€)
define('RETARD_PAR_JOUR', 15);               // Frais additionnel par jour de retard (€)
define('DATE_FORMAT', 'd/m/Y');
define('APP_TIMEZONE', 'Europe/Paris');

/* Activation du mode debug (affichage des erreurs) */
define('APP_DEBUG', true);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set(APP_TIMEZONE);

/* Permissions par rôle (tableau des accès) */
define('ROLES', ['client', 'agent', 'responsable']);

/* Libellés des rôles */
function role_label($role)
{
    $labels = [
        'client'      => 'Client',
        'agent'       => 'Agent de Location',
        'responsable' => 'Responsable Inventaire',
    ];
    return $labels[$role] ?? $role;
}
