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
             <div class="row">
                <div class="column">
                    <div class="c-card">
                      <a href="mostrar.php"><div class="header bg-light-blue">
                            <h2>
                               VENTAS DENTRO DEL SISTEMA <small></small>
                            </h2>
                        </div></a>
                    </div>
                </div>

              <div class="column">
                <div class="c-card">
                  <a href="https://ventapro.pe/c/ck-computers"><div class="header bg-light-blue">
                            <h2>
                               VENTAS ONLINE <small></small>
                            </h2>
                        </div></a>
                </div>
              </div>
  
</div>
            </div>


            <div class="page-content">
            
            <div class="records table-responsive">
                <br>
                    <h2>
                               VENTAS DENTRO DEL SISTEMA <small></small>
                            </h2>
                            <br>
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
                                    <td><h4><?php echo e($d->tipc); ?></h4></td>
                                    <td><h4><?php echo e($d->placed_on); ?></h4></td>
                                    <td><h4>S/<?php echo number_format($d->total_price, 2); ?></h4></td>
                                    <td>
                                        <div class="client">
                                           
                                            <div class="client-info">
                                                <h4><?php echo e($d->nomcl); ?></h4>
                                               
                                            </div>
                                        </div>
                                    </td>
                                    <td><h4><?php echo e($d->total_products); ?></h4></td>
        <td>
            
            <a title="Boleta" href="../ventas/boleta.php?id=<?php echo e($d->idord); ?>" class="fa fa-file-text-o tooltip"></a>
            <?php if ((int)$d->anulada === 1): ?>
                <span style="color:#B42318;font-weight:bold;margin-left:6px;">ANULADA</span>
            <?php elseif (puede('ventas_anular')): ?>
                <form method="POST" action="" style="display:inline" onsubmit="return anularVenta(this);">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idord" value="<?php echo e($d->idord); ?>">
                    <input type="hidden" name="motivo" value="">
                    <button type="submit" name="anular_venta" title="Anular venta" class="fa fa-ban" style="border:none;background:#B42318;color:#fff;cursor:pointer;padding:6px 9px;border-radius:4px;margin-left:6px;"></button>
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
</body>
</html>
