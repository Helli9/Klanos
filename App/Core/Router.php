<?php
namespace App\Core;

class Router
{
    private array $routes    = [];
    private array $container = [];
    private array $instances = [];
    private array $currentMiddleware = [];


// ── Binding ───────────────────────────────────────────────────────────
    public function bind(string $class, callable $factory): void
    {
        $this->container[$class] = $factory;
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
        $this->currentMiddleware = $middleware;
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
        // 1. Check if the ACTUAL OBJECT is already built and saved
        if(isset($this->instances[$class])){
            return $this->instances[$class]; // Returns the Object (e.g. AuthController instance)
        }

        // If a factory exists, use it. Otherwise, try 'new $class()'
        if (isset($this->container[$class])) {
            // 2. Get the "Blueprint" (the closure) from the container
            $factory = $this->container[$class];
            // 3. Run the closure to create the Object and save it for next time
            $this->instances[$class] = $factory(); // Runs: new AuthController(new AuthService())
        } else {
            $this->instances[$class] = new $class();
        }

        return $this->instances[$class]; // Returns the newly created Object
    }

    private function notFound(string $uri, string $method): void
    {
        http_response_code(404);
        // Swap this for a proper 404 view later
        echo "404 — {$method} {$uri} not found";
    }
}