<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('garantias');

require '../../backend/config/Conexion.php';

// por defecto solo las que siguen en proceso
$filtros = ['proceso' => 'En proceso', 'todas' => 'Todas'] + GARANTIA_ESTADOS;
$filtro  = array_key_exists($_GET['estado'] ?? '', $filtros) ? $_GET['estado'] : 'proceso';

$where = '';
$params = [];
if ($filtro === 'proceso') {
    $where = "WHERE g.estado <> 'Entregado'";
} elseif ($filtro !== 'todas') {
    $where = 'WHERE g.estado = ?';
    $params[] = $filtro;
}

$sentencia = $connect->prepare(
    "SELECT g.idgar, g.fecha_ingreso, g.cliente_nombre, g.cliente_doc, g.comprobante, g.vigente, g.vence,
            g.estado, g.resultado, p.nomprd, pr.nomprv
       FROM garantias g
       INNER JOIN productos p ON p.idprod = g.idprod
       LEFT JOIN proveedores pr ON pr.idprov = g.idprov
       $where
      ORDER BY g.idgar DESC"
);
$sentencia->execute($params);
$data = $sentencia->fetchAll();

$seccion = 'garantias';
$migas = 'Garantías';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <form class="filtros" method="GET" action="">
                    <label>Estado
                        <select name="estado" onchange="this.form.submit()">
<?php foreach ($filtros as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo $valor === $filtro ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                </form>

            <div class="records table-responsive">
                <div class="record-header">
                    <div class="add">
                        <button style="cursor: pointer;" onclick="location.href='nuevo.php'">Nueva orden de garantía</button>
                    </div>
                </div>
                <div>
<?php if ($data): ?>
                    <table width="100%" id="example">
                        <thead>
                            <tr>
                                <th>N° orden</th>
                                <th><span class="las la-sort"></span>Ingreso</th>
                                <th><span class="las la-sort"></span>Cliente</th>
                                <th><span class="las la-sort"></span>Producto</th>
                                <th><span class="las la-sort"></span>Compra</th>
                                <th><span class="las la-sort"></span>Garantía</th>
                                <th><span class="las la-sort"></span>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($data as $g): ?>
                            <tr>
                                <td data-order="<?php echo (int)$g->idgar; ?>"><b><?php echo e(formato_garantia($g->idgar)); ?></b></td>
                                <td data-order="<?php echo e($g->fecha_ingreso); ?>"><?php echo date('d/m/Y', strtotime($g->fecha_ingreso)); ?></td>
                                <td>
                                    <h4><?php echo e($g->cliente_nombre); ?></h4>
                                    <small><?php echo e($g->cliente_doc); ?></small>
                                </td>
                                <td><?php echo e($g->nomprd); ?></td>
                                <td><?php echo e($g->comprobante ?: '—'); ?></td>
                                <td>
<?php if ((int)$g->vigente === 1): ?>
                                    <span class="estado-gar vigente">Vigente</span>
<?php else: ?>
                                    <span class="estado-gar vencida">Vencida</span>
<?php endif; ?>
                                    <small>hasta <?php echo date('d/m/Y', strtotime($g->vence)); ?></small>
                                </td>
                                <td>
                                    <span class="estado-gar e-<?php echo e(strtolower(str_replace(' ', '-', $g->estado))); ?>"><?php echo e(GARANTIA_ESTADOS[$g->estado] ?? $g->estado); ?></span>
<?php if ($g->resultado): ?>
                                    <small><?php echo e($g->resultado); ?></small>
<?php endif; ?>
<?php if ($g->nomprv): ?>
                                    <small><?php echo e($g->nomprv); ?></small>
<?php endif; ?>
                                </td>
                                <td>
                                    <a title="Ver y actualizar" href="ver.php?id=<?php echo (int)$g->idgar; ?>" class="fa fa-eye tooltip"></a>
                                    <a title="Imprimir orden" href="orden.php?id=<?php echo (int)$g->idgar; ?>" class="fa fa-file-text-o tooltip"></a>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php else: ?>
                    <p class="sin-datos">No hay órdenes de garantía <?php echo $filtro === 'todas' ? '' : 'con este filtro'; ?>.</p>
<?php endif; ?>
                </div>
            </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
