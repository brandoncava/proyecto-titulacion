<?php
// lista de productos para armar el carrito (ventas/nuevo.php y compra/nuevo.php).
// La pagina define $tipoCarrito ('venta' o 'compra') y ya incluyo layout/cabecera.php con $tablas = true.
// Lo agregado lo procesa backend/php/add_cart(_purchase).php, que incluye la pagina
if (!isset($tipoCarrito) || !isset(CARRITOS[$tipoCarrito])) {
    exit;
}

require_once __DIR__ . '/../../backend/config/Conexion.php';

$esCompra  = $tipoCarrito === 'compra';
$productos = $connect->query(
    'SELECT p.idprod, p.codpro, p.nomprd, p.foto, p.precio, p.precio_compra, p.stock, m.nomarc
       FROM productos p INNER JOIN marca m ON m.idmar = p.idmar
      WHERE p.state = 1 ORDER BY p.idprod DESC'
)->fetchAll();
// unidades que ya estan en el carrito, por producto (se marcan en la lista)
$yaEnCarrito = array_column(carrito_lineas($connect, $tipoCarrito, (int)$_SESSION['id']), 'quantity', 'idprod');
$enCarrito = count($yaEnCarrito);
$modifica  = $_SESSION[CARRITOS[$tipoCarrito]['sesion']] ?? null;
?>

            <div class="page-content">
<?php if ($modifica): ?>
<?php include __DIR__ . '/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="cabecera-form">
                    <div>
                        <h1><?php echo $esCompra ? 'Nueva compra' : 'Nueva venta'; ?></h1>
                        <p><?php echo $esCompra ? 'Agrega los productos que llegaron con su costo unitario sin IGV' : 'Busca los productos y agrégalos al carrito'; ?></p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="cart.php" class="btn-primario"><span class="las la-shopping-cart"></span> Ver carrito (<?php echo $enCarrito; ?>)</a>
                    </div>
                </div>

                <div class="records table-responsive">
<?php if (!$productos): ?>
                    <p class="sin-datos">No hay productos activos. Regístralos primero en Productos.</p>
<?php else: ?>
                    <table width="100%" id="example">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th><span class="las la-sort"></span>Código</th>
                                <th><span class="las la-sort"></span>Producto</th>
                                <th><span class="las la-sort"></span><?php echo $esCompra ? 'Precio venta' : 'Precio'; ?></th>
<?php if ($esCompra): ?>
                                <th><span class="las la-sort"></span>Último costo (sin IGV)</th>
<?php endif; ?>
                                <th><span class="las la-sort"></span>Stock</th>
                                <th><?php echo $esCompra ? 'Cantidad y costo (sin IGV)' : 'Cantidad'; ?></th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($productos as $d): ?>
                            <tr>
                                <td><img src="../../backend/img/subidas/<?php echo e($d->foto); ?>" alt="" width="50" class="miniatura"></td>
                                <td><?php echo e($d->codpro); ?></td>
                                <td><b><?php echo e($d->nomprd); ?></b><small><?php echo e($d->nomarc); ?>
<?php if (isset($yaEnCarrito[$d->idprod])): ?>
                                    <span class="chip morado"><span class="las la-shopping-cart"></span> En el carrito: <?php echo (int)$yaEnCarrito[$d->idprod]; ?></span>
<?php endif; ?>
                                </small></td>
                                <td>S/ <?php echo number_format($d->precio, 2); ?></td>
<?php if ($esCompra): ?>
                                <td><?php echo $d->precio_compra > 0 ? 'S/ ' . number_format($d->precio_compra, 2) : '—'; ?></td>
<?php endif; ?>
                                <td><?php echo (int)$d->stock; ?></td>
                                <td>
<?php if (!$esCompra && (int)$d->stock <= 0): ?>
                                    <span class="chip peligro">Sin stock</span>
<?php else: ?>
                                    <form method="post" action="" class="cantidad-carrito">
                                        <?php echo csrf_campo(); ?>
                                        <input type="hidden" name="prdt" value="<?php echo (int)$d->idprod; ?>">
                                        <input type="number" name="p_qty" value="1" min="1"<?php echo $esCompra ? '' : ' max="' . (int)$d->stock . '"'; ?> aria-label="Cantidad" required>
<?php if ($esCompra): ?>
                                        <div class="campo-con-unidad">
                                            <span class="unidad">S/</span>
                                            <input type="number" name="p_cost" step="0.01" min="0.01" value="<?php echo $d->precio_compra > 0 ? e(number_format($d->precio_compra, 2, '.', '')) : ''; ?>" placeholder="Costo" aria-label="Costo unitario sin IGV" required>
                                        </div>
<?php endif; ?>
                                        <button type="submit" name="<?php echo $esCompra ? 'add_to_cart_purchase' : 'add_to_cart'; ?>" class="btn-primario btn-chico"><span class="las la-cart-plus"></span> Agregar</button>
                                    </form>
<?php endif; ?>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php endif; ?>
                </div>
            </div>
