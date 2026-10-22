<?php
require_once __DIR__ . '/selector_cantidad.php';

// celdas de una fila de la lista de productos para el carrito (las pide elegir_productos_datos.php).
//   $d           producto (idprod, codpro, nomprd, foto, precio, precio_compra, stock, nomarc)
//   $linea       linea del carrito de ese producto o null; con linea se muestra el selector "- n +"
//   $disponible  unidades que se pueden vender (en compras no hay tope)
function fila_elegir_producto($d, $esCompra, $linea, $disponible)
{
    $celdas = [
        '<img src="../../backend/img/subidas/' . e($d->foto) . '" alt="" width="50" class="miniatura">',
        e($d->codpro),
        '<b>' . e($d->nomprd) . '</b>',
        e($d->nomarc),
        '<span class="sin-salto">S/ ' . number_format($d->precio, 2) . '</span>',
    ];
    if ($esCompra) {
        $celdas[] = $d->precio_compra > 0 ? '<span class="sin-salto">S/ ' . number_format($d->precio_compra, 2) . '</span>' : '—';
    }
    $celdas[] = (string)$disponible;

    if (!$esCompra && $disponible <= 0 && !$linea) {
        $celdas[] = '<span class="chip peligro">Sin stock</span>';
        return $celdas;
    }

    $max = $esCompra ? 9999 : $disponible;
    // boton "Agregar" (1 unidad) mientras el producto no esta en el carrito; despues, el SelectorCantidad
    ob_start();
?>
    <form method="post" action="" class="cantidad-carrito form-agregar" data-accion="../../backend/php/<?php echo $esCompra ? 'add_cart_purchase' : 'add_cart'; ?>.php"<?php echo $linea ? ' hidden' : ''; ?>>
        <?php echo csrf_campo(); ?>
        <input type="hidden" name="prdt" value="<?php echo (int)$d->idprod; ?>">
        <input type="hidden" name="p_qty" value="1">
<?php if ($esCompra && $d->precio_compra > 0): ?>
        <?php // entra con el ultimo costo (columna de al lado); si cambio, se corrige en el carrito ?>
        <input type="hidden" name="p_cost" value="<?php echo e(number_format($d->precio_compra, 2, '.', '')); ?>">
<?php elseif ($esCompra): ?>
        <?php // nunca se compro: no hay costo anterior y hay que escribirlo ?>
        <div class="campo-con-unidad" title="Costo unitario sin IGV de la factura del proveedor">
            <span class="unidad">S/</span>
            <input type="number" name="p_cost" step="0.01" min="0.01" placeholder="Costo sin IGV" aria-label="Costo unitario sin IGV" required>
        </div>
<?php endif; ?>
        <button type="submit" name="<?php echo $esCompra ? 'add_to_cart_purchase' : 'add_to_cart'; ?>" class="btn-primario btn-chico"><span class="las la-cart-plus"></span> Agregar</button>
    </form>
<?php
    $celdas[] = selector_cantidad($esCompra ? 'compra' : 'venta', $linea, $max, ob_get_clean());
    return $celdas;
}
