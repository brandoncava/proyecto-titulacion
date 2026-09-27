<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('catalogos');

$seccion = 'marcas';
$migas = 'Marcas';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';

require '../../backend/config/Conexion.php';
// cuantos productos activos usa cada marca
$sentencia = $connect->prepare(
    'SELECT m.idmar, m.nomarc, COUNT(p.idprod) AS productos
       FROM marca m
       LEFT JOIN productos p ON p.idmar = m.idmar AND p.state = 1
      WHERE m.state = 1
      GROUP BY m.idmar, m.nomarc
      ORDER BY m.idmar DESC'
);
$sentencia->execute();
$data = $sentencia->fetchAll();
?>

            <div class="page-content">

            <div class="records table-responsive">
                <div class="record-header">
                    <div class="add">
                        <button style="cursor: pointer;" onclick="location.href='nuevo.php'">Nuevo</button>
                    </div>
                </div>
                <div>
<?php if (count($data) > 0): ?>
                    <table width="100%" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><span class="las la-sort"></span>Nombre</th>
                                <th><span class="las la-sort"></span>Productos</th>
                                <th><span class="las la-sort"></span>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($data as $d): ?>
                            <tr>
                                <td><?php echo e($d->idmar); ?></td>
                                <td>
                                    <div class="client">
                                        <div class="client-info">
                                            <h4><?php echo e($d->nomarc); ?></h4>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo (int)$d->productos; ?></td>
                                <td>
                                    <a title="Actualizar" href="editar.php?id=<?php echo e($d->idmar); ?>" class="fa fa-pencil tooltip"></a>

                                    <form onsubmit="return confirm('¿Realmente desea dar de baja esta marca?');" method="POST" action="">
                                        <input type="hidden" name="idmar" value="<?php echo e($d->idmar); ?>">
                                        <?php echo csrf_campo(); ?>
                                        <button name="delete_marca" style="cursor: pointer;" class="fa fa-trash"></button>
                                    </form>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php else: ?>
                    <div class="alert">
                        <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
                        <strong>Danger!</strong> No hay datos.
                    </div>
<?php endif; ?>
                </div>

            </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/delete_marca.php' ?>
</body>
</html>
