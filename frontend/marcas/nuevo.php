<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$marca = null;

$seccion = 'marcas';
$migas = 'Marcas / Nueva';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_marca.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
