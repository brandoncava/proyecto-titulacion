# CK Computers — Sistema de ventas e inventario

Sistema web para CK Computers (Trujillo): ventas, compras, productos, stock, clientes, proveedores y usuarios con perfiles.

Hecho en PHP + MySQL/MariaDB, sin frameworks. Genera boletas y comprobantes en PDF con FPDF.

## Requisitos

- [XAMPP](https://www.apachefriends.org/) (Apache + PHP 8 + MariaDB), o cualquier servidor con PHP 7.4 o superior y MySQL/MariaDB
- Apache con `mod_rewrite` activado (viene activado en XAMPP)

## Instalación

1. **Copia el proyecto** en `C:\xampp\htdocs\ckcomputers_system`.
   La carpeta debe llamarse así, porque `.htaccess` usa `RewriteBase /ckcomputers_system/`.

2. **Crea la base de datos.** En phpMyAdmin (`http://localhost/phpmyadmin`):
   - crea una base llamada `ckcomputers_db` con cotejamiento `utf8mb4_general_ci`
   - selecciónala, ve a **Importar** y sube `database/ckcomputers_db.sql`

3. **Configura la conexión.** Copia `backend/config/config.example.php` como `backend/config/config.php` y ajusta los datos si tu MySQL no usa `root` sin contraseña:

   ```php
   return [
       'host' => 'localhost',
       'user' => 'root',
       'pass' => '',
       'name' => 'ckcomputers_db',
   ];
   ```

4. Abre **http://localhost/ckcomputers_system**.

## Usuarios de prueba

Todos los datos de la base son de prueba.

| Usuario    | Contraseña      | Perfil        | Qué puede hacer |
|------------|-----------------|---------------|-----------------|
| `admin01`  | `Admin2026!`    | Administrador | Todo: dashboard, usuarios, anular ventas |
| `admin02`  | `Admin2026!`    | Administrador | Igual que `admin01` |
| `cajero01` | `Cajero2026!`   | Cajero        | Ventas, productos (ver) y clientes |
| `emple01`  | `Empleado2026!` | Empleado      | Compras, productos, proveedores y categorías |

Los permisos de cada perfil se definen en `backend/config/roles.php`.

## Estructura

```
ckcomputers_system/
├── database/ckcomputers_db.sql   base de datos completa (estructura + datos de prueba)
├── frontend/                     páginas del sistema, una carpeta por módulo
│   ├── layout/                   cabecera, menú y pie comunes a todas las páginas
│   └── login.php
├── backend/
│   ├── config/                   conexión, sesión, permisos y utilidades
│   ├── php/                      procesan los formularios (guardar, editar, anular...)
│   ├── css/  js/  img/  fonts/
│   ├── fpdf/                     librería para los PDF
│   └── sql/                      cambios de la base ya incluidos en database/ckcomputers_db.sql
└── index.php                     redirige al login
```

## Notas

- `backend/config/config.php` no se sube al repositorio porque tiene las credenciales locales.
- Las fotos que suben los usuarios se guardan en `backend/img/subidas/`. En el repositorio solo están las de los productos de ejemplo.
