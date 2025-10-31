# API de Usuarios - Parcial Docker

API REST completa con interfaz visual para gestión de usuarios usando PHP, MySQL y Docker.

## 🚀 Inicio Rápido

```bash
# 1. Clonar el repositorio
git clone https://github.com/Hamilton17/Hamilton_Carvajal_parcial2.git
cd Hamilton_Carvajal_parcial2

# 2. Iniciar los contenedores
docker-compose up -d

# 3. Abrir en el navegador
# http://localhost:8080
```

¡Eso es todo! La aplicación estará lista en el puerto 8080.

## 📦 Contenido del Proyecto

```
├── src/
│   ├── index.html      # Interfaz visual
│   ├── api.php         # API REST backend
│   ├── .htaccess       # Configuración Apache
│   └── uploads/        # Carpeta para imágenes
├── db/
│   └── init.sql        # Script de inicialización de BD
├── Dockerfile          # Imagen PHP + Apache
└── docker-compose.yml  # Orquestación de contenedores
```

## 🌐 Endpoints

### Interfaz Visual
- `http://localhost:8080` - Aplicación web completa

### API REST
- `GET /api.php/users` - Listar todos los usuarios
- `POST /api.php/users` - Crear nuevo usuario
- `PUT /api.php/users/{id}` - Actualizar usuario
- `DELETE /api.php/users/{id}` - Eliminar usuario

## ✨ Funcionalidades

- ✅ Crear usuarios con nombre, email e imagen
- ✅ Listar usuarios con avatares
- ✅ Editar información de usuarios
- ✅ Eliminar usuarios
- ✅ Subir imágenes de perfil
- ✅ Interfaz responsive y moderna
- ✅ API REST completa

## 🐳 Docker Hub

Imagen publicada: `kirytosao/parcial2:3.0`

```bash
docker pull kirytosao/parcial2:3.0
```

## 🛠️ Tecnologías

- **Backend**: PHP 8.2 + Apache
- **Base de Datos**: MySQL 8.0
- **Contenedores**: Docker + Docker Compose
- **Frontend**: HTML5 + CSS3 + JavaScript vanilla

## 📝 Comandos Útiles

```bash
# Ver logs
docker-compose logs -f

# Detener contenedores
docker-compose down

# Detener y eliminar volúmenes (resetear BD)
docker-compose down -v

# Reconstruir imagen
docker build -t kirytosao/parcial2:3.0 .

# Ver estado de contenedores
docker ps
```

## 👨‍💻 Autor

Hamilton Carvajal

## 📄 Licencia

Este proyecto es parte de un parcial académico.
