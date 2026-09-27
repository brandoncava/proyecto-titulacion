<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('reportes');

require '../../backend/config/Conexion.php';

// fecha Y-m-d valida o el valor por defecto
function fecha_get($campo, $defecto)
{
    $v = $_GET[$campo] ?? '';
    $f = DateTime::createFromFormat('Y-m-d', $v);
    return ($f && $f->format('Y-m-d') === $v) ? $v : $defecto;
}

$hoy   = date('Y-m-d');
$desde = fecha_get('desde', date('Y-01-01'));
$hasta = fecha_get('hasta', $hoy);
if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

$estados = ['validas' => 'Solo válidas', 'anuladas' => 'Solo anuladas', 'todas' => 'Todas'];
$estado  = array_key_exists($_GET['estado'] ?? '', $estados) ? $_GET['estado'] : 'validas';

// el rango incluye el dia "hasta" completo
$rango = ['desde' => $desde . ' 00:00:00', 'hasta' => date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00'];
$enRango = 'o.placed_on >= :desde AND o.placed_on < :hasta';

$resumen = $connect->prepare(
    "SELECT SUM(o.anulada = 0)                                   AS ventas,
            COALESCE(SUM(CASE WHEN o.anulada = 0 THEN o.total_price END), 0) AS total,
            SUM(o.anulada = 1)                                   AS anuladas,
            COALESCE(SUM(CASE WHEN o.anulada = 1 THEN o.total_price END), 0) AS total_anulado
       FROM orders o
      WHERE $enRango"
);
$resumen->execute($rango);
$r = $resumen->fetch();
$promedio = $r->ventas ? $r->total / $r->ventas : 0;

$porPago = $connect->prepare(
    "SELECT COALESCE(NULLIF(o.method, ''), 'Sin indicar') AS metodo, COUNT(*) AS ventas, SUM(o.total_price) AS total
       FROM orders o
      WHERE $enRango AND o.anulada = 0
      GROUP BY metodo
      ORDER BY total DESC"
);
$porPago->execute($rango);
$pagos = $porPago->fetchAll();

$filtroEstado = ['validas' => ' AND o.anulada = 0', 'anuladas' => ' AND o.anulada = 1', 'todas' => ''][$estado];
$lista = $connect->prepare(
    "SELECT o.idord, o.placed_on, o.tipc, o.nomcl, o.method, o.total_price, o.anulada, o.motivo_anulacion,
            u.nombre AS vendedor
       FROM orders o
       LEFT JOIN usuarios u ON u.id = o.user_id
      WHERE $enRango $filtroEstado
      ORDER BY o.placed_on DESC, o.idord DESC"
);
$lista->execute($rango);
$ventas = $lista->fetchAll();

$atajos = [
    'Hoy'       => [$hoy, $hoy],
    'Este mes'  => [date('Y-m-01'), $hoy],
    'Este año'  => [date('Y-01-01'), $hoy],
];

$seccion = 'reportes';
$migas = 'Reportes / Ventas';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <form class="filtros" method="GET" action="">
                    <label>Desde <input type="date" name="desde" value="<?php echo e($desde); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Hasta <input type="date" name="hasta" value="<?php echo e($hasta); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Estado
                        <select name="estado">
<?php foreach ($estados as $valor => $texto): ?>
                            <option value="<?php echo $valor; ?>"<?php echo $valor === $estado ? ' selected' : ''; ?>><?php echo $texto; ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit">Filtrar</button>
                    <span class="atajos">
<?php foreach ($atajos as $texto => [$d, $h]): ?>
                        <a href="?desde=<?php echo $d; ?>&amp;hasta=<?php echo $h; ?>&amp;estado=<?php echo $estado; ?>"><?php echo $texto; ?></a>
<?php endforeach; ?>
                    </span>
                </form>

                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo (int)$r->ventas; ?></h2>
                            <span class="las la-receipt"></span>
                        </div>
                        <div class="card-progress"><small>Ventas válidas</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($r->total, 2); ?></h2>
                            <span class="las la-money-bill"></span>
                        </div>
                        <div class="card-progress"><small>Total vendido</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($promedio, 2); ?></h2>
                            <span class="las la-balance-scale"></span>
                        </div>
                        <div class="card-progress"><small>Ticket promedio</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo (int)$r->anuladas; ?></h2>
                            <span class="las la-ban"></span>
                        </div>
                        <div class="card-progress"><small>Anuladas (S/<?php echo number_format($r->total_anulado, 2); ?>)</small></div>
                    </div>
                </div>

<?php if ($pagos): ?>
                <div class="records por-pago">
                    <h3>Por forma de pago</h3>
                    <table width="100%">
                        <thead>
                            <tr><th>Forma de pago</th><th>Ventas</th><th>Total</th></tr>
                        </thead>
                        <tbody>
<?php foreach ($pagos as $p): ?>
                            <tr>
                                <td><?php echo e($p->metodo); ?></td>
                                <td><?php echo (int)$p->ventas; ?></td>
                                <td>S/<?php echo number_format($p->total, 2); ?></td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
<?php endif; ?>

                <div class="records table-responsive">
                    <div class="record-header">
                        <h3>Ventas del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></h3>
                    </div>
                    <div>
<?php if ($ventas): ?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><span class="las la-sort"></span>Fecha</th>
                                    <th><span class="las la-sort"></span>Comprobante</th>
                                    <th><span class="las la-sort"></span>Cliente</th>
                                    <th><span class="las la-sort"></span>Vendedor</th>
                                    <th><span class="las la-sort"></span>Pago</th>
                                    <th><span class="las la-sort"></span>Total</th>
                                    <th><span class="las la-sort"></span>Estado</th>
                                    <th>Boleta</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($ventas as $v): ?>
                                <tr>
                                    <td><?php echo e($v->idord); ?></td>
                                    <td data-order="<?php echo e($v->placed_on); ?>"><?php echo date('d/m/Y H:i', strtotime($v->placed_on)); ?></td>
                                    <td><?php echo e($v->tipc); ?></td>
                                    <td><?php echo e($v->nomcl); ?></td>
                                    <td><?php echo e($v->vendedor ?? '—'); ?></td>
                                    <td><?php echo e($v->method); ?></td>
                                    <td data-order="<?php echo e($v->total_price); ?>">S/<?php echo number_format($v->total_price, 2); ?></td>
<?php if ((int)$v->anulada === 1): ?>
                                    <td><span class="estado-anulada" title="<?php echo e($v->motivo_anulacion); ?>">ANULADA</span></td>
<?php else: ?>
                                    <td>Válida</td>
<?php endif; ?>
                                    <td><a title="Boleta" href="../ventas/boleta.php?id=<?php echo e($v->idord); ?>" class="fa fa-file-text-o tooltip"></a></td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php else: ?>
                        <p class="sin-datos">No hay ventas en este rango.</p>
<?php endif; ?>
                    </div>
                </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
