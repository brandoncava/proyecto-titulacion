<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

require_once('../../backend/config/Conexion.php');
$user_id = $_SESSION['id'];
$modifica = $_SESSION['modifica_venta'] ?? null;

// se cobra el precio guardado en el carrito
$select_cart_items = $connect->prepare(
    'SELECT c.name, c.price, c.quantity FROM cart c WHERE c.user_id = ? ORDER BY c.idv'
);
$select_cart_items->execute([$user_id]);
$items = $select_cart_items->fetchAll(PDO::FETCH_ASSOC);

$cart_grand_total = 0;
$unidades = 0;
foreach ($items as $it) {
    $cart_grand_total += $it['price'] * $it['quantity'];
    $unidades += (int)$it['quantity'];
}
$cart_grand_total = round($cart_grand_total, 2);
list($c_base, $c_igv) = igv_desglosar($cart_grand_total);

$seccion = 'ventas';
$migas = 'Ventas / Finalizar venta';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

<?php if ($modifica): ?>
                <?php include __DIR__ . '/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="cabecera-form">
                    <div>
                        <h1>Finalizar venta</h1>
                        <p>Indica el cliente, registra el pago y confirma</p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="cart.php" class="btn-secundario"><span class="las la-arrow-left"></span> Volver al carrito</a>
                        <button type="submit" form="form-venta" id="validate" name="order" class="btn-primario"<?php echo $items ? '' : ' disabled'; ?>>
                            <span class="las la-check-circle"></span> Confirmar venta
                        </button>
                    </div>
                </div>

<?php if (!$items): ?>
                <p class="alert">Tu carrito está vacío. <a href="nuevo.php">Agrega productos</a> para registrar una venta.</p>
<?php else: ?>
                <div class="checkout-grid">

                    <form action="" method="POST" autocomplete="off" id="form-venta" class="checkout-columna">
                        <?php echo csrf_campo(); ?>

                        <section class="tarjeta-form">
                            <h2><span class="las la-user"></span> Cliente</h2>

                            <div class="fila-campos">
                                <div>
                                    <label for="doctipo">Documento</label>
                                    <select name="doctipo" id="doctipo">
                                        <option value="">Sin documento (boleta hasta S/ 700)</option>
                                        <option value="DNI"<?php echo ($modifica['doc_tipo'] ?? '') === 'DNI' ? ' selected' : ''; ?>>DNI → Boleta</option>
                                        <option value="RUC"<?php echo ($modifica['doc_tipo'] ?? '') === 'RUC' ? ' selected' : ''; ?>>RUC → Factura</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="docnum">Número de documento</label>
                                    <input type="text" name="docnum" id="docnum" inputmode="numeric" maxlength="11" placeholder="Número de documento" value="<?php echo e($modifica['doc_num'] ?? ''); ?>" disabled>
                                </div>
                            </div>

                            <label for="nomcl">Nombre o razón social</label>
                            <input type="text" name="nomcl" id="nomcl" maxlength="150" value="<?php echo e($modifica['nomcl'] ?? ''); ?>" placeholder="Solo si el cliente es nuevo; si ya existe se usa el registrado">

                            <p class="nota" id="aviso-comprobante"></p>
                        </section>

                        <section class="tarjeta-form">
                            <h2><span class="las la-wallet"></span> Pagos <span class="req">*</span></h2>

                            <p class="nota sin-margen"><span class="las la-info-circle"></span> Puedes combinar medios (ej. parte en efectivo y parte con Yape). Yape, Plin y transferencia piden el número de operación.</p>
                            <div id="pagos"></div>
                            <button type="button" id="agregar-pago" class="btn-agregar-pago"><span class="las la-plus"></span> Agregar otro medio de pago</button>
                            <p id="estado-pagos" class="estado-pagos"></p>
                        </section>
                    </form>

                    <aside class="tarjeta-form resumen-venta">
                        <h2><span class="las la-receipt"></span> Resumen de la venta</h2>

                        <ul class="resumen-items">
<?php foreach ($items as $it): ?>
                            <li>
                                <div>
                                    <span class="resumen-nombre"><?php echo e($it['name']); ?></span>
                                    <small><?php echo (int)$it['quantity']; ?> × S/ <?php echo number_format($it['price'], 2); ?></small>
                                </div>
                                <b>S/ <?php echo number_format($it['price'] * $it['quantity'], 2); ?></b>
                            </li>
<?php endforeach; ?>
                        </ul>

                        <dl class="resumen-totales">
                            <div><dt>Productos</dt><dd><?php echo $unidades; ?> unid.</dd></div>
                            <div><dt>Op. gravada</dt><dd>S/ <?php echo number_format($c_base, 2); ?></dd></div>
                            <div><dt>IGV (<?php echo IGV_TASA * 100; ?>%)</dt><dd>S/ <?php echo number_format($c_igv, 2); ?></dd></div>
                            <div class="total"><dt>Total</dt><dd>S/ <?php echo number_format($cart_grand_total, 2); ?></dd></div>
                        </dl>

                        <p class="comprobante-emitido">Se emitirá: <b id="tipo-emitido">Boleta</b></p>
                    </aside>

                </div>
