<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request; 
use Slim\Factory\AppFactory;

/* Ruatas del api */

$app->get('/usuarios', function ($request, $response, $args) use ($pdo) {
// Realizamos la consulta para obtener los usuarios
$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creamos un array con los usuarios y el número total de usuarios
$data = [
    'total' => count($usuarios),  // Contamos el número de usuarios
    'usuarios' => $usuarios       // Los datos de los usuarios
];

// Codificamos el resultado en JSON
$response->getBody()->write(json_encode($data));

// Establecemos el header Content-Type a application/json
return $response->withHeader('Content-Type', 'application/json');
});
$app->post('/usuarios', function ($request, $response, $args) use ($pdo) {
    $data = json_decode($request->getBody(), true);

    $nombre = $data['nombre'] ?? '';
    $email = $data['email'] ?? '';

    // Validaciones
    if (empty($nombre) || empty($email)) {
        $payload = [
            'error' => true,
            'message' => 'El nombre y el email son requeridos.'
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $payload = [
            'error' => true,
            'message' => 'El email es inválido.'
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email) VALUES (?, ?)");
        $stmt->execute([$nombre, $email]);
        $id = $pdo->lastInsertId();

        $payload = [
            'error' => false,
            'message' => 'Usuario creado correctamente.',
            'usuario' => [
                'id' => $id,
                'nombre' => $nombre,
                'email' => $email
            ]
        ];

        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        $payload = [
            'error' => true,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
        $response->getBody()->write(json_encode($payload));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


$app->put('/usuarios/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);
    $nombre = $data['nombre'];
    $email = $data['email'];

    $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, email = ? WHERE id = ?");
    $stmt->execute([$nombre, $email, $id]);

    $output  = [
        'id' => $id,
        'nombre' => $nombre,
        'email' => $email,
        'mensaje' => 'Usuario actualizado correctamente'
    ];

    $response->getBody()->write(json_encode($output));
    return $response->withHeader('Content-Type', 'application/json');

});
$app->delete('/usuarios/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];

    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    $output  = [
        'id' => $id,
        'mensaje' => 'Usuario eliminado correctamente'
    ];

    $response->getBody()->write(json_encode($output));
    return $response->withHeader('Content-Type', 'application/json');
});
?>