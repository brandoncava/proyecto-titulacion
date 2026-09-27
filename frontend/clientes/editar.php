<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('clientes');

$seccion = 'clientes';
$migas = 'Clientes / Actualizar';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            <?php 
require '../../backend/config/Conexion.php';
 $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT * FROM clientes  WHERE idcli = ?");
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
    <h1>Actualizar a los clientes</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>

    <label for="psw"><b>Tipo de documento</b></label><span class="badge-warning">*</span>
    <select required name="tipcl">
        <option value="<?php echo e($d->tipd); ?>"><?php echo e($d->tipd); ?></option>
        <option>------------Seleccione-----------------</option>
        <option value="dni">DNI</option>
    </select>

    <label for="email"><b>Número del documento</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->nudoc); ?>" maxlength="8" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 77656756" name="numcl"  required>
    <label for="email"><b>Nombre del cliente</b></label>
    <input type="text" name="namcl" value="<?php echo e($d->nocl); ?>"  placeholder="ejm: jjalver">

    <label for="email"><b>Apellido del cliente</b></label>
    <input type="text" name="apecl" value="<?php echo e($d->apcl); ?>" placeholder="ejm: zapata">

     <label for="email"><b>Teléfono celular del cliente</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->telfcl); ?>" maxlength="9" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 99898787" name="telcl"  required>

  
    <label for="email"><b>Nombre del usuario cliente</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->username); ?>" placeholder="ejm: jjalver" name="usrcl"  required>
    <input type="hidden" name="clid" value="<?php echo e($d->idcli); ?>">

    <hr>
   
    <button type="submit" name="upd_customer" class="registerbtn">Guardar</button>
  </div>
  
</form>
 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_customer.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
