<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('catalogos');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare('SELECT idcate, nocate FROM categoria WHERE idcate = ? AND state = 1');
$b->execute([$id]);
$categoria = $b->fetch();

$seccion = 'categorias';
$migas = 'Categorías / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$categoria): ?>
                <p class="alert">No se encontró la categoría.</p>
<?php else: ?>
<?php include __DIR__ . '/formulario.php'; ?>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_category.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
