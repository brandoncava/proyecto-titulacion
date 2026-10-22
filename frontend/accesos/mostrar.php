<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('usuarios');

$seccion = 'accesos';
$migas = 'Accesos';
$tablas = true;
$listado = [
    'titulo'    => 'Usuarios',
    'subtitulo' => 'Accesos al sistema y su perfil',
    'buscar'    => 'Buscar nombre, usuario o correo',
    'nuevo'     => ['Nuevo usuario', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Nombre', 'Correo', 'Perfil', 'Estado', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
</body>
</html>
