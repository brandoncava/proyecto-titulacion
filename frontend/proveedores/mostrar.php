<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('proveedores');

$seccion = 'proveedores';
$migas = 'Proveedores';
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
$sentencia = $connect->prepare("SELECT * FROM proveedores WHERE state = 1 ORDER BY idprov DESC;");
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
                                    <th><span class="las la-sort"></span>Proveedores</th>
                                    <th><span class="las la-sort"></span>Acciones</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php echo e($d->idprov); ?></td>
                                    <td>
                                        <div class="client">
                                           
                                            <div class="client-info">
                                                <h4><?php echo e($d->nomprv); ?></h4>
                                                <small><?php echo e($d->rucprv); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                       <a title="Actualizar" href="../proveedores/editar.php?id=<?php echo e($d->idprov); ?>" class="fa fa-pencil tooltip"></a>
                                
                                     <form  onsubmit="return confirm('Realmente desea eliminar el registro?');" method='POST' action='<?php $_SERVER['PHP_SELF'] ?>'>
<input type='hidden' name='idprov' value="<?php echo e($d->idprov); ?>">

<?php echo csrf_campo(); ?>
<button name='delete_supplier' style="cursor: pointer;" class="fa fa-trash"></button>
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
    <?php include_once '../../backend/php/delete_supplier.php' ?>
</body>
</html>
