<?php

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\PaginasController;

$router = new Router();

$router->get('/usuarios/verUsuarios', [PaginasController::class, 'getUsuarios']);
$router->post('/users/create', [PaginasController::class, 'create']);
$router->post('/users/delete', [PaginasController::class, 'destroy']);
$router->post('/users/update', [PaginasController::class, 'update']);
//comprobar las rutas
$router->comprobarRutas();