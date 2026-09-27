<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('clientes');

$seccion = 'clientes';
$migas = 'Clientes / Crear acceso';
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
    <h1>Crear acceso del cliente</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>

    <label for="email"><b>Nombre cliente</b></label>
    <input type="text" value="<?php echo e($d->nocl); ?> &nbsp; <?php echo e($d->apcl); ?>" placeholder="ejm: jjalver" disabled>
  
    <label for="email"><b>Nombre del usuario cliente</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: jjalver" name="usrcl" maxlength="15" required>
    <input type="hidden" name="clid" value="<?php echo e($d->idcli); ?>">

    <label for="email"><b>Contraseña del cliente</b></label><span class="badge-warning">*</span>
    <input type="password" placeholder="ejm: ********" name="pswcl" minlength="6" required>

    <label for="psw"><b>Rol</b></label>
    <select name="rolcl" required>
        <option value="2">CLIENTES</option>
        
      
    </select>

    <hr>
   
    <button type="submit" name="add_perfil" class="registerbtn">Guardar</button>
  </div>
  
</form>
 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_perfil.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
