<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('reportes');

$MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$hoy = new DateTime('today');

// mes en curso hasta hoy, comparado con los mismos dias del mes anterior
$iniMes   = (clone $hoy)->modify('first day of this month');
$finMes   = (clone $hoy)->modify('+1 day');                       // fin exclusivo
$iniAnt   = (clone $iniMes)->modify('-1 month');
$finAnt   = (clone $iniAnt)->modify('+' . min((int)$hoy->format('j'), (int)$iniAnt->format('t')) . ' days');
$rango    = fn($d) => $d->format('Y-m-d');

$ventas = function ($desde, $hasta) use ($connect, $rango) {
    $q = $connect->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(total_price), 0) AS total FROM orders WHERE anulada = 0 AND placed_on >= ? AND placed_on < ?');
    $q->execute([$rango($desde), $rango($hasta)]);
    return $q->fetch();
};
$vMes = $ventas($iniMes, $finMes);
$vAnt = $ventas($iniAnt, $finAnt);
$variacion = $vAnt->total > 0 ? ($vMes->total - $vAnt->total) / $vAnt->total * 100 : null;
$ticket = $vMes->n > 0 ? $vMes->total / $vMes->n : 0;

$q = $connect->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(total_price), 0) AS total FROM orders_purchase WHERE anulada = 0 AND placed_on >= ? AND placed_on < ?');
$q->execute([$rango($iniMes), $rango($finMes)]);
$cMes = $q->fetch();

$garantias = $connect->query("SELECT SUM(estado <> 'Entregado') AS abiertas, SUM(estado = 'Listo') AS listas FROM garantias")->fetch();

// catalogo: los conteos que antes eran una tarjeta cada uno
$catalogo = $connect->query(
    'SELECT (SELECT COUNT(*) FROM clientes WHERE state = 1) AS clientes, (SELECT COUNT(*) FROM productos WHERE state = 1) AS productos,
            (SELECT COUNT(*) FROM categoria WHERE state = 1) AS categorias, (SELECT COUNT(*) FROM marca WHERE state = 1) AS marcas,
            (SELECT COUNT(*) FROM usuarios WHERE state = 1) AS usuarios'
)->fetch();

// ventas por dia de los ultimos 30 dias (los dias sin ventas van en 0)
$ini30 = (clone $hoy)->modify('-29 days');
$q = $connect->prepare('SELECT DATE(placed_on) AS dia, COUNT(*) AS n, SUM(total_price) AS total FROM orders WHERE anulada = 0 AND placed_on >= ? GROUP BY DATE(placed_on)');
$q->execute([$rango($ini30)]);
$porDia = [];
foreach ($q->fetchAll() as $r) {
    $porDia[$r->dia] = $r;
}
$serie = [];
$total30 = 0;
for ($d = clone $ini30; $d <= $hoy; $d->modify('+1 day')) {
    $k = $d->format('Y-m-d');
    $serie[] = ['dia' => $k, 'etiqueta' => $d->format('j') . ' ' . substr($MESES[(int)$d->format('n') - 1], 0, 3),
                'total' => round((float)($porDia[$k]->total ?? 0), 2), 'ventas' => (int)($porDia[$k]->n ?? 0)];
    $total30 += (float)($porDia[$k]->total ?? 0);
}

$stockBajo = $connect->query('SELECT idprod, nomprd, stock FROM productos WHERE state = 1 AND stock <= ' . STOCK_BAJO . ' ORDER BY stock, nomprd LIMIT 5')->fetchAll();
$nStockBajo = (int)$connect->query('SELECT COUNT(*) FROM productos WHERE state = 1 AND stock <= ' . STOCK_BAJO)->fetchColumn();

$q = $connect->prepare(
    'SELECT d.nombre, SUM(d.cantidad) AS unidades, SUM(d.subtotal) AS total
       FROM orders_detalle d INNER JOIN orders o ON o.idord = d.idord
      WHERE o.anulada = 0 AND o.placed_on >= ? AND o.placed_on < ?
      GROUP BY d.idprod, d.nombre ORDER BY unidades DESC, total DESC LIMIT 5'
);
$q->execute([$rango($iniMes), $rango($finMes)]);
$masVendidos = $q->fetchAll();
$maxUnidades = $masVendidos ? max(array_map(fn($p) => (int)$p->unidades, $masVendidos)) : 0;

