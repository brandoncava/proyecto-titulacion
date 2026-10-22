<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('proveedores');

$seccion = 'proveedores';
$migas = 'Proveedores';
$tablas = true;
$listado = [
    'titulo'    => 'Proveedores',
    'subtitulo' => 'Empresas a las que les compras mercadería',
    'buscar'    => 'Buscar razón social, RUC o correo',
    'nuevo'     => ['Nuevo proveedor', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Proveedor', 'Correo', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/delete_supplier.php' ?>
</body>
</html>
