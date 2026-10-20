<?php
// botones de cada venta en el listado (mostrar.php); usa $d = fila de orders
if (!isset($d)) {
    exit;
}
?>
            <a title="<?php echo e($d->tipc); ?>" href="../ventas/boleta.php?id=<?php echo e($d->idord); ?>" class="btn-icono"><span class="las la-file-alt"></span></a>
            <?php if ((int)$d->anulada === 1): ?>
                <span class="etiqueta-anulada" title="<?php echo e($d->motivo_anulacion); ?>">ANULADA</span>
            <?php elseif (puede('ventas_anular')): ?>
                <form method="POST" action="" style="display:inline" onsubmit="return confirm('¿Modificar esta venta? Sus productos se cargarán en el carrito; al finalizar, la venta se anulará y se emitirá un comprobante nuevo.');">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idord" value="<?php echo e($d->idord); ?>">
                    <button type="submit" name="modificar_venta" title="Modificar venta" class="btn-icono"><span class="las la-pen"></span></button>
                </form>
                <form method="POST" action="" style="display:inline" onsubmit="return anularVenta(this);">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="idord" value="<?php echo e($d->idord); ?>">
                    <input type="hidden" name="motivo" value="">
                    <button type="submit" name="anular_venta" title="Anular venta" class="btn-icono peligro"><span class="las la-ban"></span></button>
                </form>
            <?php endif; ?>
