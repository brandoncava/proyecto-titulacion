<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('clientes');

// cliente nuevo: mismo formulario que editar.php, vacio
$cliente = null;
$historial = null;

$seccion = 'clientes';
$migas = 'Clientes / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_customer.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/cliente_form.js"></script>
</body>
</html>
