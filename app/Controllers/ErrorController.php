<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * Gestion des erreurs HTTP (404, 403) — pages autonomes.
 */
class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        require VIEWS . '/errors/404.php';
        exit;
    }

    public function forbidden(): void
    {
        http_response_code(403);
        require VIEWS . '/errors/403.php';
        exit;
    }
}
