<?php

namespace App\Core;

/**
 * Bootstrap de l'application : autoload, session, erreurs.
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/Helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strpos($class, $prefix) === 0) {
        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = ROOT . '/app/' . $relative . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});

Session::start();

/* Historique : garde l'utilisateur connecté à jour (changement de rôle/profil). */
if (Auth::check()) {
    \App\Models\Utilisateur::touchSession(Auth::id());
}
