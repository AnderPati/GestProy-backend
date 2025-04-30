# GestProy - Backend (Laravel API)

Este es el backend de **GestProy**, una aplicación de gestión de proyectos desarrollada con Laravel. Provee una API RESTful que sirve datos para el frontend Angular.

> 🔗 Proyecto complementario del frontend: [GestProy - Frontend (Angular)](https://github.com/AnderPati/GestProy-forntend)

## 📦 Requisitos

- PHP >= 8.1
- Composer
- MySQL o MariaDB
- Laravel 10+
- Extensiones: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `fileinfo`, `zip`

## ⚙️ Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/tuusuario/gestproy-backend.git
cd gestproy-backend
```

2. Instala las dependencias de Laravel:

```bash
composer install
```

3. Crea y configura el archivo `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configura la base de datos en `.env`:

```env
DB_DATABASE=gestproy
DB_USERNAME=root
DB_PASSWORD=secret
```

5. Ejecuta las migraciones:

```bash
php artisan migrateartisan db:seed
```

6. Inicia el servidor:

```bash
php artisan serve
```

## 🛠 Funcionalidades Principales

- Registro e inicio de sesión con Sanctum
- Gestión de usuarios y perfil
- CRUD de proyectos y tareas
- Subida y gestión de archivos (PDF, imágenes, etc.)
- Vista previa de imágenes con SweetAlert
- Estructura de carpetas por proyecto y sistema de archivos físico
- Límite de almacenamiento configurable por usuario
- Soporte para descargar carpetas como ZIP
- Centro de tareas global (por usuario) con filtros

## 🔐 Seguridad

- Autenticación con Laravel Sanctum
- Middleware `auth:sanctum` en rutas protegidas
- Validaciones robustas con `FormRequest` o `validate()`

## 📡 Endpoints API (resumen)

- `/api/login`
- `/api/register`
- `/api/projects`
- `/api/projects/{id}/tasks`
- `/api/projects/{id}/files`
- `/api/files/{id}`
- `/api/profile`
- `/api/tasks`

## 📂 Archivos y Carpeta

- Archivos subidos se almacenan en `storage/app/private/projects/{project_id}/files`
- Se accede a ellos mediante rutas protegidas
- Laravel `Storage::download()` gestiona descargas

## 📤 Deployment

Asegúrate de:

- Configurar correctamente los permisos de `storage`
- Ejecutar `php artisan config:cache`
- Configurar correctamente `.env` para producción