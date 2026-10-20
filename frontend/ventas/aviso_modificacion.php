<?php
// aviso de "modificando una venta" (lo incluyen cart.php y checkout.php)
if (!isset($modifica)) {
    exit;
}
?>
                <div class="aviso-modificacion">
                    <span class="las la-edit"></span>
                    <div>
                        <b>Estás modificando la venta <?php echo e($modifica['comprobante']); ?>.</b>
                        Cambia los productos o cantidades y finaliza: la venta original se anulará y se emitirá un comprobante nuevo en su reemplazo.
                    </div>
                    <a href="cancelar_modificacion.php" onclick="return confirm('¿Cancelar la modificación? El carrito se vaciará y la venta original queda como estaba.');">Cancelar modificación</a>
                </div>
