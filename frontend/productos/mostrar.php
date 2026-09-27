<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos');

$seccion = 'productos';
$migas = 'Productos';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
            <div class="records table-responsive">
                     <div class="record-header">
                        <div class="add">
                          
                            <button style="cursor: pointer;" onclick="location.href='nuevo.php'">Nuevo</button>
                        </div>
                    </div>
                    <div>
                        <?php 
require '../../backend/config/Conexion.php';
$sentencia = $connect->prepare("SELECT productos.idprod,productos.codpro ,productos.nomprd, productos.desprd, productos.foto, productos.precio, productos.stock, marca.idmar, marca.nomarc, categoria.idcate, categoria.nocate,productos.modelo, productos.peso, productos.state, productos.fere FROM productos INNER JOIN marca ON productos.idmar = marca.idmar INNER JOIN categoria ON productos.idcate = categoria.idcate WHERE productos.state = 1 ORDER BY idprod DESC;");
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
                                    <th>Código</th>
                                    <th><span class="las la-sort"></span>Producto</th>
                                    <th><span class="las la-sort"></span>Marca</th>
                                    <th><span class="las la-sort"></span>Modelo</th>
                                    <th><span class="las la-sort"></span>Categoria</th>
                                    <th><span class="las la-sort"></span>Precio</th>
                                    <th><span class="las la-sort"></span>Stock</th>
                                    <th><span class="las la-sort"></span>Acciones</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php echo e($d->codpro); ?></td>
                                    <td>
                                        <div class="client">
                <div class="client-img bg-img" style="background-image: url(../../backend/img/subidas/<?php echo e($d->foto); ?>)"></div>
                                            <div class="client-info">
                                                <h4><?php echo e($d->nomprd); ?></h4>
                                               
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h4><?php echo e($d->nomarc); ?></h4>
                                    </td>
                                    <td><h4><?php echo e($d->modelo); ?></h4></td>
                                    <td><h4><?php echo e($d->nocate); ?></h4></td>
                                    <td><h4>S/<?php echo number_format($d->precio,2) ?></h4></td>
                                    <td>
                                      <?php 
                                     if ($d->stock < 11) {
                                        echo '<span class="badge">Se esta agotando</span>';
                                     }else{
                                       echo '<h4>'.$d->stock.'</h4>';
                                       
                                     }

                                     ?>  
                                    </td>
                                    

                                    <td>
                                       <a title="Actualizar" href="../productos/editar.php?id=<?php echo e($d->idprod); ?>" class="fa fa-pencil tooltip"></a>

                                       <a title="Stock" href="../productos/stock.php?id=<?php echo e($d->idprod); ?>" class="fa fa-bookmark-o tooltip"></a>

                                       <a title="Imagen" href="../productos/foto.php?id=<?php echo e($d->idprod); ?>" class="fa fa-picture-o tooltip"></a>
                                     
                                     <form  onsubmit="return confirm('Realmente desea eliminar el registro?');" method='POST' action='<?php $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' name='idprod' value="<?php echo e($d->idprod); ?>">

<?php echo csrf_campo(); ?>
<button name='delete_product' style="cursor: pointer;" class="fa fa-trash"></button>
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
    <?php include_once '../../backend/php/delete_product.php' ?>
</body>
</html>
