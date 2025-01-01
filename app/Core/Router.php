<?php
namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionMethod;

class Router {
    private $routes = [];
    private $controllerNamespace = 'App\\Controllers\\';

    public function __construct() {
        // Load routes from configuration file
        $this->routes = require dirname(dirname(__DIR__)) . '/config/routes.php';
    }

    /**
     * Dispatch the current request to the appropriate controller method
     * @param string $requestMethod
     * @param string $requestUri
     * @throws Exception
     */
    public function dispatch($requestMethod, $requestUri) {
        // Remove query string and trim leading/trailing slashes
        $requestUri = strtok($requestUri, '?');
        $requestUri = trim($requestUri, '/');

        // Find a matching route
        foreach ($this->routes as $route => $handler) {
            // Separate method and path
            list($routeMethod, $routePath) = explode(' ', $route, 2);

            // Check if request method matches
            if ($routeMethod !== $requestMethod) {
                continue;
            }

            // Convert route with parameters to regex pattern
            $pattern = $this->convertRouteToRegex($routePath);

            // Check if current request matches the route pattern
            if (preg_match($pattern, $requestUri, $matches)) {
                // Remove the full match, leaving only captured parameters
                array_shift($matches);

                // Split handler into controller and method
                list($controllerName, $methodName) = explode('@', $handler);
                $fullControllerName = $this->controllerNamespace . $controllerName;

                // Validate controller and method
                if (!class_exists($fullControllerName)) {
                    throw new Exception("Controller {$controllerName} not found");
                }

                $controller = new $fullControllerName();
                
                if (!method_exists($controller, $methodName)) {
                    throw new Exception("Method {$methodName} not found in {$controllerName}");
                }

                // Call the controller method with matched parameters
                return call_user_func_array([$controller, $methodName], $matches);
            }
        }

        // No route found
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Route not found'
        ]);
        exit;
    }

    /**
     * Convert route with parameters to regex pattern
     * @param string $route
     * @return string
     */
    private function convertRouteToRegex($route) {
        // Escape forward slashes
        $route = str_replace('/', '\/', $route);

        // Convert {param} to regex capture groups
        $route = preg_replace('/\{([^}]+)\}/', '([^\/]+)', $route);

        // Add start and end anchors
        return '/^' . $route . '$/';
    }

    /**
     * Get current request method
     * @return string
     */
    public static function getCurrentMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get current request URI
     * @return string
     */
    public static function getCurrentUri() {
        return $_SERVER['REQUEST_URI'];
    }
}
