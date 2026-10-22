<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('compras');

$user_id = (int)$_SESSION['id'];
$modifica = $_SESSION['modifica_compra'] ?? null;

// el costo del carrito va sin igv (el de la factura del proveedor)
$select_cart_items = $connect->prepare('SELECT name, price, quantity FROM cart_purchase WHERE user_id = ? ORDER BY idcpr');
$select_cart_items->execute([$user_id]);
$items = $select_cart_items->fetchAll(PDO::FETCH_ASSOC);

$cart_grand_total = 0;
$unidades = 0;
foreach ($items as $it) {
    $cart_grand_total += $it['price'] * $it['quantity'];
    $unidades += (int)$it['quantity'];
}
list($c_base, $c_igv, $c_total) = igv_agregar($cart_grand_total);

$proveedores = $connect->query('SELECT idprov, rucprv, nomprv FROM proveedores WHERE state = 1 ORDER BY nomprv')->fetchAll();
$provElegido = (int)($modifica['idprov'] ?? 0);
$conIgv = !$modifica || $modifica['con_igv'];

$seccion = 'compras';
$migas = 'Compras / Finalizar compra';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

<?php if ($modifica): ?>
                <?php $tipoCarrito = 'compra'; include __DIR__ . '/../comun/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="cabecera-form">
                    <div>
                        <h1>Finalizar compra</h1>
                        <p>Registra el comprobante del proveedor y confirma el ingreso de mercadería</p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="cart.php" class="btn-secundario"><span class="las la-arrow-left"></span> Volver al carrito</a>
                        <button type="submit" form="form-compra" id="validate" name="order" class="btn-primario"<?php echo $items ? '' : ' disabled'; ?>>
                            <span class="las la-check-circle"></span> Confirmar compra
                        </button>
                    </div>
                </div>

<?php if (!$items): ?>
                <p class="alert">Tu carrito está vacío. <a href="nuevo.php">Agrega productos</a> para registrar una compra.</p>
<?php else: ?>
                <div class="checkout-grid">

                    <form action="" method="POST" autocomplete="off" id="form-compra" class="checkout-columna">
                        <?php echo csrf_campo(); ?>

                        <section class="tarjeta-form">
                            <h2><span class="las la-truck"></span> Proveedor</h2>
                            <label for="provee">Proveedor <span class="req">*</span></label>
                            <select name="cxtprov" id="provee" required>
                                <option value="">Seleccione</option>
<?php foreach ($proveedores as $pv): ?>
                                <option value="<?php echo (int)$pv->idprov; ?>"<?php echo (int)$pv->idprov === $provElegido ? ' selected' : ''; ?>><?php echo e($pv->nomprv . ' · RUC ' . $pv->rucprv); ?></option>
<?php endforeach; ?>
                            </select>
                            <label for="cxtcre">Método de pago <span class="req">*</span></label>
                            <select name="cxtcre" id="cxtcre" required>
                                <option value="">Seleccione</option>
<?php foreach (['Contado', 'Tarjeta', 'Transferencia'] as $m): ?>
                                <option value="<?php echo $m; ?>"<?php echo ($modifica['method'] ?? '') === $m ? ' selected' : ''; ?>><?php echo $m; ?></option>
<?php endforeach; ?>
                            </select>
                        </section>

                        <section class="tarjeta-form">
                            <h2><span class="las la-file-invoice"></span> Comprobante del proveedor</h2>
                            <div class="fila-campos">
                                <div>
                                    <label for="cxcom">Tipo <span class="req">*</span></label>
                                    <select name="cxcom" id="cxcom" required>
                                        <option value="">Seleccione</option>
<?php foreach (COMPROBANTES_COMPRA as $tipo): ?>
                                        <option value="<?php echo $tipo; ?>"<?php echo ($modifica['tipc'] ?? '') === $tipo ? ' selected' : ''; ?>><?php echo $tipo; ?></option>
<?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label for="cxigv">IGV <span class="req">*</span></label>
                                    <select name="cxigv" id="cxigv" required>
                                        <option value="1">Incluye IGV</option>
                                        <option value="0"<?php echo $conIgv ? '' : ' selected'; ?>>No incluye IGV</option>
                                    </select>
                                </div>
                            </div>
                            <div class="fila-campos">
                                <div>
                                    <label for="serie_doc">Serie <span class="req">*</span></label>
                                    <input type="text" name="serie_doc" id="serie_doc" maxlength="4" class="mayusculas" placeholder="ej. F001" value="<?php echo e($modifica['serie_doc'] ?? ''); ?>" required>
                                </div>
                                <div>
                                    <label for="numero_doc">Número <span class="req">*</span></label>
                                    <input type="text" name="numero_doc" id="numero_doc" inputmode="numeric" maxlength="8" placeholder="ej. 00001234" value="<?php echo e($modifica['numero_doc'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <label for="fecha_emision">Fecha de emisión <span class="req">*</span></label>
                            <input type="date" name="fecha_emision" id="fecha_emision" max="<?php echo date('Y-m-d'); ?>" value="<?php echo e($modifica['fecha_emision'] ?? ''); ?>" required>
                            <p class="nota"><span class="las la-info-circle"></span> El mismo comprobante del proveedor no se puede registrar dos veces.</p>
                        </section>
                    </form>

                    <aside class="tarjeta-form resumen-venta">
                        <h2><span class="las la-receipt"></span> Resumen de la compra</h2>

                        <ul class="resumen-items">
<?php foreach ($items as $it): ?>
                            <li>
                                <div>
                                    <span class="resumen-nombre"><?php echo e($it['name']); ?></span>
                                    <small><?php echo (int)$it['quantity']; ?> × S/ <?php echo number_format($it['price'], 2); ?> sin IGV</small>
                                </div>
                                <b>S/ <?php echo number_format($it['price'] * $it['quantity'], 2); ?></b>
                            </li>
<?php endforeach; ?>
                        </ul>

                        <dl class="resumen-totales">
                            <div><dt>Productos</dt><dd><?php echo $unidades; ?> unid.</dd></div>
                            <div><dt>Subtotal (sin IGV)</dt><dd>S/ <?php echo number_format($c_base, 2); ?></dd></div>
                            <div><dt>IGV (<?php echo IGV_TASA * 100; ?>%)</dt><dd id="igv-monto"></dd></div>
                            <div class="total"><dt>Total</dt><dd id="total-monto"></dd></div>
                        </dl>
                    </aside>

                </div>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_check_purchase.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/solo_digitos.js"></script>
<?php if ($items): ?>
    <script type="text/javascript">
    // el total cambia segun el comprobante del proveedor traiga igv o no
    (function () {
        var igv = document.getElementById('cxigv');
        var montoIgv = document.getElementById('igv-monto');
        var montoTotal = document.getElementById('total-monto');
        var con = <?php echo json_encode(['S/ ' . number_format($c_igv, 2), 'S/ ' . number_format($c_total, 2)]); ?>;
        var sin = <?php echo json_encode(['S/ 0.00', 'S/ ' . number_format($c_base, 2)]); ?>;
        function pintar() {
            var m = igv.value === '1' ? con : sin;
            montoIgv.textContent = m[0];
            montoTotal.textContent = m[1];
        }
        igv.addEventListener('change', pintar);
        pintar();
    })();
    </script>
<?php endif; ?>
</body>
</html>
