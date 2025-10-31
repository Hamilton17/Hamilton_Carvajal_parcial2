<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'parcial_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'rootpassword';

// Función para conectar a la base de datos
function getDBConnection() {
    global $host, $dbname, $username, $password;

    try {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// Obtener método HTTP y URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Enrutamiento simple
if ($uri === '/' || $uri === '/index.php') {
    echo json_encode([
        'message' => 'API de Usuarios - Parcial Práctico Docker',
        'endpoints' => [
            'GET /users' => 'Obtener lista de usuarios',
            'POST /users' => 'Crear un nuevo usuario (nombre, email)'
        ]
    ]);
    exit;
}

if ($uri === '/users' || $uri === '/index.php/users') {
    $pdo = getDBConnection();

    if ($method === 'GET') {
        // Obtener todos los usuarios
        try {
            $stmt = $pdo->query('SELECT id, nombre, email, created_at FROM usuarios ORDER BY id DESC');
            $users = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'count' => count($users),
                'users' => $users
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'POST') {
        // Crear un nuevo usuario
        try {
            // Obtener datos del POST
            $input = json_decode(file_get_contents('php://input'), true);

            // Validación básica
            if (!isset($input['nombre']) || !isset($input['email'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos nombre y email son requeridos']);
                exit;
            }

            $nombre = trim($input['nombre']);
            $email = trim($input['email']);

            if (empty($nombre) || empty($email)) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos nombre y email no pueden estar vacíos']);
                exit;
            }

            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                echo json_encode(['error' => 'El formato del email no es válido']);
                exit;
            }

            // Insertar usuario
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email) VALUES (:nombre, :email)');
            $stmt->execute([
                'nombre' => $nombre,
                'email' => $email
            ]);

            $userId = $pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user' => [
                    'id' => $userId,
                    'nombre' => $nombre,
                    'email' => $email
                ]
            ]);
        } catch (PDOException $e) {
            // Error de duplicado de email (asumiendo que hay una constraint UNIQUE)
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está registrado']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear usuario: ' . $e->getMessage()]);
            }
        }
        exit;
    }
}

// Si no coincide ninguna ruta
http_response_code(404);
echo json_encode(['error' => 'Endpoint no encontrado']);
