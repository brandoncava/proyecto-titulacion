<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas / Nueva';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
            <div class="records table-responsive">
                     
                    <div>
                        <?php 
require '../../backend/config/Conexion.php';
$sentencia = $connect->prepare("SELECT productos.idprod,productos.codpro ,productos.nomprd, productos.desprd, productos.foto, productos.precio, productos.stock, marca.idmar, marca.nomarc, categoria.idcate, categoria.nocate,productos.modelo, productos.peso, productos.state, productos.fere FROM productos INNER JOIN marca ON productos.idmar = marca.idmar INNER JOIN categoria ON productos.idcate = categoria.idcate ORDER BY productos.idprod DESC;");
 $sentencia->execute();
$data =  array();
if($sentencia){
  while($r = $sentencia->fetchObject()){
    $data[] = $r;
  }
}
     ?>
     <?php if(count($data)>0):?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                  
                                    <th><span class="las la-sort"></span>Foto</th>
                                    <th><span class="las la-sort"></span>Código</th>
                                    <th><span class="las la-sort"></span>Producto</th>
                                    <th><span class="las la-sort"></span>Precio</th>
                                    <th><span class="las la-sort"></span></th>
                                 
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php  echo "<img src='../../backend/img/subidas/".$d->foto."'width='50'"; ?></td>
                                    <td><h4><?php echo e($d->codpro); ?></h4></td>
                                    <td><h4><?php echo e($d->nomprd); ?></h4></td>
                                   <td><h4>S/<?php echo number_format($d->precio,2) ?></h4></td>

                                   <td style="width:260px;">
                                     <form class="form-inline" method="post" action="">
<?php echo csrf_campo(); ?>
    <input type="hidden" name="prdt" value="<?php echo e($d->idprod); ?>">
   
      <div class="form-group">
        <input type="number" name="p_qty" value="1" style="width:100px;" min="1" class="form-control" placeholder="Cantidad">
      </div>
      <button type="submit" name="add_to_cart" class="registerbtn">ADD </button>
    </form>   
                                   </td>
                                   
                                </tr>
                                 <?php endforeach; ?>
                                
                            </tbody>
                        </table>
                          <?php else:?>
                           <div class="alert">
      <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
      <strong>Danger!</strong> No hay datos.
    </div>
    <?php endif; ?>
                    </div>

                </div>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
<?php include_once '../../backend/php/add_cart.php' ?>
   
</body>
</html>
