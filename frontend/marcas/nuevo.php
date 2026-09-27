<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$seccion = 'marcas';
$migas = 'Marcas / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

<form action="" method="POST" autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nueva marca</h1>
    <div class="alert-danger">
      <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
      <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
    </div>
    <hr>
    <br>

    <label for="nommar"><b>Nombre de la marca</b></label><span class="badge-warning">*</span>
    <input type="text" id="nommar" placeholder="ejm: Lenovo" name="nommar" maxlength="100" required>
    <hr>

    <button type="submit" name="ins_marca" class="registerbtn">Guardar</button>
  </div>
</form>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_marca.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
