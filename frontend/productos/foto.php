<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

$seccion = 'productos';
$migas = 'Productos / Foto';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            <?php 
require '../../backend/config/Conexion.php';
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT * FROM productos  WHERE idprod = ?");
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
    <h1>Actualizar imagen del producto</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>


    <label for="email"><b>Foto del producto</b></label><span class="badge-warning">*</span>
    <div class="upload-box">
        <div class="upload-img">
            <img src="../../backend/img/subidas/<?php echo e($d->foto); ?>" alt="">
        </div>
            <label for="upload-input" class="upload-label">Upload Image</label>
    <input type="file" name="foto" required  id="upload-input">
    <input type="hidden" value="<?php echo e($d->idprod); ?>" name="prdid">
                   
    </div>


    <hr>
   
    <button type="submit" name="upd_foto_prodct" class="registerbtn">Guardar</button>
  </div>
  
</form>

 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_foto_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
<script type="text/javascript">
  const uploadInput = document.querySelector('#upload-input') ;
const previewImg = document.querySelector('.upload-img img') ;

uploadInput.addEventListener('change',e => {
    if(e.target.files.length > 0) {
        const url = URL.createObjectURL(e.target.files[0]) ;
        previewImg.src = url ;
    }
})
</script>
 

</body>
</html>
