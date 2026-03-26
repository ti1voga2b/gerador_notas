<?php

$envPath = dirname(__DIR__) . '/.env';
$logDirectory = dirname(__DIR__) . '/storage/logs';
$logFile = $logDirectory . '/app.log';

if (class_exists('Env')) {
    Env::load($envPath);
}

if (!is_dir($logDirectory)) {
    mkdir($logDirectory, 0777, true);
}

ini_set('log_errors', '1');
ini_set('error_log', $logFile);

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
