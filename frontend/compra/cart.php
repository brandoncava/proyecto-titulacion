<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('compras');

$tipoCarrito = 'compra';
$seccion = 'compras';
$migas = 'Compras / Carrito';
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../comun/carrito.php';
require __DIR__ . '/../layout/pie.php';
?>
    <?php include_once '../../backend/php/upd_cart_purchase.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
