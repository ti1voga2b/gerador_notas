<?php

class Render
{
    public static function view($view, $data = [])
    {
        $viewPath = dirname(__DIR__) . '/app/views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View '{$view}' not found.");
        }

        extract($data, EXTR_SKIP);

        require $viewPath;
    }
}
