<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('clientes');

require '../../backend/config/Conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare('SELECT idcli, tipd, nudoc, nocl, apcl, telfcl, emailcl, acepta_promos, acepta_promos_on FROM clientes WHERE idcli = ?');
$b->execute([$id]);
$cliente = $b->fetch();

// compras validas del cliente (las ventas quedan enlazadas a el por su documento)
$historial = null;
if ($cliente) {
    $h = $connect->prepare(
        'SELECT COUNT(*) AS compras, COALESCE(SUM(total_price), 0) AS total, MAX(placed_on) AS ultima
           FROM orders WHERE idcli = ? AND anulada = 0'
    );
    $h->execute([$cliente->idcli]);
    $historial = $h->fetch();
}

$seccion = 'clientes';
$migas = 'Clientes / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$cliente): ?>
                <p class="alert">No se encontró el cliente.</p>
<?php else: ?>
<?php include __DIR__ . '/formulario.php'; ?>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_customer.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/cliente_form.js"></script>
</body>
</html>
