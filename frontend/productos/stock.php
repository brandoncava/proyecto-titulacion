<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

$seccion = 'productos';
$migas = 'Productos / Stock';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            <?php 
require '../../backend/config/Conexion.php';
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?: 0;
 $sentencia = $connect->prepare("SELECT productos.idprod,productos.codpro ,productos.nomprd, productos.desprd, productos.foto, productos.precio, productos.stock, marca.idmar, marca.nomarc, categoria.idcate, categoria.nocate,productos.modelo, productos.peso, productos.state, productos.fere FROM productos INNER JOIN marca ON productos.idmar = marca.idmar INNER JOIN categoria ON productos.idcate = categoria.idcate WHERE idprod = ?");
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
    <h1>Actualizar stock del producto</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
    

    <input type="hidden" value="<?php echo e($d->idprod); ?>" name="prdid">

    <label for="email"><b>Nombre del producto</b></label><span class="badge-warning">*</span>
    <input disabled type="text" value="<?php echo e($d->nomprd); ?>" placeholder="ejm: Laptop Lenovo"   required>

    <label for="email"><b>Stock del producto</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="3" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" value="<?php echo e($d->stock); ?>" placeholder="ejm: Lenovo" name="prdstock"  required>

  
    </div>


    <hr>
   
    <button type="submit" name="upd_prodct_stock" class="registerbtn">Guardar</button>
  </div>
  
</form>

 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_prodct_stock.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>

 

</body>
</html>
