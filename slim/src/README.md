# Creació de APIS en PHP
## Fitxers necessaris
Per a fer una API en PHP necessitem tindre uns fitxers específics per a poder elaborar endpoints, connexió a bases de dades, etc.

Ací és mostrarà les peticions més bàsiques de CRUD en APIs en PHP mitjançant `slim`.

Primerament necessitem crear un fitxer php per a accedir a la base de dades, per exemple `db.php` o un `.env` (recomane més este últim per si vols publicar-ho en llocs com Github), en fi, la pregunta és que te que n'hi haure en el fitxer `db.php`?

### Fitxer db.php
Aquest serà el fitxer en el que farem la connexió a la base de dades per a poder agafar la informació de la pròpia base de dades.
El que té que n'hi haure és el següent:
- Una classe "Database" per a poder connectar-mo'n a la base de dades.
- Variables de dades de la base de dades ja siga nom d'usuari, nom de la base de dades, nom del host..., etc.
- Una funció que faja la connexió a la base de dades i que gaste les variables definits anteriorment.

Ara mostre un exemple de com tindre el fitxer `db.php`
```php
<?php 

//use PDO; // Esta linia la comentes o la elimines, de moment no té ús

class Database {
    private $host = 'host'; // Canvia host per el host que tingues en el docker-compose.yml en MYSQL_HOST o si no tens res posa localhost
    private $db = 'db'; // Canvia db per el nom de la base de dades que vas a utilitzar
    private $user = 'user'; // Canvia user pel teu usuari de MySQL
    private $pass = 'pass'; // Canvia pass per la teua contrasenya de MySQL
    private $charset = 'utf8mb4';

    public $pdo = null;
    // Funció per a connectar-te a la base de dades MySQL
    public function connect() {
        if ($this->pdo === null) {
            // Aquesta linia de codi fa la connexió amb les dades esmentades amb anterioritat
            $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
            // Try-catch per a probar si la connexió ha sigut exitosa o no
            try {
                //Acabar la connexió si ha sogut exitosa
                $this->pdo = new PDO($dsn, $this->user, $this->pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                //Missatge d'error si la connexió ha fallat
                echo "<p>Connection failed: " . $e->getMessage() . "</p>";
            }
        }
        return $this->pdo;
    }
}
?>
```
### Fitxer index.php
Aquest fitxer va a ser el fitxer en el que és centrarà la funcionalitat d'iniciar el servidor, i connectar amb el fitxer de endpoints i el de base de dades amb slim.
Ara mostre un exemple de com deura de ser:
```php
<?php
// Carrega slim i la base de dades
require '../vendor/autoload.php';
require './db.php';

$db = new Database(); 
$pdo = $db->connect();

// Crear l'aplicació Slim
$app = \Slim\Factory\AppFactory::create();

// Definir una ruta bàsica
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("<p>¡Hola, mundo!</p>");
    return $response;
});
// Carrega els altres endpoints
require './routes/routes.php';

// Executar l'aplicació
$app->run();
?>
```
### Fitxer routes.php
Aquest fitxer serà on farem els endpoints, principalment de les taules de la base de dades amb les peticions de la API.
Ara en els seguents apartats entrarem en detall sobre les peticons que podem fer.
#### GET
Utilitzem `GET` per a obtindre dades de la base de dades.
Ací un codi d'exemple:
```php
$app->get('/usuarios', function ($request, $response, $args) use ($pdo) {
// Realitzem la consulta per a obtindre els usuaris
$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Creem un array amb els usuaris i el número total d'usuaris
$data = [
    'total' => count($usuarios),  // Contem el número d'usuaris
    'usuarios' => $usuarios       // Les dades dels usuaris
];

// Codifiquem el resultat en JSON
$response->getBody()->write(json_encode($data));

// Establim el header Content-Type a application/json
return $response->withHeader('Content-Type', 'application/json');
});
```
#### POST
Utilitzem `POST` per a afegir dades de la base de dades.
Ací un codi d'exemple:
```php

$app->post('/usuarios', function ($request, $response, $args) use ($pdo) {
    $data = json_decode($request->getBody(), true);
    // Dades per a enviar a la base de dades
    $nombre = $data['nombre'] ?? '';
    $email = $data['email'] ?? '';

    // Validacions
    if (empty($nombre) || empty($email)) {
        $payload = [
            'error' => true,
            'message' => 'El nombre y el email son requeridos.'
        ];
        $response->getBody()->write(json_encode($payload));
        // Codi de HTTP 400: Error en el client
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $payload = [
            'error' => true,
            'message' => 'El email es inválido.'
        ];
        $response->getBody()->write(json_encode($payload));
        // Codi de HTTP 400: Error en el client
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        // Realitzem la consulta per a afegir usuaris
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email) VALUES (?, ?)");
        //Passem els parametres per a afegir-los a la base de dades
        $stmt->execute([$nombre, $email]);
        $id = $pdo->lastInsertId();
        // JSON per a la petició POST
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
        //Codi de HTTP 201: Usuari creat
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        //JSON amb misstage d'error
        $payload = [
            'error' => true,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ];
        $response->getBody()->write(json_encode($payload));
        //Codi de HTTP 500: Error en el servidor
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});
```
#### UPDATE
Utilitzem `PUT` per a actualitzar dades de la base de dades.
Ací un codi d'exemple:
```php
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
```
#### DELETE
Utilitzem `DELETE` per a borrar dades de la base de dades.
Ací un codi d'exemple:
```php
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
```