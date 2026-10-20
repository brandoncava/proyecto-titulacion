<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas dentro del sistema';
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
$sentencia = $connect->prepare("SELECT * FROM orders ORDER BY idord DESC;");
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
                                    <th><span class="las la-sort"></span>Cliente</th>
                                    <th><span class="las la-sort"></span>Productos</th>

                                    <th><span class="las la-sort"></span>Acciones</th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr<?php if ((int)$d->anulada === 1) echo ' style="opacity:.5"'; ?>>
                                    <td><?php echo e($d->idord); ?></td>
                                    <td><h4><?php echo e($d->tipc); ?></h4>
                                        <small><?php echo !empty($d->serie) ? e(formato_comprobante($d->serie, $d->numero)) : ''; ?></small></td>
                                    <td><h4><?php echo e($d->placed_on); ?></h4></td>
                                    <td><h4>S/<?php echo number_format($d->total_price, 2); ?></h4></td>
                                    <td>
                                        <div class="client">
                                           
                                            <div class="client-info">
                                                <h4><?php echo e($d->nomcl); ?></h4>
                                                <small><?php echo !empty($d->doc_num) ? e($d->doc_tipo . ' ' . $d->doc_num) : ''; ?></small>
                                               
                                            </div>
                                        </div>
                                    </td>
                                    <td><h4><?php echo e($d->total_products); ?></h4></td>
        <td>
            
            <?php include __DIR__ . '/acciones_venta.php'; ?>
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
function anularVenta(form){
    var motivo = prompt('Motivo de la anulación de esta venta:');
    if (motivo === null) return false;            // canceló
    motivo = motivo.trim();
    if (motivo === '') { alert('Debes indicar un motivo.'); return false; }
    form.motivo.value = motivo;
    return confirm('¿Anular esta venta? Se devolverá el stock al inventario y no se puede deshacer.');
}
</script>
<?php include_once '../../backend/php/anular_venta.php'; ?>
<?php include_once '../../backend/php/modificar_venta.php'; ?>
</body>
</html>
