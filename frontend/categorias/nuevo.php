<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('catalogos');

$categoria = null;

$seccion = 'categorias';
$migas = 'Categorías / Nueva';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_category.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
