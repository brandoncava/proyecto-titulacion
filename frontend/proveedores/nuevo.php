<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('proveedores');

$seccion = 'proveedores';
$migas = 'Proveedores / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nuevos proveedores</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  

    <label for="email"><b>Ruc del proveedor</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="11" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 77656756" name="rcprv"  required>

    <label for="email"><b>Nombre del proveedor</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: Wong" name="nomprv"  required>

    <label for="email"><b>Correo del proveedor</b></label>
    <input type="text" placeholder="ejm: wong@gmail.com" name="corrprv" >

    <hr>
   
    <button type="submit" name="add_supplier" class="registerbtn">Guardar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_supplier.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
