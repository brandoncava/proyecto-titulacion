<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('series');

$seccion = 'series';
$migas = 'Series';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';

require '../../backend/config/Conexion.php';
// cuantos comprobantes emitio cada serie
$sentencia = $connect->prepare(
    'SELECT s.idserie, s.tipc, s.serie, s.ultimo, s.state, COUNT(o.idord) AS comprobantes
       FROM series s
       LEFT JOIN orders o ON o.idserie = s.idserie
      GROUP BY s.idserie, s.tipc, s.serie, s.ultimo, s.state
      ORDER BY s.idserie DESC'
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
                                <th><span class="las la-sort"></span>Comprobante</th>
                                <th><span class="las la-sort"></span>Serie</th>
                                <th><span class="las la-sort"></span>Último número</th>
                                <th><span class="las la-sort"></span>Emitidos</th>
                                <th><span class="las la-sort"></span>Estado</th>
                                <th><span class="las la-sort"></span>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($data as $d): ?>
                            <tr>
                                <td><?php echo e($d->idserie); ?></td>
                                <td><h4><?php echo e($d->tipc); ?></h4></td>
                                <td><h4><?php echo e($d->serie); ?></h4></td>
                                <td><?php echo e(formato_comprobante($d->serie, $d->ultimo)); ?></td>
                                <td><?php echo (int)$d->comprobantes; ?></td>
                                <td><?php echo (int)$d->state === 1 ? 'Activa' : 'Inactiva'; ?></td>
                                <td>
<?php if ((int)$d->state === 1): ?>
                                    <small>En uso</small>
<?php else: ?>
                                    <form onsubmit="return confirm('Esta serie pasará a usarse y se desactivará la actual. ¿Continuar?');" method="POST" action="">
                                        <input type="hidden" name="idserie" value="<?php echo e($d->idserie); ?>">
                                        <?php echo csrf_campo(); ?>
                                        <button name="cambiar_serie" title="Activar esta serie" class="btn-icono"><span class="las la-sync"></span></button>
                                    </form>
<?php endif; ?>
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
<?php include_once '../../backend/php/upd_serie.php'; ?>
</body>
</html>
