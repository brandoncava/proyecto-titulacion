<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('productos');

$seccion = 'productos';
$migas = 'Productos';
$tablas = true;
$listado = [
    'titulo'    => 'Productos',
    'subtitulo' => 'Catálogo con precio de venta y stock',
    'buscar'    => 'Buscar producto, código o marca',
    'nuevo'     => puede('productos_ed') ? ['Nuevo producto', 'nuevo.php'] : null,
    'datos'     => 'datos.php',
    'columnas'  => [['Foto', false], 'Código', 'Producto', 'Marca', 'Modelo', 'Categoría', 'Precio', 'Stock', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/delete_product.php' ?>
</body>
</html>
