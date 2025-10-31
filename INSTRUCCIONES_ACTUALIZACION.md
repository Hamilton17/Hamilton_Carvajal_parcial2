# 🔄 Instrucciones de Actualización

Si clonaste el repositorio y solo ves texto JSON en lugar de la interfaz visual, sigue estos pasos:

## 📋 Pasos para Actualizar

### 1. Detener los contenedores actuales
```bash
docker-compose down
```

### 2. Descargar la imagen actualizada desde Docker Hub
```bash
docker-compose pull
```

### 3. Iniciar los contenedores con la nueva imagen
```bash
docker-compose up -d
```

### 4. Esperar unos segundos para que MySQL se inicialice
```bash
# Espera 10 segundos
sleep 10
```

### 5. Acceder a la interfaz visual
Abre tu navegador y ve a:
```
http://localhost:8080/test.html
```

---

## ✅ Verificación

Deberías ver:
- ✅ Una interfaz visual con colores morados/azules
- ✅ Formulario para crear usuarios con opción de subir imagen
- ✅ Lista de usuarios con avatares
- ✅ Botones de editar y eliminar en cada usuario

---

## 🐛 Si aún no funciona

### Opción A: Forzar recreación de contenedores
```bash
docker-compose down
docker-compose up -d --force-recreate
```

### Opción B: Limpiar todo y empezar de cero
```bash
# Detener y eliminar todo
docker-compose down -v

# Eliminar imagen local antigua
docker rmi kirytosao/parcial2:2.0

# Descargar imagen actualizada
docker pull kirytosao/parcial2:2.0

# Iniciar contenedores
docker-compose up -d
```

### Opción C: Reconstruir la imagen localmente
```bash
# Detener contenedores
docker-compose down

# Reconstruir la imagen desde el Dockerfile
docker build -t kirytosao/parcial2:2.0 .

# Iniciar contenedores
docker-compose up -d
```

---

## 📊 Verificar que la imagen está actualizada

```bash
# Ver el ID de la imagen
docker images kirytosao/parcial2:2.0

# El IMAGE ID debería ser: 2820e19baea1 (primeros dígitos)
# O ejecuta:
docker inspect kirytosao/parcial2:2.0 --format='{{.Id}}'
```

---

## 🆘 Contacto

Si ninguna de estas opciones funciona, contacta al administrador del repositorio.

---

## 📝 Nota Técnica

El problema ocurría porque la imagen anterior en Docker Hub no incluía el archivo `test.html`.
La versión actualizada (digest: sha256:2820e19baea1...) ya lo incluye.
