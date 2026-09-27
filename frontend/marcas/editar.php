<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$seccion = 'marcas';
$migas = 'Marcas / Actualizar';
require __DIR__ . '/../layout/cabecera.php';

require '../../backend/config/Conexion.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$sentencia = $connect->prepare('SELECT * FROM marca WHERE idmar = ? AND state = 1');
$sentencia->execute([$id]);
$d = $sentencia->fetch();
?>

            <div class="page-content">
<?php if ($d): ?>
<form action="" method="POST" autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Actualizar marca</h1>
    <hr>
    <br>

    <label for="nommar"><b>Nombre de la marca</b></label><span class="badge-warning">*</span>
    <input type="text" id="nommar" value="<?php echo e($d->nomarc); ?>" placeholder="ejm: Lenovo" name="nommar" maxlength="100" required>
    <input type="hidden" name="idmar" value="<?php echo e($d->idmar); ?>">
    <hr>

    <button type="submit" name="upd_marca" class="registerbtn">Guardar</button>
  </div>
</form>
<?php else: ?>
      <p class="alert alert-warning">No hay datos</p>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_marca.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
