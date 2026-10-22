<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('ventas');

$tipoCarrito = 'venta';
$seccion = 'ventas';
$migas = 'Ventas / Nueva';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../comun/elegir_productos.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/add_cart.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/carrito.js"></script>
</body>
</html>
