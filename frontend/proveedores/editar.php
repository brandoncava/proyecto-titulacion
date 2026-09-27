<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('proveedores');

$seccion = 'proveedores';
$migas = 'Proveedores / Actualizar';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            <?php 
require '../../backend/config/Conexion.php';
 $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT * FROM proveedores  WHERE idprov = ?");
 $sentencia->execute([$id]);

$data =  array();
if($sentencia){
  while($r = $sentencia->fetchObject()){
    $data[] = $r;
  }
}
   ?>
   <?php if(count($data)>0):?>
        <?php foreach($data as $d):?> 
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Actualizar proveedores</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  

    <label for="email"><b>Ruc del proveedor</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->rucprv); ?>" maxlength="11" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 77656756" name="rcprv"  required>
    <input type="hidden" name="suppid" value="<?php echo e($d->idprov); ?>">

    <label for="email"><b>Nombre del proveedor</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->nomprv); ?>" placeholder="ejm: Wong" name="noprv"  required>

    <label for="email"><b>Correo del proveedor</b></label>
    <input type="text" value="<?php echo e($d->corrprv); ?>"  name="corprv" >

    <hr>
   
    <button type="submit" name="upd_supplier" class="registerbtn">Guardar</button>
  </div>
  
</form>
<?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?> 
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_supplier.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
