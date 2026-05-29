# GestProy - Backend API

Backend de **GestProy**, una aplicación full stack para la gestión de proyectos, tareas y archivos.

Este repositorio contiene una **API REST desarrollada con Laravel**, encargada de gestionar la autenticación de usuarios, proyectos, tareas, perfil de usuario, almacenamiento de archivos y otras funcionalidades necesarias para el frontend desarrollado en Angular.

> Proyecto complementario del frontend: [GestProy - Frontend Angular](https://github.com/AnderPati/GestProy-forntend)

---

## Índice

- [Descripción](#descripción)
- [Tecnologías utilizadas](#tecnologías-utilizadas)
- [Funcionalidades principales](#funcionalidades-principales)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración del entorno](#configuración-del-entorno)
- [Ejecución del proyecto](#ejecución-del-proyecto)
- [Autenticación](#autenticación)
- [Endpoints principales](#endpoints-principales)
- [Gestión de archivos](#gestión-de-archivos)
- [Relación con el frontend](#relación-con-el-frontend)
- [Estado del proyecto](#estado-del-proyecto)
- [Autor](#autor)

---

## Descripción

**GestProy** es una aplicación web pensada para organizar proyectos personales o profesionales mediante tareas, archivos y herramientas de seguimiento.

Este backend proporciona los servicios necesarios para que el frontend pueda trabajar con datos reales mediante una API REST. Entre sus responsabilidades principales se incluyen:

- autenticación de usuarios;
- gestión de proyectos;
- gestión de tareas;
- gestión del perfil de usuario;
- subida y descarga de archivos;
- organización de archivos por proyecto;
- control de almacenamiento;
- rutas protegidas mediante token.

El objetivo del proyecto es practicar y demostrar el desarrollo de una aplicación full stack utilizando **Laravel en el backend** y **Angular en el frontend**.

---

## Tecnologías utilizadas

- PHP 8.1+
- Laravel 10+
- Laravel Sanctum
- MySQL / MariaDB
- Eloquent ORM
- Composer
- API REST
- Laravel Storage
- Validaciones con Laravel
- Middleware de autenticación
- Gestión de archivos
- Descarga de carpetas en formato ZIP

---

## Funcionalidades principales

- Registro de usuarios.
- Inicio de sesión.
- Cierre de sesión.
- Autenticación mediante tokens con Laravel Sanctum.
- Obtención de datos del usuario autenticado.
- Gestión del perfil de usuario.
- Actualización de datos del perfil.
- Gestión de imagen de perfil.
- CRUD de proyectos.
- CRUD de tareas asociadas a proyectos.
- Centro global de tareas por usuario.
- Filtros para tareas.
- Subida de archivos a proyectos.
- Descarga de archivos.
- Eliminación de archivos.
- Organización física de archivos por proyecto.
- Descarga de carpetas como archivo ZIP.
- Control de límite de almacenamiento por usuario.
- Rutas protegidas mediante middleware `auth:sanctum`.
- Validación de datos recibidos desde el frontend.

---

## Requisitos

Antes de instalar el proyecto, asegúrate de tener instalado:

- PHP >= 8.1
- Composer
- MySQL o MariaDB
- Laravel 10+
- Servidor local tipo XAMPP, Laragon, Laravel Herd o similar

También son necesarias las siguientes extensiones de PHP:

- `pdo`
- `mbstring`
- `openssl`
- `tokenizer`
- `xml`
- `fileinfo`
- `zip`

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/AnderPati/gestproy-backend.git
cd gestproy-backend
```

> Si el nombre real del repositorio es diferente, sustituye la URL por la correspondiente.


### 2. Instalar dependencias

```bash
composer install
```


### 3. Crear el archivo `.env`

```bash
cp .env.example .env
```

En Windows, si el comando anterior no funciona, puedes copiar manualmente el archivo `.env.example` y renombrarlo a `.env`.


### 4. Generar la clave de la aplicación

```bash
php artisan key:generate
```


## Configuración del entorno

Abre el archivo `.env` y configura la conexión con la base de datos.

Ejemplo de configuración local:

```env
APP_NAME=GestProy
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestproy
DB_USERNAME=root
DB_PASSWORD=
```

Crea previamente una base de datos vacía llamada:

```bash
gestproy
```

También puedes cambiar el nombre de la base de datos si lo necesitas, siempre que coincida con el valor definido en `DB_DATABASE`.

---

## Ejecución del proyecto

### 1. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

Si prefieres hacerlo por separado:

```bash
php artisan migrate
php artisan db:seed
```


### 2. Crear el enlace simbólico de almacenamiento

```bash
php artisan storage:link
```

Este comando permite acceder desde `public/storage` a los archivos almacenados en `storage/app/public`.


### 3. Iniciar el servidor de desarrollo

```bash
php artisan serve
```

Por defecto, la API estará disponible en:

```bash
http://127.0.0.1:8000
```

## Autenticación

La API utiliza **Laravel Sanctum** para proteger las rutas privadas.

Cuando un usuario inicia sesión correctamente, el backend devuelve un token de autenticación.  
El frontend debe enviar ese token en las siguientes peticiones mediante el header:

```http
Authorization: Bearer TOKEN
```

Las rutas privadas están protegidas mediante el middleware:

```php
auth:sanctum
```

Ejemplo de flujo básico:

1. El usuario inicia sesión desde el frontend.
2. El backend valida las credenciales.
3. Se genera un token con Laravel Sanctum.
4. El frontend guarda el token.
5. Las siguientes peticiones se realizan enviando el token en el header `Authorization`.
6. El backend valida el token antes de permitir el acceso a los recursos protegidos.

---

## Endpoints principales

### Autenticación

| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/register` | Registrar un nuevo usuario |
| POST | `/api/login` | Iniciar sesión |
| POST | `/api/logout` | Cerrar sesión |
| GET | `/api/user` | Obtener el usuario autenticado |

---

### Perfil

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/profile` | Obtener datos del perfil |
| PUT/PATCH | `/api/profile` | Actualizar datos del perfil |
| DELETE | `/api/profile/image` | Eliminar imagen de perfil |

---

### Proyectos

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/projects` | Listar proyectos del usuario |
| POST | `/api/projects` | Crear un nuevo proyecto |
| GET | `/api/projects/{id}` | Obtener detalle de un proyecto |
| PUT/PATCH | `/api/projects/{id}` | Actualizar un proyecto |
| DELETE | `/api/projects/{id}` | Eliminar un proyecto |

---

### Tareas

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/tasks` | Listar tareas del usuario |
| GET | `/api/tasks/upcoming` | Obtener próximas tareas |
| GET | `/api/projects/{project_id}/tasks` | Listar tareas de un proyecto |
| POST | `/api/projects/{project_id}/tasks` | Crear tarea en un proyecto |
| GET | `/api/tasks/{id}` | Obtener detalle de una tarea |
| PUT/PATCH | `/api/tasks/{id}` | Actualizar una tarea |
| DELETE | `/api/tasks/{id}` | Eliminar una tarea |

---

### Archivos

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/projects/{project_id}/files` | Listar archivos de un proyecto |
| POST | `/api/projects/{project_id}/files` | Subir archivo a un proyecto |
| GET | `/api/files/{id}` | Descargar archivo |
| DELETE | `/api/files/{id}` | Eliminar archivo |
| GET | `/api/projects/{project_id}/folders/download` | Descargar carpeta del proyecto en ZIP |

> Los endpoints pueden variar ligeramente según la implementación final del proyecto.

---

## Gestión de archivos

El backend permite subir, organizar, descargar y eliminar archivos asociados a proyectos.

Los archivos se almacenan utilizando el sistema de archivos de Laravel.

Ejemplo de estructura interna:

```bash
storage/app/private/projects/{project_id}/files
```

El acceso a los archivos se realiza mediante rutas protegidas, evitando que usuarios no autorizados puedan acceder a documentos de otros proyectos.

El sistema permite trabajar con diferentes tipos de archivos, como:

- imágenes;
- documentos PDF;
- archivos de texto;
- otros archivos asociados a proyectos.

También existe soporte para descargar carpetas completas en formato `.zip`.

---

## Relación con el frontend

Este backend está diseñado para trabajar junto con el frontend de GestProy desarrollado en Angular.

Repositorio del frontend:

```bash
https://github.com/AnderPati/GestProy-frontend
```

El frontend consume esta API para gestionar:

- registro e inicio de sesión;
- sesión del usuario;
- proyectos;
- tareas;
- perfil;
- subida y descarga de archivos;
- estadísticas;
- centro global de tareas;
- filtros y búsquedas.

---

## Uso con el frontend Angular

Para conectar correctamente el frontend con este backend, asegúrate de que la URL de la API en Angular apunte al servidor de Laravel.

Ejemplo en entorno local:

```ts
http://127.0.0.1:8000/api
```

También es importante configurar correctamente CORS en Laravel para permitir peticiones desde el servidor del frontend, normalmente:

```ts
http://localhost:4200
```

---

## Estado del proyecto

Este proyecto fue desarrollado como una aplicación full stack personal para practicar y demostrar conocimientos de desarrollo web moderno.

Actualmente está orientado principalmente a ejecución en entorno local.

El despliegue en producción requiere configurar correctamente:

- variables de entorno;
- base de datos;
- permisos de almacenamiento;
- configuración de CORS;
- rutas públicas y privadas;
- sistema de archivos;
- generación y uso de tokens;
- seguridad del servidor.

---

## Autor

**Ander Patino Pacheco**

Desarrollador Web Junior.

- GitHub: [AnderPati](https://github.com/AnderPati)
- Portfolio: próximamente
