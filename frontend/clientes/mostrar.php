<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('clientes');

$seccion = 'clientes';
$migas = 'Clientes';
$tablas = true;
$listado = [
    'titulo'    => 'Clientes',
    'subtitulo' => 'Personas (DNI) y empresas (RUC)',
    'buscar'    => 'Buscar nombre, DNI/RUC o correo',
    'nuevo'     => ['Nuevo cliente', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Cliente', 'Contacto', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/delete_customer.php' ?>
</body>
</html>
