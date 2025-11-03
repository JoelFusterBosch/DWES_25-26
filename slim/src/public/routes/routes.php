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
    $stmt = $pdo->query('SELECT * FROM rutas');
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

    $stmt = $pdo->prepare("
        SELECT c.id,
               ao.id AS origen_id, ao.codigo_iata AS origen_iata, ao.municipio AS origen_municipio,
               ad.id AS destino_id, ad.codigo_iata AS destino_iata, ad.municipio AS destino_municipio,
               c.aerolinea, c.codigo_compartido, c.paradas, c.equipamiento
        FROM rutas c
        JOIN aeropuertos ao ON ao.id = c.id_aeropuerto_origen
        JOIN aeropuertos ad ON ad.id = c.id_aeropuerto_destino
        WHERE LOWER(ao.municipio) = LOWER(:from) 
          AND LOWER(ad.municipio) = LOWER(:to)
    ");

    $stmt->execute([
        'from' => $ciudad_orig,
        'to'   => $ciudad_dest
    ]);

    $conexion = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [
        'total' => count($conexion),
        'conexion' => $conexion
    ];

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/airport/{id}/connections', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);

    $stmt = $pdo->prepare("
    SELECT c.id,
        ao.id AS origen_id, ao.codigo_iata AS origen_iata, ao.municipio AS origen_municipio,
        ad.id AS destino_id, ad.codigo_iata AS destino_iata, ad.municipio AS destino_municipio,
        c.aerolinea, c.codigo_compartido, c.paradas, c.equipamiento
    FROM rutas c
    JOIN aeropuertos ao ON ao.id = c.id_aeropuerto_origen
    JOIN aeropuertos ad ON ad.id = c.id_aeropuerto_destino
    WHERE c.id_aeropuerto_origen = ?;
");
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

$app->get('/connections/with-stops/{from}/{to}', function ($request, $response, $args) use ($pdo) {
    $from = $args['from'];
    $to   = $args['to'];

    $sql = " 
    -- Nivel 1: directo
    SELECT ao1.codigo_iata AS origen, ad1.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata) AS ruta,
           0 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    WHERE LOWER(ao1.municipio) = LOWER(:from)
      AND LOWER(ad1.municipio) = LOWER(:to)

    UNION ALL

    -- Nivel 2: 1 escala
    SELECT ao1.codigo_iata AS origen, ad2.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata,'->',ad2.codigo_iata) AS ruta,
           1 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    JOIN rutas r2 ON r2.id_aeropuerto_origen = ad1.id
    JOIN aeropuertos ad2 ON ad2.id = r2.id_aeropuerto_destino
    WHERE LOWER(ao1.municipio) = LOWER(:from)
      AND LOWER(ad2.municipio) = LOWER(:to)
      AND ad2.id NOT IN (ao1.id, ad1.id)

    UNION ALL

    -- Nivel 3: 2 escalas
    SELECT ao1.codigo_iata AS origen, ad3.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata,'->',ad2.codigo_iata,'->',ad3.codigo_iata) AS ruta,
           2 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    JOIN rutas r2 ON r2.id_aeropuerto_origen = ad1.id
    JOIN aeropuertos ad2 ON ad2.id = r2.id_aeropuerto_destino
    JOIN rutas r3 ON r3.id_aeropuerto_origen = ad2.id
    JOIN aeropuertos ad3 ON ad3.id = r3.id_aeropuerto_destino
    WHERE LOWER(ao1.municipio) = LOWER(:from)
      AND LOWER(ad3.municipio) = LOWER(:to)
      AND ad3.id NOT IN (ao1.id, ad1.id, ad2.id)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['from' => $from, 'to' => $to]);
    $rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode([
        'total' => count($rutas),
        'rutas' => $rutas
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


$app->get('/airport/{id}/connections/with-stops', function ($request, $response, $args) use ($pdo) {
    $id_origen = (int)$args['id'];

    $sql = "
    -- Nivel 1: directo
    SELECT ao1.codigo_iata AS origen, ad1.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata) AS ruta,
           0 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    WHERE r1.id_aeropuerto_origen = :airport_id

    UNION ALL

    -- Nivel 2: 1 escala
    SELECT ao1.codigo_iata AS origen, ad2.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata,'->',ad2.codigo_iata) AS ruta,
           1 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    JOIN rutas r2 ON r2.id_aeropuerto_origen = ad1.id
    JOIN aeropuertos ad2 ON ad2.id = r2.id_aeropuerto_destino
    WHERE r1.id_aeropuerto_origen = :airport_id
      AND ad2.id NOT IN (ao1.id, ad1.id)

    UNION ALL

    -- Nivel 3: 2 escalas
    SELECT ao1.codigo_iata AS origen, ad3.codigo_iata AS destino,
           CONCAT(ao1.codigo_iata,'->',ad1.codigo_iata,'->',ad2.codigo_iata,'->',ad3.codigo_iata) AS ruta,
           2 AS escalas
    FROM rutas r1
    JOIN aeropuertos ao1 ON ao1.id = r1.id_aeropuerto_origen
    JOIN aeropuertos ad1 ON ad1.id = r1.id_aeropuerto_destino
    JOIN rutas r2 ON r2.id_aeropuerto_origen = ad1.id
    JOIN aeropuertos ad2 ON ad2.id = r2.id_aeropuerto_destino
    JOIN rutas r3 ON r3.id_aeropuerto_origen = ad2.id
    JOIN aeropuertos ad3 ON ad3.id = r3.id_aeropuerto_destino
    WHERE r1.id_aeropuerto_origen = :airport_id
      AND ad3.id NOT IN (ao1.id, ad1.id, ad2.id)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['airport_id' => $id_origen]);
    $rutas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode([
        'total' => count($rutas),
        'rutas' => $rutas
    ]));

    return $response->withHeader('Content-Type', 'application/json');
});


