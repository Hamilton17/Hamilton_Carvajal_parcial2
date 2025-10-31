<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuración de base de datos
$host = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_NAME') ?: 'parcial_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'rootpassword';

// Conexión a base de datos
function getDB() {
    global $host, $dbname, $username, $password;
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
        exit;
    }
}

// Obtener método y path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Parsear ID de la URL
$matches = [];
if (preg_match('#/api\.php/users/(\d+)#', $path, $matches)) {
    $userId = (int)$matches[1];
} else {
    $userId = null;
}

// Rutas
if ($path === '/api.php/users' || $path === '/api.php/users/') {
    $pdo = getDB();

    // GET - Listar usuarios
    if ($method === 'GET') {
        $stmt = $pdo->query('SELECT id, nombre, email, imagen, created_at FROM usuarios ORDER BY created_at DESC');
        $users = $stmt->fetchAll();
        echo json_encode([
            'success' => true,
            'count' => count($users),
            'users' => $users
        ]);
    }

    // POST - Crear usuario
    elseif ($method === 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $imagen = null;

        // Manejo de imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '/var/www/html/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $targetPath)) {
                $imagen = 'uploads/' . $filename;
            }
        }

        if (empty($nombre) || empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y email son requeridos']);
            exit;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre, email, imagen) VALUES (:nombre, :email, :imagen)');
            $stmt->execute(['nombre' => $nombre, 'email' => $email, 'imagen' => $imagen]);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'id' => $pdo->lastInsertId()
            ]);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al crear usuario: ' . $e->getMessage()]);
        }
    }

} elseif ($userId && preg_match('#/api\.php/users/\d+#', $path)) {
    $pdo = getDB();

    // GET - Obtener usuario
    if ($method === 'GET') {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if ($user) {
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
        }
    }

    // PUT - Actualizar usuario
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = $data['nombre'] ?? '';
        $email = $data['email'] ?? '';

        if (empty($nombre) || empty($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y email son requeridos']);
            exit;
        }

        try {
            $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre, email = :email WHERE id = :id');
            $stmt->execute(['nombre' => $nombre, 'email' => $email, 'id' => $userId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    // DELETE - Eliminar usuario
    elseif ($method === 'DELETE') {
        try {
            // Obtener imagen para eliminarla
            $stmt = $pdo->prepare('SELECT imagen FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);
            $user = $stmt->fetch();

            if ($user && $user['imagen']) {
                $imagePath = '/var/www/html/' . $user['imagen'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
            $stmt->execute(['id' => $userId]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Usuario no encontrado']);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint no encontrado']);
}
