<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('reportes');

require '../../backend/config/Conexion.php';
require __DIR__ . '/comun.php';

$estados = ['validas' => 'Solo válidas', 'anuladas' => 'Solo anuladas', 'todas' => 'Todas'];
$estado  = opcion_get('estado', $estados, 'validas');

$proveedores = ['' => 'Todos'];
foreach ($connect->query('SELECT idprov, nomprv FROM proveedores ORDER BY nomprv') as $p) {
    $proveedores[(string)$p->idprov] = $p->nomprv;
}
$idprov = opcion_get('proveedor', $proveedores, '');

$comprobantes = ['' => 'Todos'] + array_combine(COMPROBANTES_COMPRA, COMPROBANTES_COMPRA);
$tipc = opcion_get('comprobante', $comprobantes, '');

$metodos = ['' => 'Todas'] + array_combine(METODOS_PAGO, METODOS_PAGO);
$metodo = opcion_get('metodo', $metodos, '');

// filtros que valen para el resumen y para la lista (el estado solo para la lista)
$where = 'o.placed_on >= :desde AND o.placed_on < :hasta';
$params = $rango;
if ($idprov !== '') {
    $where .= ' AND o.idprov = :prov';
    $params['prov'] = (int)$idprov;
}
if ($tipc !== '') {
    $where .= ' AND o.tipc = :tipc';
    $params['tipc'] = $tipc;
}
if ($metodo !== '') {
    $where .= ' AND o.method = :metodo';
    $params['metodo'] = $metodo;
}

$resumen = $connect->prepare(
    "SELECT SUM(o.anulada = 0)                                                   AS compras,
            COALESCE(SUM(CASE WHEN o.anulada = 0 THEN o.total_price END), 0)     AS total,
            COALESCE(SUM(CASE WHEN o.anulada = 0 THEN o.igv END), 0)             AS igv,
            SUM(o.anulada = 1)                                                   AS anuladas,
            COALESCE(SUM(CASE WHEN o.anulada = 1 THEN o.total_price END), 0)     AS total_anulado
       FROM orders_purchase o
      WHERE $where"
);
$resumen->execute($params);
$r = $resumen->fetch();
$promedio = $r->compras ? $r->total / $r->compras : 0;

$porProveedor = $connect->prepare(
    "SELECT p.nomprv, COUNT(*) AS compras, SUM(o.total_price) AS total
       FROM orders_purchase o INNER JOIN proveedores p ON p.idprov = o.idprov
      WHERE $where AND o.anulada = 0
      GROUP BY p.idprov, p.nomprv
      ORDER BY total DESC"
);
$porProveedor->execute($params);
$proveedoresTotal = $porProveedor->fetchAll();

$filtroEstado = ['validas' => ' AND o.anulada = 0', 'anuladas' => ' AND o.anulada = 1', 'todas' => ''][$estado];
$lista = $connect->prepare(
    "SELECT o.idordpur, o.placed_on, o.tipc, o.serie_doc, o.numero_doc, o.fecha_emision, p.nomprv, o.method, o.igv, o.total_price,
            o.anulada, o.motivo_anulacion, o.modificada_on, u.nombre AS comprador
       FROM orders_purchase o
       INNER JOIN proveedores p ON p.idprov = o.idprov
       LEFT JOIN usuarios u ON u.id = o.user_id
      WHERE $where $filtroEstado
      ORDER BY o.placed_on DESC, o.idordpur DESC"
);
$lista->execute($params);
$compras = $lista->fetchAll();

$seccion = 'reportes';
$migas = 'Reportes / Compras';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <?php pestanas_reportes('compras'); ?>

                <form class="filtros" method="GET" action="">
                    <label>Desde <input type="date" name="desde" value="<?php echo e($desde); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Hasta <input type="date" name="hasta" value="<?php echo e($hasta); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Proveedor
                        <select name="proveedor">
<?php foreach ($proveedores as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo (string)$valor === $idprov ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <label>Comprobante
                        <select name="comprobante">
<?php foreach ($comprobantes as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo $valor === $tipc ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <label>Forma de pago
                        <select name="metodo">
<?php foreach ($metodos as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo $valor === $metodo ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
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
                        <a href="<?php echo url_atajo($d, $h); ?>"><?php echo $texto; ?></a>
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

<?php if ($proveedoresTotal): ?>
                <div class="records por-pago">
                    <h3>Por proveedor</h3>
                    <table width="100%">
                        <thead>
                            <tr><th>Proveedor</th><th>Compras</th><th>Total</th></tr>
                        </thead>
                        <tbody>
<?php foreach ($proveedoresTotal as $p): ?>
                            <tr>
                                <td><?php echo e($p->nomprv); ?></td>
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
                                    <th><span class="las la-sort"></span>Registro</th>
                                    <th><span class="las la-sort"></span>Comprobante</th>
                                    <th><span class="las la-sort"></span>Emisión</th>
                                    <th><span class="las la-sort"></span>Proveedor</th>
                                    <th><span class="las la-sort"></span>Comprador</th>
                                    <th><span class="las la-sort"></span>Pago</th>
                                    <th><span class="las la-sort"></span>IGV</th>
                                    <th><span class="las la-sort"></span>Total</th>
                                    <th><span class="las la-sort"></span>Estado</th>
                                    <th>Ver</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($compras as $v): ?>
                                <tr>
                                    <td><?php echo e($v->idordpur); ?></td>
                                    <td data-order="<?php echo e($v->placed_on); ?>"><?php echo date('d/m/Y H:i', strtotime($v->placed_on)); ?></td>
                                    <td><?php echo e($v->tipc); ?><?php echo !empty($v->serie_doc) ? ' ' . e($v->serie_doc . '-' . $v->numero_doc) : ''; ?></td>
                                    <td><?php echo $v->fecha_emision ? date('d/m/Y', strtotime($v->fecha_emision)) : '—'; ?></td>
                                    <td><?php echo e($v->nomprv); ?></td>
                                    <td><?php echo e($v->comprador ?? '—'); ?></td>
                                    <td><?php echo e($v->method); ?></td>
                                    <td>S/<?php echo number_format($v->igv, 2); ?></td>
                                    <td data-order="<?php echo e($v->total_price); ?>">S/<?php echo number_format($v->total_price, 2); ?></td>
<?php if ((int)$v->anulada === 1): ?>
                                    <td><span class="estado-anulada" title="<?php echo e($v->motivo_anulacion); ?>">ANULADA</span></td>
<?php else: ?>
                                    <td>Válida<?php echo $v->modificada_on ? '<br><small>Modificada ' . date('d/m/Y', strtotime($v->modificada_on)) . '</small>' : ''; ?></td>
<?php endif; ?>
                                    <td><a title="Comprobante" href="../compra/comprobante.php?id=<?php echo e($v->idordpur); ?>" class="btn-icono"><span class="las la-file-alt"></span></a></td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
<?php else: ?>
                        <p class="sin-datos">No hay compras con estos filtros.</p>
<?php endif; ?>
                    </div>
                </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
