<?php

namespace App\Core;

/**
 * Routeur — fait correspondre une méthode HTTP + URL à un contrôleur.
 * Patterns acceptés : /entite, /entite/action, /entite/action/:id ...
 */
class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $handler): void
    {
        $regex = '#^' . $this->compile($pattern) . '$#';
        $this->routes[] = [
            'method'  => strtoupper($method),
            'regex'   => $regex,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /**
     * Convertit un motif "/x/:id" en regex avec capture du segment.
     */
    private function compile(string $pattern): string
    {
        $pattern = trim($pattern, '/');
        $pattern = preg_replace('#:([a-zA-Z_]+)#', '([^/]+)', $pattern);
        return str_replace('/', '/', $pattern);
    }

    public function dispatch(string $method, string $uri): void
    {
        $path   = parse_url($uri, PHP_URL_PATH) ?? '/';
        $path   = trim($path, '/');

        /* Retire le préfixe du dossier public (ex: equiploc/public) */
        $base = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base !== '' && $base !== '.' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        $path = trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }
            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches);
                [$class, $action] = explode('@', $route['handler']);
                $fullClass = '\\App\\Controllers\\' . $class;
                if (!class_exists($fullClass)) {
                    throw new \Exception("Contrôleur introuvable : {$fullClass}");
                }
                $controller = new $fullClass();
                if (!method_exists($controller, $action)) {
                    throw new \Exception("Action introuvable : {$class}@{$action}");
                }
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        http_response_code(404);
        (new \App\Controllers\ErrorController())->notFound();
    }

    /**
     * Redirige vers une URL absolue (si non préfixée, base sur BASE_URL).
     */
    public static function redirect(string $url): void
    {
        if (preg_match('#^https?://#i', $url)) {
            header('Location: ' . $url);
        } else {
            header('Location: ' . BASE_URL . ltrim($url, '/'));
        }
        exit;
    }
}