<?php endif; ?>

            </div>

<template id="fila-pago">
    <div class="fila-pago">
        <select name="pago_metodo[]" class="pago-metodo" required>
            <option value="">Medio de pago</option>
<?php foreach (METODOS_PAGO as $m): ?>
            <option value="<?php echo $m; ?>"><?php echo $m === 'Contado' ? 'Contado (efectivo)' : $m; ?></option>
<?php endforeach; ?>
        </select>
        <div class="campo-con-unidad pago-monto-caja">
            <span class="unidad">S/</span>
            <input type="number" name="pago_monto[]" class="pago-monto" step="0.01" min="0.01" placeholder="Monto" required>
        </div>
        <input type="text" name="pago_operacion[]" class="pago-operacion" maxlength="30" placeholder="N° de operación" hidden>
        <button type="button" class="quitar-pago" title="Quitar este pago"><span class="las la-times"></span></button>
    </div>
</template>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_check.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        var form = document.getElementById('form-venta');
        if (!form) { return; }
        var total = <?php echo json_encode($cart_grand_total); ?>;
        var limite = <?php echo json_encode(LIMITE_BOLETA_SIN_DNI); ?>;
        var conOperacion = <?php echo json_encode(METODOS_CON_OPERACION); ?>;

        // documento -> boleta o factura
        var tipo = document.getElementById('doctipo');
        var num = document.getElementById('docnum');
        var aviso = document.getElementById('aviso-comprobante');
        var emitido = document.getElementById('tipo-emitido');

        function actualizarDoc() {
            var t = tipo.value;
            num.disabled = (t === '');
            num.required = (t !== '');
            if (t === '') { num.value = ''; }
            num.maxLength = (t === 'RUC') ? 11 : 8;
            num.placeholder = (t === 'RUC') ? 'RUC (11 dígitos)' : (t === 'DNI' ? 'DNI (8 dígitos)' : 'Elige el tipo de documento');
            if (num.value.length > num.maxLength) { num.value = num.value.slice(0, num.maxLength); }
            emitido.textContent = (t === 'RUC') ? 'Factura' : 'Boleta';
            var falta = (t === '' && total > limite);
            aviso.className = 'nota' + (falta ? ' alerta' : '');
            aviso.innerHTML = falta
                ? '<span class="las la-exclamation-triangle"></span> El total supera S/ ' + limite.toFixed(2) + ': ingresa el DNI del cliente (o su RUC para factura).'
                : '<span class="las la-info-circle"></span> Con DNI o sin documento se emite boleta; con RUC, factura.';
        }
        num.addEventListener('input', function () { num.value = num.value.replace(/\D/g, ''); });
        tipo.addEventListener('change', actualizarDoc);
        actualizarDoc();

        // pagos mixtos
        var cont = document.getElementById('pagos');
        var plantilla = document.getElementById('fila-pago');
        var estado = document.getElementById('estado-pagos');

        function pagado() {
            var s = 0;
            cont.querySelectorAll('.pago-monto').forEach(function (i) { s += parseFloat(i.value) || 0; });
            return Math.round(s * 100) / 100;
        }

        function refrescar() {
            var falta = Math.round((total - pagado()) * 100) / 100;
            var filas = cont.querySelectorAll('.fila-pago');
            filas.forEach(function (f) { f.querySelector('.quitar-pago').hidden = (filas.length === 1); });
            if (falta === 0) {
                estado.textContent = 'Pagos completos: S/ ' + total.toFixed(2);
                estado.className = 'estado-pagos ok';
            } else if (falta > 0) {
                estado.textContent = 'Falta cubrir S/ ' + falta.toFixed(2);
                estado.className = 'estado-pagos falta';
            } else {
                estado.textContent = 'Los pagos superan el total en S/ ' + (-falta).toFixed(2);
                estado.className = 'estado-pagos falta';
            }
        }

        function agregarFila(monto) {
            var fila = plantilla.content.firstElementChild.cloneNode(true);
            var metodo = fila.querySelector('.pago-metodo');
            var op = fila.querySelector('.pago-operacion');
            fila.querySelector('.pago-monto').value = monto > 0 ? monto.toFixed(2) : '';
            metodo.addEventListener('change', function () {
                var pide = conOperacion.indexOf(metodo.value) !== -1;
                op.hidden = !pide;
                op.required = pide;
                if (!pide) { op.value = ''; }
            });
            fila.querySelector('.quitar-pago').addEventListener('click', function () { fila.remove(); refrescar(); });
            fila.querySelector('.pago-monto').addEventListener('input', refrescar);
            cont.appendChild(fila);
            refrescar();
        }

        document.getElementById('agregar-pago').addEventListener('click', function () {
            agregarFila(Math.max(0, Math.round((total - pagado()) * 100) / 100));
        });

        form.addEventListener('submit', function (ev) {
            if (Math.round((total - pagado()) * 100) !== 0) {
                ev.preventDefault();
                swal('Revisa los pagos', estado.textContent, 'warning');
            }
        });

        agregarFila(total);
    })();
    </script>
</body>
</html>
