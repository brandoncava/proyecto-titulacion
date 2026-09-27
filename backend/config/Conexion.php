<?php
// conexion a la bd, credenciales en config.php

require_once __DIR__ . '/auth.php';

$configLocal = __DIR__ . '/config.php';
$cfg = file_exists($configLocal) ? require $configLocal : [];

define('dbhost', $cfg['host'] ?? 'localhost');
define('dbuser', $cfg['user'] ?? 'root');
define('dbpass', $cfg['pass'] ?? '');
define('dbname', $cfg['name'] ?? 'ckcomputers_db');

try {
    $connect = new PDO(
        'mysql:host=' . dbhost . ';dbname=' . dbname . ';charset=utf8mb4',
        dbuser,
        dbpass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // no mostrar el detalle del error al usuario
    error_log('Error de conexion a la base de datos: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos. Avisa al administrador.');
}
