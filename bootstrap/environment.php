<?php

$envPath = dirname(__DIR__) . '/.env';

if (class_exists('Env')) {
    Env::load($envPath);
}

function app_base_path()
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = str_replace('\\', '/', dirname($scriptName));

    if ($basePath === '/' || $basePath === '.') {
        return '';
    }

    return rtrim($basePath, '/');
}

function url($path = '/')
{
    $path = '/' . ltrim($path, '/');

    if ($path === '/index.php') {
        $path = '/';
    }

    return app_base_path() . ($path === '/' ? '' : $path);
}
