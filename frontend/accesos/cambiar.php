<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('usuarios');

$seccion = 'accesos';
$migas = 'Accesos / Contraseña';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            <?php 
require '../../backend/config/Conexion.php';
 $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT * FROM usuarios  WHERE id = ?");
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
    <h1>Cambiar contraseña del usuario</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>

    <label for="email"><b>Nombre del usuario</b></label>
    <input type="text" value="<?php echo e($d->nombre); ?>" placeholder="ejm: jjalver" disabled>
  
   
    <input type="hidden" name="useid" value="<?php echo e($d->id); ?>">

    <label for="email"><b>Nueva contraseña</b></label><span class="badge-warning">*</span>
    <input type="password" placeholder="ejm: ********" name="pswuse"  required>

    <hr>
   
    <button type="submit" name="upd_acceso_pwd" class="registerbtn">Guardar</button>
  </div>
  
</form>
 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_acceso_pwd.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
