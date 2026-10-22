<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('reportes');

require __DIR__ . '/comun.php';

$buscar  = mb_substr(trim($_GET['buscar'] ?? ''), 0, 60);
$metodos = ['' => 'Todos'] + array_combine(METODOS_PAGO, METODOS_PAGO);
$metodo  = opcion_get('metodo', $metodos, '');

// ventas validas del periodo; el medio de pago filtra ventas que lo usaron
$where  = 'o.anulada = 0 AND o.placed_on >= :desde AND o.placed_on < :hasta';
$params = $rango;
if ($buscar !== '') {
    $where .= ' AND (o.nomcl LIKE :nombre OR o.doc_num LIKE :doc)';
    $params['nombre'] = '%' . $buscar . '%';
    $params['doc']    = $buscar . '%';
}
if ($metodo !== '') {
    $where .= ' AND EXISTS (SELECT 1 FROM orders_pagos pg WHERE pg.idord = o.idord AND pg.metodo = :metodo)';
    $params['metodo'] = $metodo;
}

// el cliente se identifica por su registro, si no por su documento y si no por el nombre
$ventas = $connect->prepare(
    "SELECT o.idord, o.idcli, o.nomcl, o.doc_tipo, o.doc_num, o.total_price, o.placed_on,
            COALESCE(CONCAT('c', o.idcli), CONCAT('d', o.doc_num), CONCAT('n', o.nomcl)) AS clave
       FROM orders o
      WHERE $where
      ORDER BY o.placed_on"
);
$ventas->execute($params);
$ventas = $ventas->fetchAll();

$clientes = [];
$ids = [];
foreach ($ventas as $v) {
    if (!isset($clientes[$v->clave])) {
        $clientes[$v->clave] = (object)[
            'nombre' => $v->nomcl, 'doc' => $v->doc_num ? $v->doc_tipo . ' ' . $v->doc_num : '',
            'compras' => 0, 'total' => 0, 'primera' => $v->placed_on, 'ultima' => $v->placed_on, 'medios' => [],
        ];
    }
    $c = $clientes[$v->clave];
    $c->compras++;
    $c->total += $v->total_price;
    $c->ultima = $v->placed_on;
    $ids[$v->idord] = $v->clave;
}

// cuanto pago cada cliente con cada medio
if ($ids) {
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $pagos = $connect->prepare("SELECT idord, metodo, SUM(monto) AS monto FROM orders_pagos WHERE idord IN ($marcas) GROUP BY idord, metodo");
    $pagos->execute(array_keys($ids));
    foreach ($pagos->fetchAll() as $p) {
        $c = $clientes[$ids[$p->idord]];
        $c->medios[$p->metodo] = ($c->medios[$p->metodo] ?? 0) + $p->monto;
    }
}

uasort($clientes, function ($a, $b) { return $b->total <=> $a->total; });
$totalGeneral = array_sum(array_map(function ($c) { return $c->total; }, $clientes));
$comprasTotal = array_sum(array_map(function ($c) { return $c->compras; }, $clientes));
$mejor = $clientes ? reset($clientes) : null;

$seccion = 'reportes';
$migas = 'Reportes / Clientes';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <?php pestanas_reportes('clientes'); ?>

                <form class="filtros" method="GET" action="">
                    <label>Desde <input type="date" name="desde" value="<?php echo e($desde); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Hasta <input type="date" name="hasta" value="<?php echo e($hasta); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Cliente <input type="text" name="buscar" value="<?php echo e($buscar); ?>" placeholder="Nombre, DNI o RUC"></label>
                    <label>Medio de pago
                        <select name="metodo">
<?php foreach ($metodos as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo $valor === $metodo ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <button type="submit">Filtrar</button>
                    <span class="atajos">
<?php foreach ($atajos as $texto => [$d, $h]): ?>
                        <a href="<?php echo url_atajo($d, $h); ?>"><?php echo $texto; ?></a>
<?php endforeach; ?>
                    </span>
                </form>

                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo count($clientes); ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress"><small>Clientes que compraron</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($totalGeneral, 2); ?></h2>
                            <span class="las la-money-bill"></span>
                        </div>
                        <div class="card-progress"><small>Total comprado (<?php echo $comprasTotal; ?> compras)</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($comprasTotal ? $totalGeneral / $comprasTotal : 0, 2); ?></h2>
                            <span class="las la-balance-scale"></span>
                        </div>
                        <div class="card-progress"><small>Ticket promedio</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2 class="texto-largo"><?php echo $mejor ? e($mejor->nombre) : '—'; ?></h2>
                            <span class="las la-crown"></span>
                        </div>
                        <div class="card-progress"><small>Mejor cliente<?php echo $mejor ? ' (S/' . number_format($mejor->total, 2) . ')' : ''; ?></small></div>
                    </div>
                </div>

                <div class="records table-responsive">
                    <div class="record-header">
                        <h3>Compras por cliente del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></h3>
                    </div>
                    <div>
<?php if ($clientes): ?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                    <th><span class="las la-sort"></span>Cliente</th>
                                    <th><span class="las la-sort"></span>Documento</th>
                                    <th><span class="las la-sort"></span>Compras</th>
                                    <th><span class="las la-sort"></span>Total</th>
                                    <th><span class="las la-sort"></span>Ticket prom.</th>
                                    <th><span class="las la-sort"></span>Primera</th>
                                    <th><span class="las la-sort"></span>Última</th>
                                    <th>Medios de pago</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td><?php echo e($c->nombre); ?></td>
                                    <td><?php echo e($c->doc ?: '—'); ?></td>
                                    <td><?php echo $c->compras; ?></td>
                                    <td data-order="<?php echo $c->total; ?>">S/<?php echo number_format($c->total, 2); ?></td>
                                    <td data-order="<?php echo $c->total / $c->compras; ?>">S/<?php echo number_format($c->total / $c->compras, 2); ?></td>
                                    <td data-order="<?php echo e($c->primera); ?>"><?php echo date('d/m/Y', strtotime($c->primera)); ?></td>
                                    <td data-order="<?php echo e($c->ultima); ?>"><?php echo date('d/m/Y', strtotime($c->ultima)); ?></td>
                                    <td>
<?php foreach ($c->medios as $m => $monto): ?>
                                        <span class="medio-pago"><?php echo e($m); ?> S/<?php echo number_format($monto, 2); ?></span>
<?php endforeach; ?>
                                    </td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php else: ?>
                        <p class="sin-datos">No hay compras de clientes con estos filtros.</p>
<?php endif; ?>
                    </div>
                </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
