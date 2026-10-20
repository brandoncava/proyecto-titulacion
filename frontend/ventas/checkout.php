<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas / Finalizar pago';
require __DIR__ . '/../layout/cabecera.php';

require_once('../../backend/config/Conexion.php');
$user_id = $_SESSION['id'];
$modifica = $_SESSION['modifica_venta'] ?? null;

// se cobra el precio guardado en el carrito
$select_cart_items = $connect->prepare(
    'SELECT c.name, c.price, c.quantity FROM cart c WHERE c.user_id = ?'
);
$select_cart_items->execute([$user_id]);
$items = $select_cart_items->fetchAll(PDO::FETCH_ASSOC);

$cart_grand_total = 0;
foreach ($items as $it) {
    $cart_grand_total += $it['price'] * $it['quantity'];
}
$cart_grand_total = round($cart_grand_total, 2);
list($c_base, $c_igv) = igv_desglosar($cart_grand_total);
?>

            <div class="page-content">

<?php if ($modifica): ?>
                <?php include __DIR__ . '/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="input-block">
<?php if ($items): ?>
<?php foreach ($items as $it): ?>
   <p> <?php echo e($it['name']); ?> <span>(S/<?php echo number_format($it['price'], 2); ?> x <?php echo (int)$it['quantity']; ?>)</span> </p>
<?php endforeach; ?>
<?php else: ?>
   <p class="alert alert-warning">Tu carrito esta vació</p>
<?php endif; ?>
   <div class="grand-total">Op. gravada : <span>S/<?php echo number_format($c_base, 2); ?></span></div>
   <div class="grand-total">IGV (<?php echo IGV_TASA * 100; ?>%) : <span>S/<?php echo number_format($c_igv, 2); ?></span></div>
   <div class="grand-total">Total (incluye IGV) : <span>S/<?php echo number_format($cart_grand_total, 2); ?></span></div>
 </div>

<form action="" method="POST" autocomplete="off" id="form-venta">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Finalizar venta</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>

    <label for="doctipo"><b>Documento del cliente</b></label>
    <select name="doctipo" id="doctipo">
        <option value="">Sin documento (solo boleta de hasta S/ 700)</option>
        <option value="DNI"<?php echo ($modifica['doc_tipo'] ?? '') === 'DNI' ? ' selected' : ''; ?>>DNI &rarr; Boleta</option>
        <option value="RUC"<?php echo ($modifica['doc_tipo'] ?? '') === 'RUC' ? ' selected' : ''; ?>>RUC &rarr; Factura</option>
    </select>

    <input type="text" name="docnum" id="docnum" inputmode="numeric" maxlength="11" placeholder="Número de documento" value="<?php echo e($modifica['doc_num'] ?? ''); ?>" disabled>

    <label for="nomcl"><b>Nombre o razón social del cliente</b></label>
    <input type="text" name="nomcl" id="nomcl" maxlength="150" value="<?php echo e($modifica['nomcl'] ?? ''); ?>" placeholder="Solo si el cliente es nuevo; si ya existe se usa el registrado">

    <p id="aviso-comprobante" style="margin:10px 0;font-size:15px;">Se emitirá: <b id="tipo-emitido">Boleta</b></p>

    <label><b>Pagos</b></label><span class="badge-warning">*</span>
    <p class="ayuda-pagos">Puedes combinar medios (ej. parte en efectivo y parte con Yape). Yape, Plin y transferencia piden el número de operación.</p>
    <div id="pagos"></div>
    <button type="button" id="agregar-pago" class="btn-agregar-pago"><span class="las la-plus"></span> Agregar otro medio de pago</button>
    <p id="estado-pagos" class="estado-pagos"></p>

    <hr>

    <button type="submit" id="validate" name="order" class="registerbtn <?= ($cart_grand_total > 1)?'':'disabled'; ?>">Guardar</button>
    <button type="button" onclick="location.href='cart.php'" class="pabtn ">Cancelar</button>
  </div>

</form>

            </div>

<template id="fila-pago">
    <div class="fila-pago">
        <select name="pago_metodo[]" class="pago-metodo" required>
            <option value="">Medio de pago</option>
<?php foreach (METODOS_PAGO as $m): ?>
            <option value="<?php echo $m; ?>"><?php echo $m === 'Contado' ? 'Contado (efectivo)' : $m; ?></option>
<?php endforeach; ?>
        </select>
        <input type="number" name="pago_monto[]" class="pago-monto" step="0.01" min="0.01" placeholder="Monto S/" required>
        <input type="text" name="pago_operacion[]" class="pago-operacion" maxlength="30" placeholder="N° de operación" hidden>
        <button type="button" class="quitar-pago" title="Quitar este pago"><span class="las la-times"></span></button>
    </div>
</template>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_check.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        var total = <?php echo json_encode($cart_grand_total); ?>;
        var limite = <?php echo json_encode(LIMITE_BOLETA_SIN_DNI); ?>;
        var conOperacion = <?php echo json_encode(METODOS_CON_OPERACION); ?>;

        // documento -> boleta o factura
        var tipo = document.getElementById('doctipo');
        var num = document.getElementById('docnum');
        var aviso = document.getElementById('aviso-comprobante');

        function actualizarDoc() {
            var t = tipo.value;
            num.disabled = (t === '');
            num.required = (t !== '');
            if (t === '') { num.value = ''; }
            num.maxLength = (t === 'RUC') ? 11 : 8;
            num.placeholder = (t === 'RUC') ? 'RUC (11 dígitos)' : (t === 'DNI' ? 'DNI (8 dígitos)' : 'Número de documento');
            if (num.value.length > num.maxLength) { num.value = num.value.slice(0, num.maxLength); }
            var falta = (t === '' && total > limite);
            aviso.style.color = falta ? '#B42318' : '';
            aviso.innerHTML = falta
                ? 'El total supera S/ ' + limite.toFixed(2) + ': ingresa el <b>DNI</b> del cliente (o su <b>RUC</b> para factura).'
                : 'Se emitirá: <b>' + (t === 'RUC' ? 'Factura' : 'Boleta') + '</b>';
        }
        num.addEventListener('input', function () { num.value = num.value.replace(/\D/g, ''); });
        tipo.addEventListener('change', actualizarDoc);
        actualizarDoc();

        // pagos mixtos
        var cont = document.getElementById('pagos');
        var plantilla = document.getElementById('fila-pago');
        var estado = document.getElementById('estado-pagos');
        var guardar = document.getElementById('validate');

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

        document.getElementById('form-venta').addEventListener('submit', function (ev) {
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
