<?php
// carrito de venta o de compra (ventas/cart.php y compra/cart.php).
// La pagina define $tipoCarrito ('venta' o 'compra') y ya incluyo layout/cabecera.php.
// Los cambios de cantidad los procesa backend/php/upd_cart(_purchase).php, que incluye la pagina
if (!isset($tipoCarrito) || !isset(CARRITOS[$tipoCarrito])) {
    exit;
}

require_once __DIR__ . '/../../backend/config/Conexion.php';

$esCompra = $tipoCarrito === 'compra';
$modifica = $_SESSION[CARRITOS[$tipoCarrito]['sesion']] ?? null;
$lineas   = carrito_lineas($connect, $tipoCarrito, (int)$_SESSION['id']);

$suma = 0;
foreach ($lineas as $l) {
    $suma += $l['price'] * $l['quantity'];
}
// venta: el precio ya incluye igv; compra: se muestra el subtotal sin igv (el igv se define al finalizar)
$total = $esCompra ? igv_agregar($suma)[0] : round($suma, 2);
?>

            <div class="page-content">
<?php if ($modifica): ?>
<?php include __DIR__ . '/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="cabecera-form">
                    <div>
                        <h1><?php echo $esCompra ? 'Carrito de compra' : 'Carrito de venta'; ?></h1>
                        <p><?php echo $esCompra ? 'Revisa cantidades y costos (sin IGV) antes de registrar la compra' : 'Revisa los productos y cantidades antes de cobrar'; ?></p>
                    </div>
                </div>

                <div class="records table-responsive">
<?php if (!$lineas): ?>
                    <p class="sin-datos">Tu carrito está vacío. <a href="nuevo.php">Agrega productos</a> para empezar.</p>
<?php else: ?>
                    <table width="100%" class="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
<?php if (!$esCompra): ?>
                                <th>Precio</th>
<?php endif; ?>
                                <th>Stock</th>
                                <th><?php echo $esCompra ? 'Cantidad y costo unit. (sin IGV)' : 'Cantidad'; ?></th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($lineas as $l): ?>
                            <tr>
                                <td><?php echo e($l['codpro']); ?></td>
                                <td><b><?php echo e($l['nomprd']); ?></b></td>
<?php if (!$esCompra): ?>
                                <td>S/ <?php echo number_format($l['price'], 2); ?></td>
<?php endif; ?>
                                <td><?php echo (int)$l['stock']; ?></td>
                                <td>
                                    <form action="" method="POST" class="cantidad-carrito">
                                        <?php echo csrf_campo(); ?>
                                        <input type="hidden" name="prdt" value="<?php echo (int)$l['idlinea']; ?>">
                                        <input type="number" name="p_qty" value="<?php echo (int)$l['quantity']; ?>" min="1" max="<?php echo $esCompra ? 9999 : max(1, (int)$l['stock']); ?>" aria-label="Cantidad" required>
<?php if ($esCompra): ?>
                                        <div class="campo-con-unidad">
                                            <span class="unidad">S/</span>
                                            <input type="number" name="p_cost" step="0.01" min="0.01" value="<?php echo e(number_format($l['price'], 2, '.', '')); ?>" aria-label="Costo unitario sin IGV" required>
                                        </div>
<?php endif; ?>
                                        <button type="submit" name="update_qty" class="btn-icono" title="Actualizar"><span class="las la-sync"></span></button>
                                    </form>
                                </td>
                                <td><b>S/ <?php echo number_format($l['price'] * $l['quantity'], 2); ?></b></td>
                                <td>
                                    <a href="eliminar.php?id=<?php echo (int)$l['idlinea']; ?>" class="btn-icono peligro" title="Quitar del carrito" onclick="return confirm('¿Quitar este producto del carrito?');"><span class="las la-trash-alt"></span></a>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php endif; ?>

                    <div class="carrito-pie">
                        <div class="carrito-total">
                            <small><?php echo $esCompra ? 'Subtotal (sin IGV)' : 'Total a pagar'; ?></small>
                            <strong>S/ <?php echo number_format($total, 2); ?></strong>
                            <span><?php echo $esCompra ? 'El IGV se define al finalizar la compra' : 'Precios con IGV incluido'; ?></span>
                        </div>
                        <div class="carrito-acciones">
                            <a href="nuevo.php" class="btn-secundario"><span class="las la-plus"></span> Agregar más productos</a>
                            <button type="button" class="btn-primario" onclick="location.href='checkout.php'"<?php echo $lineas ? '' : ' disabled'; ?>>
                                Proceder al pago <span class="las la-arrow-right"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
