<?php
namespace App\Core;

class Router
{
    private array $routes    = [];
    private array $currentMiddleware = [];
    private Container $container;

    // Inject the DI container used to resolve controllers.
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


// ── Register a GET route. ───────────────────────────────────────────────────────────────
    public function get(string $uri, array $action): self
    {
        return $this->addRoute('GET', $uri, $action);
    }

// ── Register a POST route. ──────────────────────────────────────────────────────────────
    public function post(string $uri, array $action): self
    {
        return $this->addRoute('POST', $uri, $action);
    }

// ── Attach middleware to the last registered route. ────────────────────
    public function middleware(array $middleware): self
    {
        $lastRouteKey = array_key_last($this->routes);
    
        if ($lastRouteKey !== null) {
            $this->routes[$lastRouteKey]['middleware'] = $middleware;
        }
        return $this;
    }

    //Add the route to the list, 
    //then clear the middleware buffer so it does not leak into the next route.
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
    /**
     * Match the incoming request to a route, run its middleware,
     * then call the controller action. Falls back to 404 if no match.
    */
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

// Ask the container to build the controller and inject its dependencies automatically.
    public function make(string $class) 
    {
        // Simply delegate the work to your dedicated container!
        return $this->container->get($class);
    }
    
//Send a 404 response when no route matches.
    private function notFound(string $uri, string $method): void
    {
        if (PHP_SAPI !== 'cli') {
            http_response_code(404);
        }
        // Swap this for a proper 404 view later
        echo "404 — {$method} {$uri} not found";
    }
}