$app->post('/airports', function ($request, $response, $args) use ($pdo) {
    $data = json_decode($request->getBody(), true);

    $ident = $data['ident'] ?? '';
    $tipo = $data['tipo'] ?? '';
    $nombre_aeropuerto = $data['nombre_aeropuerto'] ?? '';
    $latitud_deg = $data['latitud_deg'] ?? '';
    $longitud_deg = $data['longitud_deg'] ?? '';
    $elevacion_ft = $data['elevacion_ft'] ?? '';
    $continente = $data['continente'] ?? 'NA';
    $iso_pais = $data['iso_pais'] ?? '';
    $iso_region = $data['iso_region'] ?? '';
    $municipio = $data['municipio'] ?? '';
    $servicio_programado = $data['$servicio_programado'] ?? 'no';
    $codigo_icao = $data['codigo_icao'] ?? '';
    $codigo_iata = $data['codigo_iata'] ?? '';
    $codigo_gps = $data['codigo_gps'] ?? '';
    $codigo_local = $data['codigo_local'] ?? '';
    $link_inicio = $data['link_inicio'] ?? '';
    $link_wikipedia = $data['link_wikipedia'] ?? '';
    $palabras_clave = $data['palabras_clave'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO aeropuertos (ident, tipo, nombre_aeropuerto, latitud_deg, longitud_deg, elevacion_ft, continente, iso_pais, iso_region, municipio, servicio_programado, codigo_icao, codigo_iata, codigo_gps, codigo_local, link_inicio, link_wikipedia, palabras_clave) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$ident, $tipo, $nombre_aeropuerto, $latitud_deg, $longitud_deg, $elevacion_ft, $continente, $iso_pais, $iso_region, $municipio, $servicio_programado, $codigo_icao, $codigo_iata, $codigo_gps, $codigo_local, $link_inicio, $link_wikipedia, $palabras_clave]);
        $id = $pdo->lastInsertId();

        $payload = [
            'error' => false,
            'message' => 'Aeropuerto creado correctamente.',
            'aeropuerto' => [
                'id' => $id,
                'ident' => $ident,
                'tipo' => $tipo,
                'nombre_aeropuerto' => $nombre_aeropuerto,
                'latitud_deg' => $latitud_deg,
                'longitud_deg' => $longitud_deg,
                'elevacion_ft' => $elevacion_ft,
                'continente' => $continente,
                'iso_pais' => $iso_pais,
                'iso_region' => $iso_region,
                'municipio' => $municipio,
                'servicio_programado' => $servicio_programado,
                'codigo_icao' => $codigo_icao,
                'codigo_iata' => $codigo_iata,
                'codigo_gps' => $codigo_gps,
                'codigo_local' => $codigo_local,
                'link_inicio' => $link_inicio,
                'link_wikipedia' => $link_wikipedia,
                'palabras_clave' => $palabras_clave
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

$app->put('/airport/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];
    $data= json_decode($request->getBody(), true);
    
    $ident = $data['ident'];
    $tipo = $data['tipo'];
    $nombre_aeropuerto = $data['nombre_aeropuerto'];
    $latitud_deg = $data['latitud_deg'];
    $longitud_deg = $data['longitud_deg'];
    $elevacion_ft = $data['elevacion_ft'];
    $continente = $data['continente'];
    $iso_pais = $data['iso_pais'];
    $iso_region = $data['iso_region'];
    $municipio = $data['municipio'];
    $servicio_programado = $data['servicio_programado'];
    $codigo_icao = $data['codigo_icao'];
    $codigo_iata = $data['codigo_iata'];
    $codigo_gps = $data['codigo_gps'];
    $codigo_local = $data['codigo_local'];
    $link_inicio = $data['link_inicio'];
    $link_wikipedia = $data['link_wikipedia'];
    $palabras_clave = $data['palabras_clave'];

    $stmt = $pdo->prepare("UPDATE aeropuertos SET ident = ?, tipo = ?, nombre_aeropuerto = ?, latitud_deg = ?, longitud_deg = ?, elevacion_ft = ?, continente = ?, iso_pais = ?, iso_region = ?, municipio = ?, servicio_programado = ?, codigo_icao = ?, codigo_iata = ?, codigo_gps = ?, codigo_local = ?, link_inicio = ?, link_wikipedia = ?, palabras_clave = ? WHERE id = ?");
    $stmt->execute([$ident, $tipo,$nombre_aeropuerto,$latitud_deg,$longitud_deg,$elevacion_ft,$continente,$iso_pais,$iso_region,$municipio,$servicio_programado,$codigo_icao,$codigo_iata,$codigo_gps,$codigo_local,$link_inicio,$link_wikipedia,$palabras_clave, $id]);

    $output  = [
        'id' => $id,
        'ident' => $ident,
        'tipo' => $tipo,
        'nombre_aeropuerto' => $nombre_aeropuerto,
        'latitud_deg' => $latitud_deg,
        'longitud_deg' => $longitud_deg,
        'elevacion_ft' => $elevacion_ft,
        'continente' => $continente,
        'iso_pais' => $iso_pais,
        'iso_region' => $iso_region,
        'municipio' => $municipio,
        'servicio_programado' => $servicio_programado,
        'codigo_icao' => $codigo_icao,
        'codigo_iata' => $codigo_iata,
        'codigo_gps' => $codigo_gps,
        'codigo_local' => $codigo_local,
        'link_inicio' => $link_inicio,
        'link_wikipedia' => $link_wikipedia,
        'palabras_clave' => $palabras_clave,
        'mensaje' => 'Aeropuerto actualizado correctamente'
    ];

    $response->getBody()->write(json_encode($output));
    return $response->withHeader('Content-Type', 'application/json');

});
$app->delete('/airports/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];

    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM rutas
        WHERE id_aeropuerto_origen = :id OR id_aeropuerto_destino = :id
    ");
    $stmt->execute(['id' => $id]);
    $rutas = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($rutas['total'] > 0) {
        // 3️⃣ Si tiene rutas, impedir la eliminación
        $response->getBody()->write(json_encode([
            'error' => 'No se puede eliminar el aeropuerto porque tiene rutas asociadas',
            'detalle' => 'Debe eliminar primero las rutas donde participa este aeropuerto',
            'total_rutas_asociadas' => $rutas['total']
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $delete = $pdo->prepare("DELETE FROM aeropuertos WHERE id = ?");
    $delete->execute([$id]);

    $output  = [
        'id' => $id,
        'mensaje' => 'Aeropuerto eliminado correctamente'
    ];

    $response->getBody()->write(json_encode($output));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/connections', function ($request, $response, $args) use ($pdo) {
    $data = json_decode($request->getBody(), true);

    $aerolinea = $data['aerolinea'] ?? '';
    $id = $data['id'] ?? '';
    $aeropuerto_origen = $data['aeropuerto_origen'] ?? '';
    $id_aeropuerto_origen = $data['id_aeropuerto_origen'] ?? '';
    $aeropuerto_destino = $data['aeropuerto_destino'] ?? '';
    $id_aeropuerto_destino = $data['id_aeropuerto_destino'] ?? '';
    $codigo_compartido = $data['codigo_compartido'] ?? '';
    $paradas = $data['paradas'] ?? '';
    $equipamiento = $data['equipamiento'] ?? '';
    try {
        $stmt = $pdo->prepare("INSERT INTO rutas (aerolinea, aeropuerto_origen, id_aeropuerto_origen, aeropuerto_destino, id_aeropuerto_destino, codigo_compartido, paradas, equipamiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$aerolinea, $aeropuerto_origen, $id_aeropuerto_origen, $aeropuerto_destino, $id_aeropuerto_destino, $codigo_compartido, $paradas, $equipamiento]);
        $id = $pdo->lastInsertId();

        $payload = [
            'error' => false,
            'message' => 'Ruta creada correctamente.',
            'ruta' => [
                'id' => $id,
                'aerolinea' => $aerolinea,
                'aeropuerto_origen' => $aeropuerto_origen,
                'id_aeropuerto_origen' => $id_aeropuerto_origen,
                'aeropuerto_destino' => $aeropuerto_destino,
                'id_aeropuerto_destino' => $id_aeropuerto_destino,
                'codigo_compartido' => $codigo_compartido,
                'paradas' => $paradas,
                'equipamiento' => $equipamiento
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

$app->delete('/connections/{id}', function ($request, $response, $args) use ($pdo) {
    $id = $args['id'];

    $stmt = $pdo->prepare("DELETE FROM rutas WHERE id = ?");
    $stmt->execute([$id]);

    $output  = [
        'id' => $id,
        'mensaje' => 'Ruta eliminada correctamente'
    ];

    $response->getBody()->write(json_encode($output));
    return $response->withHeader('Content-Type', 'application/json');
});
?>