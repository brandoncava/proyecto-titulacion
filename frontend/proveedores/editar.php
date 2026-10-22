<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('proveedores');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare('SELECT idprov, rucprv, nomprv, corrprv FROM proveedores WHERE idprov = ? AND state = 1');
$b->execute([$id]);
$proveedor = $b->fetch();

$seccion = 'proveedores';
$migas = 'Proveedores / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$proveedor): ?>
                <p class="alert">No se encontró el proveedor.</p>
<?php else: ?>
<?php include __DIR__ . '/formulario.php'; ?>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_supplier.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/solo_digitos.js"></script>
</body>
</html>
