<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('reportes');

require '../../backend/config/Conexion.php';
require __DIR__ . '/comun.php';

// stock bajo: igual o menos de estas unidades

$categorias = ['' => 'Todas'];
foreach ($connect->query('SELECT idcate, nocate FROM categoria WHERE state = 1 ORDER BY nocate') as $c) {
    $categorias[(string)$c->idcate] = $c->nocate;
}
$marcas = ['' => 'Todas'];
foreach ($connect->query('SELECT idmar, nomarc FROM marca WHERE state = 1 ORDER BY nomarc') as $m) {
    $marcas[(string)$m->idmar] = $m->nomarc;
}
$niveles = ['' => 'Todos', 'bajo' => 'Stock bajo (≤ ' . STOCK_BAJO . ')', 'sin' => 'Sin stock'];

$idcate = opcion_get('categoria', $categorias, '');
$idmar  = opcion_get('marca', $marcas, '');
$nivel  = opcion_get('stock', $niveles, '');

$where  = 'p.state = 1';
$params = $rango;
if ($idcate !== '') {
    $where .= ' AND p.idcate = :cate';
    $params['cate'] = (int)$idcate;
}
if ($idmar !== '') {
    $where .= ' AND p.idmar = :mar';
    $params['mar'] = (int)$idmar;
}
if ($nivel === 'bajo') {
    $where .= ' AND p.stock <= ' . STOCK_BAJO;
} elseif ($nivel === 'sin') {
    $where .= ' AND p.stock <= 0';
}

// ventas validas del periodo por producto
$lista = $connect->prepare(
    "SELECT p.idprod, p.codpro, p.nomprd, p.stock, p.precio, p.precio_compra, p.garantia_meses,
            c.nocate, m.nomarc,
            COALESCE(v.unidades, 0) AS vendidos, COALESCE(v.ingresos, 0) AS ingresos
       FROM productos p
       INNER JOIN categoria c ON c.idcate = p.idcate
       INNER JOIN marca m ON m.idmar = p.idmar
       LEFT JOIN (
            SELECT d.idprod, SUM(d.cantidad) AS unidades, SUM(d.subtotal) AS ingresos
              FROM orders_detalle d INNER JOIN orders o ON o.idord = d.idord
             WHERE o.anulada = 0 AND o.placed_on >= :desde AND o.placed_on < :hasta
             GROUP BY d.idprod
       ) v ON v.idprod = p.idprod
      WHERE $where
      ORDER BY vendidos DESC, p.nomprd"
);
$lista->execute($params);
$productos = $lista->fetchAll();

$tot = ['stock' => 0, 'valor' => 0, 'vendidos' => 0, 'ingresos' => 0, 'ganancia' => 0, 'bajos' => 0];
foreach ($productos as $p) {
    // el precio de venta incluye igv y el costo de compra no: se compara sin igv
    $p->ingresos_sin_igv = round($p->ingresos / (1 + IGV_TASA), 2);
    $p->ganancia = $p->precio_compra > 0 ? round($p->ingresos_sin_igv - $p->precio_compra * $p->vendidos, 2) : null;
    $p->margen = ($p->precio_compra > 0 && $p->precio > 0)
        ? round((($p->precio / (1 + IGV_TASA)) - $p->precio_compra) / ($p->precio / (1 + IGV_TASA)) * 100, 1)
        : null;

    $tot['stock']    += max(0, (int)$p->stock);
    $tot['valor']    += max(0, (int)$p->stock) * $p->precio_compra;
    $tot['vendidos'] += (int)$p->vendidos;
    $tot['ingresos'] += $p->ingresos;
    $tot['ganancia'] += (float)$p->ganancia;
    $tot['bajos']    += (int)$p->stock <= STOCK_BAJO ? 1 : 0;
}

$seccion = 'reportes';
$migas = 'Reportes / Productos';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

                <?php pestanas_reportes('productos'); ?>

                <form class="filtros" method="GET" action="">
                    <label>Ventas desde <input type="date" name="desde" value="<?php echo e($desde); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Hasta <input type="date" name="hasta" value="<?php echo e($hasta); ?>" max="<?php echo e($hoy); ?>"></label>
                    <label>Categoría
                        <select name="categoria">