$ultimas = $connect->query('SELECT idord, tipc, serie, numero, nomcl, total_price, placed_on, anulada FROM orders ORDER BY placed_on DESC, idord DESC LIMIT 6')->fetchAll();

$soles = fn($n) => 'S/ ' . number_format((float)$n, 2);

$seccion = 'dashboard';
$migas = 'Dashboard';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <div class="cabecera-form">
                    <div>
                        <h1>Resumen de <?php echo $MESES[(int)$hoy->format('n') - 1] . ' ' . $hoy->format('Y'); ?></h1>
                        <p>Del 1 al <?php echo $hoy->format('j'); ?>, comparado con los mismos días del mes anterior</p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="../reportes/ventas.php" class="btn-secundario"><span class="las la-chart-bar"></span> Reportes</a>
                        <a href="../ventas/nuevo.php" class="btn-primario"><span class="las la-plus"></span> Nueva venta</a>
                    </div>
                </div>

                <!-- indicadores: una sola tarjeta con los numeros que importan -->
                <section class="resumen-kpi">
                    <div class="kpis">
                        <div class="kpi">
                            <span class="kpi-etiqueta">Ventas del mes</span>
                            <strong class="kpi-valor"><?php echo $soles($vMes->total); ?></strong>
                            <span class="kpi-detalle">
<?php if ($variacion !== null): ?>
                                <span class="kpi-delta <?php echo $variacion >= 0 ? 'sube' : 'baja'; ?>">
                                    <span class="las la-arrow-<?php echo $variacion >= 0 ? 'up' : 'down'; ?>"></span><?php echo ($variacion >= 0 ? '+' : '') . number_format($variacion, 1); ?>%
                                </span>
                                vs. mes anterior
<?php else: ?>
                                Sin ventas el mes anterior
<?php endif; ?>
                            </span>
                        </div>
                        <div class="kpi">
                            <span class="kpi-etiqueta">Comprobantes emitidos</span>
                            <strong class="kpi-valor"><?php echo (int)$vMes->n; ?></strong>
                            <span class="kpi-detalle">Ticket promedio <?php echo $soles($ticket); ?></span>
                        </div>
                        <div class="kpi">
                            <span class="kpi-etiqueta">Compras del mes</span>
                            <strong class="kpi-valor"><?php echo $soles($cMes->total); ?></strong>
                            <span class="kpi-detalle"><?php echo (int)$cMes->n; ?> compra(s) a proveedores</span>
                        </div>
                        <div class="kpi">
                            <span class="kpi-etiqueta">Garantías en curso</span>
                            <strong class="kpi-valor"><?php echo (int)$garantias->abiertas; ?></strong>
                            <span class="kpi-detalle"><?php echo (int)$garantias->listas; ?> lista(s) para entregar</span>
                        </div>
                    </div>
                    <nav class="catalogo-resumen" aria-label="Catálogo">
                        <a href="../clientes/mostrar.php"><b><?php echo (int)$catalogo->clientes; ?></b> clientes</a>
                        <a href="../productos/mostrar.php"><b><?php echo (int)$catalogo->productos; ?></b> productos</a>
                        <a href="../categorias/mostrar.php"><b><?php echo (int)$catalogo->categorias; ?></b> categorías</a>
                        <a href="../marcas/mostrar.php"><b><?php echo (int)$catalogo->marcas; ?></b> marcas</a>
                        <a href="../accesos/mostrar.php"><b><?php echo (int)$catalogo->usuarios; ?></b> usuarios</a>
                    </nav>
                </section>

                <div class="tablero">
                    <section class="tarjeta-form panel-ancho">
                        <div class="panel-titulo">
                            <h2><span class="las la-chart-bar"></span> Ventas de los últimos 30 días</h2>
                            <span class="panel-dato">Total <b><?php echo $soles($total30); ?></b></span>
                        </div>
                        <div id="grafico-ventas" class="grafico" role="img" aria-label="Ventas por día de los últimos 30 días"></div>
                        <details class="ver-datos">
                            <summary>Ver datos</summary>
                            <table class="tabla-datos">
                                <thead><tr><th>Día</th><th>Comprobantes</th><th>Total</th></tr></thead>
                                <tbody>
