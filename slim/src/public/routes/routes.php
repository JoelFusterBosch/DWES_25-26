<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 
use Slim\Factory\AppFactory;

/* Ruatas del api */

$app->get('/airports', function ($request, $response, $args) use ($pdo) {
// Realizamos la consulta para obtener los usuarios
$stmt = $pdo->query("SELECT * FROM aeropuertos");
$aeropuertos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creamos un array con los usuarios y el número total de usuarios
$data = [
    'total' => count($aeropuertos),  // Contamos el número de usuarios
    'aeropuertos' => $aeropuertos       // Los datos de los usuarios
];

// Codificamos el resultado en JSON
$response->getBody()->write(json_encode($data));

// Establecemos el header Content-Type a application/json
return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/cities', function ($request, $response, $args) use ($pdo) {
// Realizamos la consulta para obtener los usuarios
$stmt = $pdo->query("SELECT * FROM ciudades");
$ciudades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creamos un array con los usuarios y el número total de usuarios
$data = [
    'total' => count($ciudades),  // Contamos el número de usuarios
    'ciudades' => $ciudades       // Los datos de los usuarios
];

// Codificamos el resultado en JSON
$response->getBody()->write(json_encode($data));

// Establecemos el header Content-Type a application/json
return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/airports/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);

    $stmt = $pdo->prepare("SELECT * FROM aeropuertos WHERE id = ?");
    $stmt->execute([$id]);
    $aeropuertos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Creamos un array con los usuarios y el número total de usuarios
    $data = [
        'total' => count($aeropuertos),  // Contamos el número de usuarios
        'aeropuertos' => $aeropuertos       // Los datos de los usuarios
    ];
    
    // Codificamos el resultado en JSON
    $response->getBody()->write(json_encode($data));
    
    // Establecemos el header Content-Type a application/json
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/cities/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);

    $stmt = $pdo->prepare("SELECT * FROM ciudades WHERE id = ?");
    $stmt->execute([$id]);
    $ciudades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Creamos un array con los usuarios y el número total de usuarios
    $data = [
        'total' => count($ciudades),  // Contamos el número de usuarios
        'ciudades' => $ciudades      // Los datos de los usuarios
    ];
    
    // Codificamos el resultado en JSON
    $response->getBody()->write(json_encode($data));
    
    // Establecemos el header Content-Type a application/json
    return $response->withHeader('Content-Type', 'application/json',);
});

$app->get('/connections', function($request,$response,$args) use ($pdo){
    $stmt = $pdo->query('SELECT * FROM conexiondirecta');
    $conexiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [
        'total' => count($conexiones),
        'conexiones' => $conexiones
    ];

    $response->getBody()->write(json_encode($data));

    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/connections/{from}/{to}', function ($request, $response, $args) use ($pdo) {
    $ciudad_orig = $args['from'];
    $ciudad_dest = $args['to'];
    $data= json_decode($request->getBody(), true);

    $stmt = $pdo->prepare("SELECT * FROM conexiondirecta WHERE ciudad_origen = ? AND ciudad_destino = ?");
    $stmt->execute([$ciudad_orig, $ciudad_dest]);
    $conexion = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Creamos un array con los usuarios y el número total de usuarios
    $data = [
        'total' => count($conexion),
        'conexion' => $conexion
    ];
    
    // Codificamos el resultado en JSON
    $response->getBody()->write(json_encode($data));
    
    // Establecemos el header Content-Type a application/json
    return $response->withHeader('Content-Type', 'application/json',);
});

$app->get('/airport/{id}/connections', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);

    $stmt = $pdo->prepare("SELECT * FROM conexiondirecta WHERE ciudad_origen = ?");
    $stmt->execute([$id]);
    $conexion = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Creamos un array con los usuarios y el número total de usuarios
    $data = [
        'total' => count($conexion),
        'conexion' => $conexion
    ];
    
    // Codificamos el resultado en JSON
    $response->getBody()->write(json_encode($data));
    
    // Establecemos el header Content-Type a application/json
    return $response->withHeader('Content-Type', 'application/json',);
});
?>