<?php

namespace App\Core;

/**
 * Contrôleur de base : rendu de vues, redirections, flash, auth.
 */
abstract class Controller
{
    protected string $layout = 'dashboard';

    protected function view(string $view, array $data = []): void
    {
        $data['_currentUser'] = Auth::user();
        $data['_baseUrl']     = BASE_URL;
        extract($data, EXTR_SKIP);

        $viewFile = VIEWS . '/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new \Exception("Vue introuvable : {$view}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = VIEWS . '/layouts/' . $this->layout . '.php';
        require $layoutFile;
    }

    protected function redirect(string $url): void
    {
        Router::redirect($url);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer !== '') {
            $host = parse_url($referer, PHP_URL_HOST);
            if ($host !== null && strcasecmp($host, $_SERVER['HTTP_HOST'] ?? 'localhost') === 0) {
                Router::redirect($referer);
            }
        }
        Router::redirect('');
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->flash('info', 'Veuillez vous connecter pour continuer.');
            Router::redirect('login');
        }
    }

    /**
     * @param string|array $roles Rôles autorisés
     */
    protected function requireRole(string|array $roles): void
    {
        $this->requireLogin();
        $allowed = is_array($roles) ? $roles : [$roles];
        if (!in_array(Auth::user()['role'], $allowed, true)) {
            http_response_code(403);
            (new \App\Controllers\ErrorController())->forbidden();
            exit;
        }
    }

    /* --------------------------- Flash --------------------------- */
    protected function flash(string $type, string $message): void
    {
        Session::set('flash', ['type' => $type, 'message' => $message]);
    }

    /* --------------------------- CSRF --------------------------- */
    protected function verifyCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!Csrf::verify($token)) {
            http_response_code(419);
            die('Session expirée — merci de recharger la page.');
        }
    }
}
