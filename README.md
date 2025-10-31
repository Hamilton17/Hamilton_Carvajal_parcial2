# Parcial Práctico Docker - API de Usuarios PHP + MySQL

Proyecto de aplicación web contenerizada usando Docker, PHP 8.2 con Apache y MySQL 8.0.

## Descripción

Esta es una API REST simple desarrollada en PHP que permite gestionar usuarios con operaciones básicas de lectura y creación, conectada a una base de datos MySQL.

## Características

- API REST en PHP 8.2 sin framework
- Base de datos MySQL 8.0
- Contenedores Docker con docker-compose
- Imagen personalizada publicada en Docker Hub
- Validación de datos de entrada
- Manejo de errores

## Endpoints Disponibles

### GET /
Muestra información general de la API

**Respuesta:**
```json
{
  "message": "API de Usuarios - Parcial Práctico Docker",
  "endpoints": {
    "GET /users": "Obtener lista de usuarios",
    "POST /users": "Crear un nuevo usuario (nombre, email)"
  }
}
```

### GET /index.php/users
Obtiene la lista de todos los usuarios

**Respuesta:**
```json
{
  "success": true,
  "count": 3,
  "users": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "email": "juan.perez@example.com",
      "created_at": "2025-10-31 02:03:06"
    }
  ]
}
```

### POST /index.php/users
Crea un nuevo usuario

**Request Body:**
```json
{
  "nombre": "Pedro Rodriguez",
  "email": "pedro.rodriguez@example.com"
}
```

**Respuesta (201):**
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "user": {
    "id": "4",
    "nombre": "Pedro Rodriguez",
    "email": "pedro.rodriguez@example.com"
  }
}
```

## Requisitos Previos

- Docker Desktop instalado
- Docker Compose
- Cuenta en Docker Hub (para el push de la imagen)

## Estructura del Proyecto

```
.
├── db/
│   └── init.sql              # Script de inicialización de la base de datos
├── src/
│   └── index.php             # Código fuente de la aplicación PHP
├── Dockerfile                # Definición de la imagen Docker
├── docker-compose.yml        # Configuración de los servicios
├── .gitignore               # Archivos a ignorar por Git
└── README.md                # Este archivo
```

## Instalación y Uso

### 1. Clonar el repositorio

```bash
git clone https://github.com/Hamilton17/Hamilton_Carvajal_parcial2.git
cd Hamilton_Carvajal_parcial2
```

### 2. Construir la imagen Docker (opcional)

La imagen ya está disponible en Docker Hub, pero si deseas construirla localmente:

```bash
docker build -t kirytosao/parcial2:1.0 .
```

### 3. Iniciar los contenedores

```bash
docker-compose up -d
```

Este comando:
- Descarga la imagen `kirytosao/parcial2:1.0` desde Docker Hub
- Descarga la imagen de MySQL 8.0
- Crea una red para la comunicación entre contenedores
- Inicia el contenedor de MySQL con la base de datos inicializada
- Inicia el contenedor de PHP/Apache

### 4. Verificar que los contenedores están corriendo

```bash
docker-compose ps
```

### 5. Probar la aplicación

**Endpoint raíz:**
```bash
curl http://localhost:8080/
```

**Listar usuarios:**
```bash
curl http://localhost:8080/index.php/users
```

**Crear un nuevo usuario:**
```bash
curl -X POST http://localhost:8080/index.php/users \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Pedro Rodriguez","email":"pedro.rodriguez@example.com"}'
```

### 6. Detener los contenedores

```bash
docker-compose down
```

Para eliminar también los volúmenes (datos de la base de datos):
```bash
docker-compose down -v
```

## Configuración

### Variables de Entorno

El archivo `docker-compose.yml` define las siguientes variables de entorno:

**Servicio PHP:**
- `DB_HOST`: mysql
- `DB_NAME`: parcial_db
- `DB_USER`: root
- `DB_PASSWORD`: rootpassword

**Servicio MySQL:**
- `MYSQL_ROOT_PASSWORD`: rootpassword
- `MYSQL_DATABASE`: parcial_db

### Puertos

- **PHP/Apache**: 8080 → 80
- **MySQL**: 3306 → 3306

## Tecnologías Utilizadas

- **PHP**: 8.2-apache
- **MySQL**: 8.0
- **Docker**: Containerización
- **Docker Compose**: Orquestación de contenedores
- **Apache**: Servidor web
- **PDO**: PHP Data Objects para conexión a MySQL

## Imagen en Docker Hub

La imagen de este proyecto está disponible públicamente en:

```
kirytosao/parcial2:1.0
```

Para descargarla directamente:
```bash
docker pull kirytosao/parcial2:1.0
```

## Características de Seguridad

- Validación de campos requeridos
- Validación de formato de email
- Uso de Prepared Statements para prevenir SQL Injection
- Manejo de restricciones UNIQUE en la base de datos
- Sanitización de entradas de usuario

## Desarrollo

### Reconstruir la imagen después de cambios

Si realizas cambios en el código fuente:

1. Reconstruir la imagen:
```bash
docker build -t kirytosao/parcial2:1.0 .
```

2. Hacer push a Docker Hub:
```bash
docker push kirytosao/parcial2:1.0
```

3. Reiniciar los contenedores:
```bash
docker-compose down
docker-compose up -d
```

### Ver logs

**Logs de la aplicación PHP:**
```bash
docker logs parcial-php-app
```

**Logs de MySQL:**
```bash
docker logs parcial-mysql
```

**Seguir logs en tiempo real:**
```bash
docker-compose logs -f
```

## Solución de Problemas

### La aplicación no se conecta a la base de datos

Espera unos segundos después de iniciar los contenedores para que MySQL termine de inicializarse. Puedes verificar el estado con:

```bash
docker-compose ps
```

### Error "Port already in use"

Si los puertos 8080 o 3306 están en uso, modifica el archivo `docker-compose.yml` para usar otros puertos.

### Ver el estado de salud de MySQL

```bash
docker inspect parcial-mysql | grep -A 10 Health
```

## Autor

Desarrollado como proyecto práctico de Docker para la materia de contenedores y orquestación.

## Licencia

Este proyecto es de código abierto y está disponible para fines educativos.
