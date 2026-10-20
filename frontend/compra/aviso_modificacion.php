<?php
// aviso de "modificando una compra" (lo incluyen cart.php y checkout.php)
if (!isset($modifica)) {
    exit;
}
?>
                <div class="aviso-modificacion">
                    <span class="las la-edit"></span>
                    <div>
                        <b>Estás modificando la compra N° <?php echo (int)$modifica['idordpur']; ?>.</b>
                        Cambia productos, cantidades o costos y finaliza: se actualizará la misma compra y el stock se ajustará con la diferencia.
                    </div>
                    <a href="cancelar_modificacion.php" onclick="return confirm('¿Cancelar la modificación? El carrito se vaciará y la compra queda como estaba.');">Cancelar modificación</a>
                </div>
