<?php
namespace App\Core;

class Router
{
    private array $routes = [];

    private function addRoute(string $method, string $uri, array $action): static
    {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action,
        ];

        return $this;
    }

    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function dispatch(string $uri, string $method): void
    {
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {
                [$controller, $action] = $route['action'];
                (new $controller())->$action();
                return;
            }
        }
        $this->notFound($uri, $method);
    }

    private function notFound(string $uri, string $method): void
    {
        http_response_code(404);
        // Swap this for a proper 404 view later
        echo "404 — {$method} {$uri} not found";
    }
}