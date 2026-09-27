<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('usuarios');

$seccion = 'accesos';
$migas = 'Accesos';
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
$sentencia = $connect->prepare("SELECT * FROM usuarios ORDER BY id DESC;");
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
                                    <th><span class="las la-sort"></span>Nombre</th>
                                    <th><span class="las la-sort"></span>Perfil</th>
                                    <th><span class="las la-sort"></span>Acciones</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php echo e($d->id); ?></td>
                                    <td>
                                        <div class="client">
                                           
                                            <div class="client-info">
                                                <h4><?php echo e($d->nombre); ?></h4>
                                                <small><?php echo e($d->username); ?></small>
                                               
                                            </div>
                                        </div>
                                    </td>
                                    <td data-title="Perfil"><?php echo e(nombre_rol($d->rol)); ?></td>
                                    <td>
                                       <a title="Actualizar" href="../accesos/editar.php?id=<?php echo e($d->id); ?>" class="fa fa-pencil tooltip"></a>

                                       <a title="Cambiar contraseña" href="../accesos/cambiar.php?id=<?php echo e($d->id); ?>" class="fa fa-key tooltip"></a>
                                     
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
</body>
</html>
