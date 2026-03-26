<?php
class Router
{
    private $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function run()
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $this->normalizeUri();

        if (isset($this->routes[$httpMethod][$uri])) {
            list($controller, $action) = explode('@', $this->routes[$httpMethod][$uri]);
            $controller = new $controller();
            $controller->$action();
            return;
        }

        http_response_code(404);
        echo '404';
    }

    private function normalizeUri()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($basePath !== '' && $basePath !== '.' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        if ($uri === '' || $uri === false) {
            return '/';
        }

        if ($uri === '/index.php') {
            return '/';
        }

        return rtrim($uri, '/') ?: '/';
    }
}