<?php foreach ($categorias as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo (string)$valor === $idcate ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <label>Marca
                        <select name="marca">
<?php foreach ($marcas as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo (string)$valor === $idmar ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
<?php endforeach; ?>
                        </select>
                    </label>
                    <label>Stock
                        <select name="stock">
<?php foreach ($niveles as $valor => $texto): ?>
                            <option value="<?php echo e($valor); ?>"<?php echo $valor === $nivel ? ' selected' : ''; ?>><?php echo e($texto); ?></option>
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
                            <h2><?php echo count($productos); ?></h2>
                            <span class="las la-box"></span>
                        </div>
                        <div class="card-progress"><small>Productos (<?php echo $tot['bajos']; ?> con stock bajo)</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($tot['valor'], 2); ?></h2>
                            <span class="las la-warehouse"></span>
                        </div>
                        <div class="card-progress"><small>Valor del inventario (<?php echo $tot['stock']; ?> unid. a costo)</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $tot['vendidos']; ?></h2>
                            <span class="las la-shopping-bag"></span>
                        </div>
                        <div class="card-progress"><small>Unidades vendidas en el período</small></div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2>S/<?php echo number_format($tot['ganancia'], 2); ?></h2>
                            <span class="las la-chart-line"></span>
                        </div>
                        <div class="card-progress"><small>Ganancia estimada (sin IGV)</small></div>
                    </div>
                </div>

                <div class="records table-responsive">
                    <div class="record-header">
                        <h3>Productos — ventas del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></h3>
                    </div>
                    <div>
<?php if ($productos): ?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                    <th><span class="las la-sort"></span>Código</th>
                                    <th><span class="las la-sort"></span>Producto</th>
                                    <th><span class="las la-sort"></span>Categoría</th>
                                    <th><span class="las la-sort"></span>Marca</th>
                                    <th><span class="las la-sort"></span>Stock</th>
                                    <th><span class="las la-sort"></span>Costo (sin IGV)</th>
                                    <th><span class="las la-sort"></span>Precio venta</th>
                                    <th><span class="las la-sort"></span>Margen</th>
                                    <th><span class="las la-sort"></span>Vendidos</th>
                                    <th><span class="las la-sort"></span>Ingresos</th>
                                    <th><span class="las la-sort"></span>Ganancia est.</th>
                                    <th><span class="las la-sort"></span>Garantía</th>
                                </tr>
                            </thead>
                            <tbody>
<?php foreach ($productos as $p): ?>
                                <tr>
                                    <td><?php echo e($p->codpro); ?></td>
                                    <td><?php echo e($p->nomprd); ?></td>
                                    <td><?php echo e($p->nocate); ?></td>
                                    <td><?php echo e($p->nomarc); ?></td>
                                    <td data-order="<?php echo (int)$p->stock; ?>"><?php echo (int)$p->stock; ?><?php echo (int)$p->stock <= STOCK_BAJO ? ' <span class="chip alerta">bajo</span>' : ''; ?></td>
                                    <td data-order="<?php echo e($p->precio_compra); ?>"><?php echo $p->precio_compra > 0 ? 'S/' . number_format($p->precio_compra, 2) : '—'; ?></td>
                                    <td data-order="<?php echo e($p->precio); ?>">S/<?php echo number_format($p->precio, 2); ?></td>
                                    <td data-order="<?php echo $p->margen ?? -999; ?>"><?php echo $p->margen !== null ? $p->margen . '%' : '—'; ?></td>
                                    <td><?php echo (int)$p->vendidos; ?></td>
                                    <td data-order="<?php echo e($p->ingresos); ?>">S/<?php echo number_format($p->ingresos, 2); ?></td>
                                    <td data-order="<?php echo $p->ganancia ?? -999999; ?>"><?php echo $p->ganancia !== null ? 'S/' . number_format($p->ganancia, 2) : '<small>sin costo</small>'; ?></td>
                                    <td><?php echo (int)$p->garantia_meses; ?> meses</td>
                                </tr>
<?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="sin-datos">"Ganancia est." = ingresos sin IGV − costo de compra registrado × unidades vendidas. Los productos sin costo registrado no se calculan.</p>
<?php else: ?>
                        <p class="sin-datos">No hay productos con estos filtros.</p>
<?php endif; ?>
                    </div>
                </div>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
</body>
</html>
