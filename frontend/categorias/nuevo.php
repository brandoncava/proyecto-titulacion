<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$seccion = 'categorias';
$migas = 'Categorias / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nuevas categorias</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  
    <label for="email"><b>Nombre de la categoria</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: Laptos" name="catnom"  required>
    <hr>
   
    <button type="submit" name="add_category" class="registerbtn">Guardar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_category.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
