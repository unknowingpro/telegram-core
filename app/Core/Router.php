<?php
declare(strict_types=1);

namespace App\Core;

/**
 * HTTP router — dispatches GET/POST to controller actions
 * Supports parameterized routes and middleware groups
 */
class Router
{
    private array $routes = [];
    private array $groups = [];
    private array $middleware = [];

    /**
     * Register a GET route
     */
    public function get(string $path, string $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, string $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register routes for both GET and POST
     */
    public function match(array $methods, string $path, string $handler): self
    {
        foreach ($methods as $method) {
            $this->addRoute(strtoupper($method), $path, $handler);
        }
        return $this;
    }

    /**
     * Start a route group with prefix and/or middleware
     */
    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $previousPrefix = $this->groups;
        $this->groups[] = $prefix;
        $this->middleware[] = $middleware;

        $callback($this);

        array_pop($this->groups);
        array_pop($this->middleware);
    }

    private function addRoute(string $method, string $path, string $handler): self
    {
        $prefix = implode('', $this->groups);
        $fullPath = $prefix . $path;

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => end($this->middleware) ?? [],
        ];

        // Auto-register /bot{token}/method alt for /bot/{token}/method (Telegram format)
        if (preg_match('#^/(bot)/\{(\w+)\}/#', $fullPath, $m)) {
            $altPath = '/' . $m[1] . '{' . $m[2] . '}/' . substr($fullPath, strlen($m[0]));
            $this->routes[] = [
                'method' => $method,
                'path' => $altPath,
                'handler' => $handler,
                'middleware' => end($this->middleware) ?? [],
            ];
        }

        return $this;
    }

    /**
     * Register routes for all methods
     */
    public function any(string $path, string $handler): self
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $path, $handler);
    }

    /**
     * Dispatch a request to the matching route
     */
    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                // Run middleware
                foreach ($route['middleware'] as $mw) {
                    $result = $this->runMiddleware($mw, $request);
                    if ($result instanceof Response) {
                        return $result;
                    }
                }

                // Execute handler: "Controller@method"
                return $this->callHandler($route['handler'], $params, $request);
            }
        }

        return Response::error('Not Found', 404);
    }

    /**
     * Match a route pattern against a URI
     * Supports {param} placeholders
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert {param} to regex named groups
        $regex = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Filter out numeric keys
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Execute a controller@action handler
     */
    private function callHandler(string $handler, array $params, Request $request): Response
    {
        [$controllerClass, $action] = explode('@', $handler);

        // Resolve from container or instantiate
        $container = App::getInstance()->getContainer();
        $controller = $container->has($controllerClass)
            ? $container->make($controllerClass)
            : new $controllerClass();

        // Merge route params into request body for easy access
        foreach ($params as $key => $value) {
            $_REQUEST[$key] = $value;
        }

        return $controller->$action($request, ...array_values($params));
    }

    /**
     * Run a middleware class
     */
    private function runMiddleware(string $middlewareClass, Request $request): mixed
    {
        if (!class_exists($middlewareClass)) {
            return null;
        }

        $middleware = new $middlewareClass();
        return $middleware->handle($request);
    }

    /**
     * Get all registered routes (for debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
