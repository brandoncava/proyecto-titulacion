<?php
// punto de entrada comun de todas las paginas y manejadores: una sola linea en cada archivo,
//   require_once __DIR__ . '/<ruta hasta>/backend/config/app.php';
// Carga la sesion y los permisos (auth.php, que trae roles.php y helpers.php) y la conexion a la
// base de datos en $connect (Conexion.php, con las credenciales de config.php).
// Lo que deba valer para todo el sistema se agrega aqui.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/Conexion.php';
