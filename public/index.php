<?php
session_start();

require '../core/Router.php';

$router = new Router();

$router->get('/', 'AuthController@login');
$router->post('/', 'AuthController@login');

$router->get('/dashboard', 'UploadController@index');

$router->run();
