<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Application bootstrap singleton
 * Loads config, initializes container, dispatches router
 */
class App
{
    private static ?self $instance = null;
    private Container $container;
    private Router $router;
    private bool $running = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Bootstrap and run the application
     */
    public function run(): void
    {
        if ($this->running) {
            return;
        }
        $this->running = true;

        // Load environment
        require BASE_PATH . '/config/env.php';

        // Load config
        $config = require BASE_PATH . '/config/app.php';
        $dbConfig = require BASE_PATH . '/config/database.php';

        // Initialize container
        $this->container = new Container();
        $this->container->bind('config', $config);
        $this->container->bind('db_config', $dbConfig);
        $this->container->bind(Container::class, $this->container);

        // Initialize database
        $db = Database::getInstance($dbConfig);
        $this->container->bind(Database::class, $db);

        // Initialize router
        $this->router = new Router();

        // Load routes
        $this->loadRoutes();

        // Set timezone
        date_default_timezone_set($config['timezone'] ?? 'UTC');

        // Dispatch
        $this->dispatch();
    }

    /**
     * Load route files
     */
    private function loadRoutes(): void
    {
        $routesDir = BASE_PATH . '/routes';

        if (file_exists($routesDir . '/api.php')) {
            require $routesDir . '/api.php';
        }
        if (file_exists($routesDir . '/web.php')) {
            require $routesDir . '/web.php';
        }
    }

    /**
     * Dispatch the current request
     */
    private function dispatch(): void
    {
        $request = Request::capture();
        $response = $this->router->dispatch($request);
        $response->send();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
