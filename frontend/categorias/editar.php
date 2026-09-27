<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$seccion = 'categorias';
$migas = 'Categorias / Actualizar';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
             <?php 
require '../../backend/config/Conexion.php';
 $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT * FROM categoria  WHERE idcate = ?");
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
    <h1>Actualizar categorias</h1>
    
    <hr>
    <br>
  
    <label for="email"><b>Nombre de la categoria</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->nocate); ?>" placeholder="ejm: Laptos" name="catnom"  required>
    <input type="hidden" name="cateid" value="<?php echo e($d->idcate); ?>">
    <hr>
   
    <button type="submit" name="upd_category" class="registerbtn">Guardar</button>
  </div>
  
</form>
<?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?> 
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_category.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
