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
                        <a href="nuevo.php" class="btn-primario btn-nuevo"><span class="las la-plus"></span> Nuevo</a>
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
                                    <a title="Actualizar" href="editar.php?id=<?php echo e($d->idmar); ?>" class="btn-icono"><span class="las la-pen"></span></a>

                                    <form onsubmit="return confirm('¿Realmente desea dar de baja esta marca?');" method="POST" action="">
                                        <input type="hidden" name="idmar" value="<?php echo e($d->idmar); ?>">
                                        <?php echo csrf_campo(); ?>
                                        <button name="delete_marca" class="btn-icono peligro"><span class="las la-trash-alt"></span></button>
                                    </form>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php else: ?>
                    <p class="sin-datos">Todavía no hay registros.</p>
<?php endif; ?>
                </div>

            </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/delete_marca.php' ?>
</body>
</html>
