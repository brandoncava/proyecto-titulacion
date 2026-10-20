<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
            <div class="records table-responsive">
                     <div class="record-header">
                        <div class="add">
                          
                            <a href="nuevo.php" class="btn-primario btn-nuevo"><span class="las la-plus"></span> Nuevo</a>
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
                                <tr<?php if ((int)$d->anulada === 1) echo ' class="fila-anulada"'; ?>>
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
                           <p class="sin-datos">Todavía no hay registros.</p>
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
