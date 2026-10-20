<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('proveedores');

$proveedor = null;

$seccion = 'proveedores';
$migas = 'Proveedores / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_supplier.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/solo_digitos.js"></script>
</body>
</html>
