<?php
// SelectorCantidad: "- n +" que cambia al momento la cantidad de una linea del carrito (lo maneja
// backend/js/carrito.js). Lo usan la lista de productos y el carrito, de ventas y de compras.
//   $tipo    'venta' o 'compra' (decide a que manejador se guarda)
//   $linea   linea del carrito (idlinea, quantity, price) o null si el producto aun no esta
//   $max     tope de unidades (en ventas el stock disponible)
//   $antes   html que va dentro del componente antes del selector (ej. el boton "Agregar"
//            que se muestra mientras el producto no esta en el carrito)
//   $costo   en compras: true muestra el campo de costo de la linea junto al selector
function selector_cantidad($tipo, $linea, $max, $antes = '', $costo = false)
{
    $cantidad = (int)($linea['quantity'] ?? 0);
    $datos = ' data-linea="' . (int)($linea['idlinea'] ?? 0) . '" data-max="' . (int)$max . '"'
           . ' data-actualizar="../../backend/php/' . ($tipo === 'compra' ? 'upd_cart_purchase' : 'upd_cart') . '.php"'
           . ' data-quitar="eliminar.php"';
    if ($tipo === 'compra') {
        $datos .= ' data-costo="' . e($linea['price'] ?? '') . '"';
    }
    ob_start();
?>
<div class="accion-carrito"<?php echo $datos; ?>>
    <?php echo $antes; ?>
    <div class="selector-cantidad"<?php echo $linea ? '' : ' hidden'; ?>>
        <button type="button" class="restar" aria-label="Quitar una unidad"><span class="las la-minus"></span></button>
        <input type="number" min="0" max="<?php echo (int)$max; ?>" value="<?php echo $cantidad; ?>" aria-label="Cantidad en el carrito">
        <button type="button" class="sumar" aria-label="Agregar una unidad"<?php echo $cantidad >= $max ? ' disabled' : ''; ?>><span class="las la-plus"></span></button>
    </div>
<?php if ($costo && $linea): ?>
    <div class="campo-con-unidad costo-linea" title="Costo unitario sin IGV">
        <span class="unidad">S/</span>
        <input type="number" step="0.01" min="0.01" value="<?php echo e(number_format((float)$linea['price'], 2, '.', '')); ?>" aria-label="Costo unitario sin IGV">
    </div>
<?php endif; ?>
</div>
<?php
    return ob_get_clean();
}
