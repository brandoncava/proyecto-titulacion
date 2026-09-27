<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

$seccion = 'productos';
$migas = 'Productos / Actualizar';
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
    <h1>Actualizar producto</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
    <label for="email"><b>Código del producto</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->codpro); ?>" maxlength="14" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 22478754784578" name="prdcod"  required>

    <input type="hidden" value="<?php echo e($d->idprod); ?>" name="prdid">

    <label for="email"><b>Nombre del producto</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->nomprd); ?>" placeholder="ejm: Laptop Lenovo" name="prdnom"  required>

    <label for="email"><b>Descripción del producto</b></label><span class="badge-warning">*</span>
    <textarea required value="<?php echo e($d->desprd); ?>" name="prddes" id="consl" required placeholder="Write something.." style="height:200px"><?php echo e($d->desprd); ?></textarea>

    <label for="email"><b>Precio del producto</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->precio); ?>" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"  placeholder="ejm: 299" name="prdprec"  required>
    
    <label for="psw"><b>Marca del producto</b></label><span class="badge-warning">*</span>
    <select required name="prdmarc" id="marc">
        <option value="<?php echo e($d->idmar); ?>"><?php echo e($d->nomarc); ?></option>
        <option>-------------------Seleccione------------------</option>

        <?php 
    $stmt = $connect->prepare('SELECT * FROM marca WHERE state = 1');
    $stmt->execute();

while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            ?>
            <option value="<?php echo e($idmar); ?>"><?php echo e($nomarc); ?></option>
            <?php
        }
        ?>
     ?>
        
    </select>

    <label for="psw"><b>Categoria del producto</b></label><span class="badge-warning">*</span>
    <select required name="prdcate" id="cat">
        <option value="<?php echo e($d->idcate); ?>"><?php echo e($d->nocate); ?></option>
        <option>-----------------------------Seleccione------------------------------------</option>

        <?php 
    $stmt = $connect->prepare('SELECT * FROM categoria WHERE state = 1');
    $stmt->execute();

while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            ?>
            <option value="<?php echo e($idcate); ?>"><?php echo e($nocate); ?></option>
            <?php
        }
        ?>
     ?>
    </select>

    <label for="email"><b>Modelo del producto</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->modelo); ?>" placeholder="ejm: Lenovo" name="prdmod"  required>

     <label for="email"><b>Peso del producto</b></label><span class="badge-warning">*</span>
    <input type="text" value="<?php echo e($d->peso); ?>" placeholder="ejm: 20kg" name="prdpes"  required>
                   
    </div>


    <hr>
   
    <button type="submit" name="upd_prodct" class="registerbtn">Guardar</button>
  </div>
  
</form>

 <?php endforeach; ?>
  
    <?php else:?>
      <p class="alert alert-warning">No hay datos</p>
    <?php endif; ?>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>

 

</body>
</html>
