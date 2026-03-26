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
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (isset($this->routes[$method][$uri])) {
            list($controller, $method) = explode('@', $this->routes[$method][$uri]);
            require "../app/controllers/$controller.php";
            $controller = new $controller();
            $controller->$method();
        } else {
            echo "404";
        }
    }
}
