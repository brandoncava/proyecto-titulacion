<?php
// control de acceso, va en todo archivo que no sea el login

require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/helpers.php';

// duracion de "Mantener la sesion" en el login (30 dias)
define('SESION_RECORDADA', 60 * 60 * 24 * 30);

if (session_status() === PHP_SESSION_NONE) {
    // que php no borre los datos de la sesion antes de que venza la cookie
    ini_set('session.gc_maxlifetime', (string)SESION_RECORDADA);
    session_start();
}

function hay_sesion()
{
    return isset($_SESSION['id'], $_SESSION['rol']);
}

function rol_actual()
{
    return (int)($_SESSION['rol'] ?? 0);
}

function puede($permiso)
{
    return hay_sesion() && rol_tiene_permiso(rol_actual(), $permiso);
}

function ruta_login()
{
    $dir = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? ""));

    // las paginas de modulo estan en frontend/<modulo>/, el login un nivel arriba
    return (basename($dir) === "frontend") ? "login.php" : "../login.php";
}

function requiere_sesion()
{
    if (!hay_sesion()) {
        header('Location: ' . ruta_login());
        exit;
    }
}

// se usa al inicio de cada pagina del frontend
function requiere_permiso($permiso)
{
    requiere_sesion();

    if (!puede($permiso)) {
        http_response_code(403);
        exit('No tienes permisos para acceder a esta sección.');
    }
}

// version para los manejadores de backend/php, no devuelven html de login
function requiere_permiso_api($permiso)
{
    if (!hay_sesion()) {
        http_response_code(401);
        exit('No autenticado.');
    }

    if (!puede($permiso)) {
        http_response_code(403);
        exit('No autorizado.');
    }
}
