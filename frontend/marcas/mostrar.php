<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('catalogos');

$seccion = 'marcas';
$migas = 'Marcas';
$tablas = true;
$listado = [
    'titulo'    => 'Marcas',
    'subtitulo' => 'Fabricantes de los productos que vendes',
    'buscar'    => 'Buscar marca',
    'nuevo'     => ['Nueva marca', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Nombre', 'Productos', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/delete_marca.php' ?>
</body>
</html>