<?php foreach (array_reverse($serie) as $s): ?>
                                    <tr><td><?php echo date('d/m/Y', strtotime($s['dia'])); ?></td><td><?php echo $s['ventas']; ?></td><td><?php echo $soles($s['total']); ?></td></tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </details>
                    </section>

                    <section class="tarjeta-form">
                        <div class="panel-titulo">
                            <h2><span class="las la-exclamation-triangle"></span> Stock bajo</h2>
                            <span class="panel-dato"><?php echo $nStockBajo; ?> producto(s) con <?php echo STOCK_BAJO; ?> o menos</span>
                        </div>
<?php if (!$stockBajo): ?>
                        <p class="lista-vacia">Todo el inventario está sobre <?php echo STOCK_BAJO; ?> unidades.</p>
<?php else: ?>
                        <ul class="lista-panel">
<?php foreach ($stockBajo as $p): ?>
                            <li>
                                <a href="../productos/editar.php?id=<?php echo (int)$p->idprod; ?>"><?php echo e($p->nomprd); ?></a>
                                <span class="chip <?php echo (int)$p->stock <= 0 ? 'peligro' : 'alerta'; ?>"><?php echo (int)$p->stock <= 0 ? 'Sin stock' : (int)$p->stock . ' unid.'; ?></span>
                            </li>
<?php endforeach; ?>
                        </ul>
                        <a class="enlace-panel" href="../compra/nuevo.php">Registrar una compra <span class="las la-arrow-right"></span></a>
<?php endif; ?>
                    </section>

                    <section class="tarjeta-form">
                        <div class="panel-titulo">
                            <h2><span class="las la-trophy"></span> Más vendidos del mes</h2>
                            <span class="panel-dato">unidades</span>
                        </div>
<?php if (!$masVendidos): ?>
                        <p class="lista-vacia">Todavía no hay ventas este mes.</p>
<?php else: ?>
                        <ul class="barras-panel">
<?php foreach ($masVendidos as $p): ?>
                            <li title="<?php echo e($p->nombre . ' · ' . $soles($p->total)); ?>">
                                <span class="barra-nombre"><?php echo e($p->nombre); ?></span>
                                <span class="barra-pista">
                                    <span class="barra" style="width: calc((100% - 36px) * <?php echo round($p->unidades / $maxUnidades, 3); ?>)"></span>
                                    <span class="barra-valor"><?php echo (int)$p->unidades; ?></span>
                                </span>
                            </li>
<?php endforeach; ?>
                        </ul>
<?php endif; ?>
                    </section>

                    <section class="tarjeta-form panel-ancho-2">
                        <div class="panel-titulo">
                            <h2><span class="las la-receipt"></span> Últimas ventas</h2>
                            <a class="panel-dato" href="../ventas/mostrar.php">Ver todas</a>
                        </div>
                        <ul class="lista-panel">
<?php foreach ($ultimas as $v): ?>
                            <li>
                                <div>
                                    <b><?php echo e($v->nomcl); ?></b>
                                    <small><?php echo e(($v->serie ? formato_comprobante($v->serie, $v->numero) : 'Venta ' . $v->idord) . ' · ' . date('d/m/Y H:i', strtotime($v->placed_on))); ?></small>
                                </div>
<?php if ((int)$v->anulada === 1): ?>
                                <span class="chip peligro">Anulada</span>
<?php else: ?>
                                <b class="monto"><?php echo $soles($v->total_price); ?></b>
<?php endif; ?>
                            </li>
<?php endforeach; ?>
                        </ul>
                    </section>
                </div>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <script>window.VENTAS_30_DIAS = <?php echo json_encode($serie, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE); ?>;</script>
    <script src="../../backend/js/dashboard.js"></script>
</body>
</html>
