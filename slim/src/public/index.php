<?php

require '../vendor/autoload.php';
require './db.php';

$db = new Database(); 
$pdo = $db->connect();

// Crear la aplicación Slim
$app = \Slim\Factory\AppFactory::create();

// Definir una ruta básica
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("<p>¡Hola, mundo!</p>");
    return $response;
});

require './routes/routes.php';

// Ejecutar la aplicación
$app->run();