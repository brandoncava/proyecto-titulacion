<?php
// aviso de "modificando una venta / compra" (lo incluyen el carrito y el checkout).
// La pagina define $tipoCarrito ('venta' o 'compra') y $modifica (datos guardados en la sesion)
if (!isset($tipoCarrito, $modifica)) {
    exit;
}

if ($tipoCarrito === 'venta') {
    $titulo  = 'Estás modificando la venta ' . $modifica['comprobante'] . '.';
    $texto   = 'Cambia los productos o cantidades y finaliza: la venta original se anulará y se emitirá un comprobante nuevo en su reemplazo.';
    $mensaje = '¿Cancelar la modificación? El carrito se vaciará y la venta original queda como estaba.';
} else {
    $titulo  = 'Estás modificando la compra N° ' . (int)$modifica['idordpur'] . '.';
    $texto   = 'Cambia productos, cantidades o costos y finaliza: se actualizará la misma compra y el stock se ajustará con la diferencia.';
    $mensaje = '¿Cancelar la modificación? El carrito se vaciará y la compra queda como estaba.';
}
?>
                <div class="aviso-modificacion">
                    <span class="las la-edit"></span>
                    <div>
                        <b><?php echo e($titulo); ?></b>
                        <?php echo e($texto); ?>
                    </div>
                    <a href="cancelar_modificacion.php" onclick="return confirm(<?php echo e(json_encode($mensaje, JSON_UNESCAPED_UNICODE)); ?>);">Cancelar modificación</a>
                </div>
