<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('catalogos');

$seccion = 'categorias';
$migas = 'Categorías';
$tablas = true;
$listado = [
    'titulo'    => 'Categorías',
    'subtitulo' => 'Agrupan los productos del catálogo',
    'buscar'    => 'Buscar categoría',
    'nuevo'     => ['Nueva categoría', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Nombre', 'Productos', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/delete_category.php' ?>
</body>
</html>
