<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('compras');

$seccion = 'compras';
$migas = 'Compras';
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
$sentencia = $connect->prepare("SELECT orders_purchase.idordpur, orders_purchase.user_id, proveedores.idprov, proveedores.rucprv, proveedores.nomprv, orders_purchase.total_products, orders_purchase.total_price, orders_purchase.placed_on, orders_purchase.payment_status, orders_purchase.tipc, orders_purchase.serie_doc, orders_purchase.numero_doc, orders_purchase.anulada FROM orders_purchase INNER JOIN proveedores ON orders_purchase.idprov = proveedores.idprov ORDER BY idordpur DESC;");
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
                                    <th>#</th>
                                    <th><span class="las la-sort"></span>Comprobante</th>
                                    <th><span class="las la-sort"></span>Fecha</th>
                                    <th><span class="las la-sort"></span>Total</th>
                                    <th><span class="las la-sort"></span>Proveedores</th>
                                    <th><span class="las la-sort"></span>Productos</th>

                                    <th><span class="las la-sort"></span>Acciones</th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php echo e($d->idordpur); ?></td>
                                    <td><h4><?php echo e($d->tipc); ?></h4>
                                        <small><?php echo !empty($d->serie_doc) ? e($d->serie_doc . '-' . $d->numero_doc) : ''; ?></small></td>
                                    <td><h4><?php echo e($d->placed_on); ?></h4></td>
                                    <td><h4>S/<?php echo number_format($d->total_price, 2); ?></h4></td>
                                    <td>
                                        <div class="client">
                                           
                                            <div class="client-info">
                                                <h4><?php echo e($d->nomprv); ?></h4>
                                               
                                            </div>
                                        </div>
                                    </td>
                                    <td><h4><?php echo e($d->total_products); ?></h4></td>
        <td>
            
            <a title="Comprobante" href="comprobante.php?id=<?php echo e($d->idordpur); ?>" class="btn-icono"><span class="las la-file-alt"></span></a>
            <?php if ((int)$d->anulada === 1): ?>
                <span style="color:#B42318;font-weight:bold;margin-left:6px;">ANULADA</span>
            <?php elseif (puede('compras_anular')): ?>
                <form method="POST" action="" style="display:inline" onsubmit="return confirm('¿Modificar esta compra? Sus productos se cargarán en el carrito de compras para editarlos.');">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idordpur" value="<?php echo e($d->idordpur); ?>">
                    <button type="submit" name="modificar_compra" title="Modificar compra" class="btn-icono"><span class="las la-pen"></span></button>
                </form>
                <form method="POST" action="" style="display:inline" onsubmit="return anularCompra(this);">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idordpur" value="<?php echo e($d->idordpur); ?>">
                    <input type="hidden" name="motivo" value="">
                    <button type="submit" name="anular_compra" title="Anular compra" class="btn-icono peligro"><span class="las la-ban"></span></button>
                </form>
            <?php endif; ?>
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
<script type="text/javascript">
function anularCompra(form){
    var motivo = prompt('Motivo de la anulación de esta compra:');
    if (motivo === null) return false;            // canceló
    motivo = motivo.trim();
    if (motivo === '') { alert('Debes indicar un motivo.'); return false; }
    form.motivo.value = motivo;
    return confirm('¿Anular esta compra? Se descontará el stock del inventario y no se puede deshacer.');
}
</script>
<?php include_once '../../backend/php/anular_compra.php'; ?>
<?php include_once '../../backend/php/modificar_compra.php'; ?>
</body>
</html>
