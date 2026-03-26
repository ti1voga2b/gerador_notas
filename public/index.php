<?php

#echo password_hash('$teYyu7457y;67fj',PASSWORD_BCRYPT); exit;

session_start();

require dirname(__DIR__) . '/vendor/autoload.php';

$router = new Router();

$router->get('/', 'AuthController@login');
$router->post('/', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/dashboard', 'UploadController@index');
$router->post('/upload', 'UploadController@upload');
$router->get('/nfcom/download', 'UploadController@downloadNfcom');

$router->run();
