<?php
// carrito de venta o de compra (ventas/cart.php y compra/cart.php).
// La pagina define $tipoCarrito ('venta' o 'compra') y ya incluyo layout/cabecera.php.
// Los cambios de cantidad los procesa backend/php/upd_cart(_purchase).php, que incluye la pagina
if (!isset($tipoCarrito) || !isset(CARRITOS[$tipoCarrito])) {
    exit;
}

require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/selector_cantidad.php';

$esCompra = $tipoCarrito === 'compra';
$modifica = $_SESSION[CARRITOS[$tipoCarrito]['sesion']] ?? null;
$lineas   = carrito_lineas($connect, $tipoCarrito, (int)$_SESSION['id']);
// al modificar una venta, sus unidades vuelven al confirmar: cuentan como disponibles
$devuelve = $esCompra ? [] : unidades_venta_modificada($connect);
// lineas que piden mas de lo disponible (ej. el stock bajo despues de agregarlas): asi no se puede cobrar
$faltan = 0;
foreach ($lineas as $l) {
    if (!$esCompra && (int)$l['quantity'] > (int)$l['stock'] + ($devuelve[$l['idprod']] ?? 0)) {
        $faltan++;
    }
}

$suma = 0;
foreach ($lineas as $l) {
    $suma += $l['price'] * $l['quantity'];
}
// venta: el precio ya incluye igv; compra: se muestra el subtotal sin igv (el igv se define al finalizar)
$total = $esCompra ? igv_agregar($suma)[0] : round($suma, 2);
?>

            <div class="page-content pagina-llena">
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
                    <!-- la lista se desplaza por dentro; el total y los botones quedan siempre a la vista -->
                    <?php echo csrf_campo(); ?>
                    <div class="tabla-sin-paginar">
                    <table width="100%" class="tabla-carrito">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
<?php if (!$esCompra): ?>
                                <th>Precio</th>
<?php endif; ?>
                                <th>Stock</th>
                                <th><?php echo $esCompra ? 'Cantidad y costo (sin IGV)' : 'Cantidad'; ?></th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($lineas as $l): ?>
<?php $disponible = (int)$l['stock'] + ($devuelve[$l['idprod']] ?? 0); ?>
<?php $excede = !$esCompra && (int)$l['quantity'] > $disponible; ?>
                            <tr data-precio="<?php echo e($l['price']); ?>"<?php echo $esCompra ? '' : ' data-disponible="' . $disponible . '"'; ?><?php echo $excede ? ' class="fila-sin-stock"' : ''; ?>>
                                <td><?php echo e($l['codpro']); ?></td>
                                <td><b><?php echo e($l['nomprd']); ?></b></td>
<?php if (!$esCompra): ?>
                                <td><span class="sin-salto">S/ <?php echo number_format($l['price'], 2); ?></span></td>
<?php endif; ?>
                                <td><span class="sin-salto"><?php echo $disponible; ?></span><span class="chip peligro aviso-fila">Solo hay <?php echo $disponible; ?></span></td>
                                <td><?php echo selector_cantidad($tipoCarrito, $l, $esCompra ? 9999 : max(1, $disponible), '', $esCompra); ?></td>
                                <td><b data-subtotal class="sin-salto"><?php echo 'S/ ' . number_format($l['price'] * $l['quantity'], 2); ?></b></td>
                                <td>
                                    <a href="eliminar.php?id=<?php echo (int)$l['idlinea']; ?>" class="btn-icono peligro" title="Quitar del carrito" onclick="return confirm('¿Quitar este producto del carrito?');"><span class="las la-trash-alt"></span></a>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
<?php endif; ?>

                    <div class="carrito-pie">
                        <div class="carrito-total">
                            <small><?php echo $esCompra ? 'Subtotal (sin IGV)' : 'Total a pagar'; ?></small>
                            <strong id="total-carrito">S/ <?php echo number_format($total, 2); ?></strong>
                            <span><?php echo $esCompra ? 'El IGV se define al finalizar la compra' : 'Precios con IGV incluido'; ?></span>
                        </div>
                        <div class="carrito-lado">
                        <p class="nota alerta" id="aviso-stock"<?php echo $faltan ? '' : ' hidden'; ?>><span class="las la-exclamation-triangle"></span> Hay productos que piden más de lo que hay en stock: baja la cantidad o quítalos para poder cobrar.</p>
                        <div class="carrito-acciones">
                            <a href="nuevo.php" class="btn-secundario"><span class="las la-plus"></span> Agregar más productos</a>
                            <button type="button" id="proceder-pago" class="btn-primario" onclick="location.href='checkout.php'"<?php echo $lineas && !$faltan ? '' : ' disabled'; ?>>
                                Proceder al pago <span class="las la-arrow-right"></span>
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
