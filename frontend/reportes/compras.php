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
    "SELECT SUM(o.anulada = 0)                                   AS compras,
            COALESCE(SUM(CASE WHEN o.anulada = 0 THEN o.total_price END), 0) AS total,
            COALESCE(SUM(CASE WHEN o.anulada = 0 THEN o.igv END), 0) AS igv,
            SUM(o.anulada = 1)                                   AS anuladas,
            COALESCE(SUM(CASE WHEN o.anulada = 1 THEN o.total_price END), 0) AS total_anulado
       FROM orders_purchase o
      WHERE $enRango"
);
$resumen->execute($rango);
$r = $resumen->fetch();
$promedio = $r->compras ? $r->total / $r->compras : 0;

$porPago = $connect->prepare(
    "SELECT COALESCE(NULLIF(o.method, ''), 'Sin indicar') AS metodo, COUNT(*) AS compras, SUM(o.total_price) AS total
       FROM orders_purchase o
      WHERE $enRango AND o.anulada = 0
      GROUP BY metodo
      ORDER BY total DESC"
);
$porPago->execute($rango);
$pagos = $porPago->fetchAll();

$filtroEstado = ['validas' => ' AND o.anulada = 0', 'anuladas' => ' AND o.anulada = 1', 'todas' => ''][$estado];
$lista = $connect->prepare(
    "SELECT o.idordpur, o.placed_on, o.tipc, o.serie_doc, o.numero_doc, p.nomprv, o.method, o.igv, o.total_price, o.anulada, o.motivo_anulacion,
            u.nombre AS comprador
       FROM orders_purchase o
       INNER JOIN proveedores p ON p.idprov = o.idprov
       LEFT JOIN usuarios u ON u.id = o.user_id
      WHERE $enRango $filtroEstado
      ORDER BY o.placed_on DESC, o.idordpur DESC"
);
$lista->execute($rango);
$compras = $lista->fetchAll();

$atajos = [
    'Hoy'       => [$hoy, $hoy],
    'Este mes'  => [date('Y-m-01'), $hoy],
    'Este año'  => [date('Y-01-01'), $hoy],
];

$seccion = 'reportes';
$migas = 'Reportes / Compras';
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
                        <a href="ventas.php">Reporte de ventas</a>
<?php foreach ($atajos as $texto => [$d, $h]): ?>
                        <a href="?desde=<?php echo $d; ?>&amp;hasta=<?php echo $h; ?>&amp;estado=<?php echo $estado; ?>"><?php echo $texto; ?></a>
<?php endforeach; ?>
                    </span>
                </form>

                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo (int)$r->compras; ?></h2>
                            <span class="las la-receipt"></span>
                        </div>
                        <div class="card-progress"><small>Compras válidas</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($r->total, 2); ?></h2>
                            <span class="las la-money-bill"></span>
                        </div>
                        <div class="card-progress"><small>Total comprado (con IGV)</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($r->igv, 2); ?></h2>
                            <span class="las la-percent"></span>
                        </div>
                        <div class="card-progress"><small>IGV de las compras (crédito fiscal)</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($promedio, 2); ?></h2>
                            <span class="las la-balance-scale"></span>
                        </div>
                        <div class="card-progress"><small>Compra promedio</small></div>
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
                            <tr><th>Forma de pago</th><th>Compras</th><th>Total</th></tr>
                        </thead>
                        <tbody>
<?php foreach ($pagos as $p): ?>
                            <tr>
                                <td><?php echo e($p->metodo); ?></td>
                                <td><?php echo (int)$p->compras; ?></td>
                                <td>S/<?php echo number_format($p->total, 2); ?></td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
<?php endif; ?>

                <div class="records table-responsive">
                    <div class="record-header">
                        <h3>Compras del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></h3>
                    </div>
                    <div>
<?php if ($compras): ?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><span class="las la-sort"></span>Fecha</th>
                                    <th><span class="las la-sort"></span>Comprobante</th>
                                    <th><span class="las la-sort"></span>Proveedor</th>
                                    <th><span class="las la-sort"></span>Comprador</th>
                                    <th><span class="las la-sort"></span>Pago</th>
                                    <th><span class="las la-sort"></span>IGV</th>
                                    <th><span class="las la-sort"></span>Total</th>
                                    <th><span class="las la-sort"></span>Estado</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($compras as $v): ?>
                                <tr>
                                    <td><?php echo e($v->idordpur); ?></td>
                                    <td data-order="<?php echo e($v->placed_on); ?>"><?php echo date('d/m/Y H:i', strtotime($v->placed_on)); ?></td>
                                    <td><?php echo e($v->tipc); ?><?php echo !empty($v->serie_doc) ? ' ' . e($v->serie_doc . '-' . $v->numero_doc) : ''; ?></td>
                                    <td><?php echo e($v->nomprv); ?></td>
                                    <td><?php echo e($v->comprador ?? '—'); ?></td>
                                    <td><?php echo e($v->method); ?></td>
                                    <td>S/<?php echo number_format($v->igv, 2); ?></td>
                                    <td data-order="<?php echo e($v->total_price); ?>">S/<?php echo number_format($v->total_price, 2); ?></td>
<?php if ((int)$v->anulada === 1): ?>
                                    <td><span class="estado-anulada" title="<?php echo e($v->motivo_anulacion); ?>">ANULADA</span></td>
<?php else: ?>
                                    <td>Válida</td>
<?php endif; ?>
                                    <td><a title="Comprobante" href="../compra/comprobante.php?id=<?php echo e($v->idordpur); ?>" class="fa fa-file-text-o tooltip"></a></td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php else: ?>
                        <p class="sin-datos">No hay compras en este rango.</p>
<?php endif; ?>
                    </div>
                </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
