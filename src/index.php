<?php
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
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
        exit;
    }
}

// Función para manejar la subida de imágenes
function handleImageUpload($file) {
    $uploadDir = '/var/www/html/uploads/';

    // Crear directorio si no existe
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WEBP'];
    }

    if ($file['size'] > $maxSize) {
        return ['error' => 'El archivo es demasiado grande. Máximo 5MB'];
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('user_', true) . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => 'uploads/' . $filename];
    }

    return ['error' => 'Error al subir el archivo'];
}

// Función para eliminar imagen
function deleteImage($imagePath) {
    if ($imagePath && file_exists('/var/www/html/' . $imagePath)) {
        unlink('/var/www/html/' . $imagePath);
    }
}

// Obtener método HTTP y URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Enrutamiento simple
if ($uri === '/' || $uri === '/index.php') {
    header('Content-Type: application/json');
    echo json_encode([
        'message' => 'API de Usuarios - Parcial Práctico Docker',
        'endpoints' => [
            'GET /users' => 'Obtener lista de usuarios',
            'POST /users' => 'Crear un nuevo usuario (nombre, email, imagen)',
            'PUT /users/{id}' => 'Actualizar un usuario',
            'DELETE /users/{id}' => 'Eliminar un usuario'
        ]
    ]);
    exit;
}

// Manejo de rutas de usuarios
if ($uri === '/users' || $uri === '/index.php/users' || preg_match('#^/index\.php/users/(\d+)$#', $uri, $matches) || preg_match('#^/users/(\d+)$#', $uri, $matches)) {
    $pdo = getDBConnection();
    $userId = isset($matches[1]) ? (int)$matches[1] : null;

    // GET - Obtener usuarios
    if ($method === 'GET') {
        header('Content-Type: application/json');
        try {
            if ($userId) {
                // Obtener un usuario específico
                $stmt = $pdo->prepare('SELECT id, nombre, email, imagen, created_at, updated_at FROM usuarios WHERE id = :id');
                $stmt->execute(['id' => $userId]);
                $user = $stmt->fetch();

                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'user' => $user
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Usuario no encontrado']);
                }
            } else {
                // Obtener todos los usuarios
                $stmt = $pdo->query('SELECT id, nombre, email, imagen, created_at, updated_at FROM usuarios ORDER BY id DESC');
                $users = $stmt->fetchAll();

                echo json_encode([
                    'success' => true,
                    'count' => count($users),
                    'users' => $users
                ]);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
        }
        exit;
    }

    // POST - Crear usuario
    if ($method === 'POST' && !$userId) {
        $imagePath = null;

        // Verificar si es multipart/form-data (con imagen)
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = handleImageUpload($_FILES['imagen']);
            if (isset($uploadResult['error'])) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['error' => $uploadResult['error']]);
                exit;
            }
            $imagePath = $uploadResult['filename'];
        }

        // Obtener datos (multipart o JSON)
        $nombre = $_POST['nombre'] ?? null;
        $email = $_POST['email'] ?? null;

        // Si no viene por POST, intentar JSON
        if (!$nombre || !$email) {
            $input = json_decode(file_get_contents('php://input'), true);
            $nombre = $input['nombre'] ?? null;
            $email = $input['email'] ?? null;
        }

        // Validación básica
        if (!$nombre || !$email) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Los campos nombre y email son requeridos']);
            exit;
        }

        $nombre = trim($nombre);
        $email = trim($email);

        if (empty($nombre) || empty($email)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Los campos nombre y email no pueden estar vacíos']);
            exit;
        }

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'El formato del email no es válido']);
            exit;
        }

        try {
            // Insertar usuario
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, imagen) VALUES (:nombre, :email, :imagen)');
            $stmt->execute([
                'nombre' => $nombre,
                'email' => $email,
                'imagen' => $imagePath
            ]);

            $userId = $pdo->lastInsertId();

            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user' => [
                    'id' => $userId,
                    'nombre' => $nombre,
                    'email' => $email,
                    'imagen' => $imagePath
                ]
            ]);
        } catch (PDOException $e) {
            // Limpiar imagen si hubo error
            if ($imagePath) {
                deleteImage($imagePath);
            }

            header('Content-Type: application/json');
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

    // PUT - Actualizar usuario
    if ($method === 'PUT' && $userId) {
        header('Content-Type: application/json');

        // Obtener datos del PUT
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos inválidos']);
            exit;
        }

        try {
            // Verificar que el usuario existe
            $stmt = $pdo->prepare('SELECT imagen FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            // Construir query dinámicamente
            $updates = [];
            $params = ['id' => $userId];

            if (isset($input['nombre']) && trim($input['nombre']) !== '') {
                $updates[] = 'nombre = :nombre';
                $params['nombre'] = trim($input['nombre']);
            }

            if (isset($input['email']) && trim($input['email']) !== '') {
                $email = trim($input['email']);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'El formato del email no es válido']);
                    exit;
                }
                $updates[] = 'email = :email';
                $params['email'] = $email;
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['error' => 'No hay datos para actualizar']);
                exit;
            }

            $sql = 'UPDATE usuarios SET ' . implode(', ', $updates) . ' WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Obtener usuario actualizado
            $stmt = $pdo->prepare('SELECT id, nombre, email, imagen, created_at, updated_at FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $updatedUser = $stmt->fetch();

            echo json_encode([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'user' => $updatedUser
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                http_response_code(409);
                echo json_encode(['error' => 'El email ya está registrado']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar usuario: ' . $e->getMessage()]);
            }
        }
        exit;
    }

    // DELETE - Eliminar usuario
    if ($method === 'DELETE' && $userId) {
        header('Content-Type: application/json');

        try {
            // Obtener imagen antes de eliminar
            $stmt = $pdo->prepare('SELECT imagen FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
                exit;
            }

            // Eliminar usuario
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);

            // Eliminar imagen si existe
            if ($user['imagen']) {
                deleteImage($user['imagen']);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar usuario: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Si no coincide ninguna ruta
header('Content-Type: application/json');
http_response_code(404);
echo json_encode(['error' => 'Endpoint no encontrado']);
