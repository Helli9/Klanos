<?php
namespace App\Core;

class Router
{
    private array $routes    = [];
    private array $currentMiddleware = [];
    private Container $container;

// Accept the Container via the constructor
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


// ── GET ───────────────────────────────────────────────────────────────
    public function get(string $uri, array $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }

// ── POST ──────────────────────────────────────────────────────────────
    public function post(string $uri, array $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }

// ── Middleware chain ────────────────────
    public function middleware(array $middleware): self
    {
        $lastRouteKey = array_key_last($this->routes);
    
        if ($lastRouteKey !== null) {
            $this->routes[$lastRouteKey]['middleware'] = $middleware;
        }
        
        return $this;
    }

    private function addRoute(string $method, string $uri, array $action): self
    {
        $this->routes[] = [
            'method' => $method,
            'uri'    => $uri,
            'action' => $action,
            'middleware' => $this->currentMiddleware ?? [],
        ];
        $this->currentMiddleware = [];
        return $this;
    }

// ── Dispatch ──────────────────────────────────────────────────────────
    public function dispatch(string $uri, string $method): void
    {
        //echo "<pre>"; print_r($this->routes); echo "</pre>"; die();
        foreach ($this->routes as $route) {
            if ($route['uri'] === $uri && $route['method'] === $method) {

             if (!empty($route['middleware'])) {
                foreach ($route['middleware'] as $middleware) {
                    $middleware->handle();
                }
            }

                //breaks the action array apart
                [$class, $action] = $route['action'];
                // $class -> "AuthController"
                // $action -> "showLogin"

                $controller = $this->make($class);
                $controller->$action();
                return;
            }
        }
        $this->notFound($uri, $method);
    }


    public function make(string $class) 
    {
        // Simply delegate the work to your dedicated container!
        return $this->container->get($class);
    }

    private function notFound(string $uri, string $method): void
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code(404);
        }
        // Swap this for a proper 404 view later
        echo "404 — {$method} {$uri} not found";
    }
}