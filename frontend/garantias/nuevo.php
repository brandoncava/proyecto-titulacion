<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('garantias');

$q      = trim($_GET['q'] ?? '');
$idord  = filter_input(INPUT_GET, 'idord', FILTER_VALIDATE_INT) ?: 0;
$manual = isset($_GET['manual']);
$hoy    = date('Y-m-d');

// fecha de vencimiento: compra + meses de garantia del producto
function vence_garantia($fechaCompra, $meses)
{
    return date('Y-m-d', strtotime($fechaCompra . ' +' . (int)$meses . ' months'));
}

// 1) busqueda de la venta original
$resultados = [];
if ($q !== '' && !$idord && !$manual) {
    $sql = "SELECT idord, serie, numero, tipc, placed_on, nomcl, doc_num, total_price
              FROM orders
             WHERE anulada = 0 AND (nomcl LIKE ? OR doc_num = ?";
    $params = ['%' . $q . '%', $q];
    // B001-5 o B001-00000005
    if (preg_match('/^([A-Za-z0-9]{4})-?(\d{1,8})$/', $q, $m)) {
        $sql .= ' OR (serie = ? AND numero = ?)';
        $params[] = strtoupper($m[1]);
        $params[] = (int)$m[2];
    }
    $sql .= ') ORDER BY idord DESC LIMIT 20';
    $buscar = $connect->prepare($sql);
    $buscar->execute($params);
    $resultados = $buscar->fetchAll();
}

// 2) venta elegida: sus productos con la vigencia de cada uno
$venta = null;
$lineas = [];
if ($idord) {
    $b = $connect->prepare(
        'SELECT o.idord, o.serie, o.numero, o.tipc, o.placed_on, o.nomcl, o.doc_num, o.idcli, c.telfcl
           FROM orders o LEFT JOIN clientes c ON c.idcli = o.idcli
          WHERE o.idord = ? AND o.anulada = 0'
    );
    $b->execute([$idord]);
    $venta = $b->fetch();

    if ($venta) {
        $l = $connect->prepare(
            'SELECT d.idprod, d.nombre, d.cantidad, p.garantia_meses
               FROM orders_detalle d INNER JOIN productos p ON p.idprod = d.idprod
              WHERE d.idord = ?'
        );
        $l->execute([$idord]);
        $lineas = $l->fetchAll();
        foreach ($lineas as $x) {
            $x->vence = vence_garantia(substr($venta->placed_on, 0, 10), $x->garantia_meses);
            $x->vigente = $x->vence >= $hoy;
        }
    }
}

// 3) venta fuera del sistema: se elige el producto y la fecha a mano
$productos = [];
if ($manual || ($venta && !$lineas)) {
    $productos = $connect->query('SELECT idprod, nomprd, garantia_meses FROM productos WHERE state = 1 ORDER BY nomprd')->fetchAll();
}

$seccion = 'garantias';
$migas = 'Garantías / Nueva orden';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">

<?php if (!$venta && !$manual): ?>
                <div class="cabecera-form">
                    <div>
                        <h1>Nueva orden de garantía</h1>
                        <p>Busca la venta original para traer el cliente, el producto y la fecha de compra</p>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="mostrar.php" class="btn-secundario">Cancelar</a>
                        <a href="?manual=1" class="btn-secundario">La venta no está en el sistema</a>
                    </div>
                </div>

                <form class="tarjeta-form buscar-venta" method="GET" action="">
                    <label for="q">Buscar la venta original</label>
                    <div class="buscador-cliente">
                        <span class="las la-search"></span>
                        <input type="text" id="q" name="q" value="<?php echo e($q); ?>" placeholder="Comprobante (ej. B001-5), DNI/RUC o nombre del cliente" autofocus>
                    </div>
                    <button type="submit" class="btn-primario">Buscar</button>
                </form>

<?php if ($q !== ''): ?>
                <div class="records table-responsive">
                    <div class="record-header"><h3>Ventas encontradas</h3></div>
<?php if ($resultados): ?>
                    <table width="100%">
                        <thead><tr><th>Comprobante</th><th>Fecha</th><th>Cliente</th><th>Total</th><th></th></tr></thead>
                        <tbody>
<?php foreach ($resultados as $r): ?>
                            <tr>
                                <td><?php echo e($r->tipc . ' ' . formato_comprobante($r->serie, $r->numero)); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($r->placed_on)); ?></td>
                                <td><?php echo e($r->nomcl); ?> <small><?php echo e($r->doc_num); ?></small></td>
                                <td>S/<?php echo number_format($r->total_price, 2); ?></td>
                                <td><a class="btn-secundario btn-chico" href="?idord=<?php echo (int)$r->idord; ?>">Elegir</a></td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
<?php else: ?>
                    <p class="sin-datos">No se encontraron ventas válidas. Revisa el dato o registra la orden con "La venta no está en el sistema".</p>
<?php endif; ?>
                </div>
<?php endif; ?>

<?php else: ?>
<?php
$cabecera = [
    'titulo'    => 'Orden de recepción por garantía',
    'subtitulo' => $venta ? 'Venta original: ' . $venta->tipc . ' ' . formato_comprobante($venta->serie, $venta->numero) . ' del ' . date('d/m/Y', strtotime($venta->placed_on))
                          : 'Producto vendido fuera del sistema',
    'form'      => 'form-garantia',
    'boton'     => 'ins_garantia',
    'texto'     => 'Registrar orden',
    'volver'    => 'nuevo.php',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form action="" method="POST" autocomplete="off" id="form-garantia" class="tarjetas-form">
                    <?php echo csrf_campo(); ?>
<?php if ($venta): ?>
                    <input type="hidden" name="idord" value="<?php echo (int)$venta->idord; ?>">
<?php endif; ?>

                    <section class="tarjeta-form">
                        <h2><span class="las la-box"></span> Producto</h2>
<?php if ($lineas): ?>
                        <span class="etiqueta-campo">Producto que falla <span class="req">*</span></span>
                        <div class="lista-productos-gar">
<?php foreach ($lineas as $i => $x): ?>
                            <label class="opcion-producto">
                                <input type="radio" name="idprod" value="<?php echo (int)$x->idprod; ?>" data-vigente="<?php echo $x->vigente ? 1 : 0; ?>" <?php echo $i === 0 ? 'checked' : ''; ?> required>
                                <span><?php echo e($x->nombre); ?></span>
                                <span class="chip <?php echo $x->vigente ? 'ok' : 'peligro'; ?>">
                                    <?php echo $x->vigente ? 'Vigente' : 'Vencida'; ?> hasta <?php echo date('d/m/Y', strtotime($x->vence)); ?>
                                </span>
                            </label>
<?php endforeach; ?>
                        </div>
<?php else: ?>
                        <label for="idprod">Producto que falla <span class="req">*</span></label>
                        <select name="idprod" id="idprod" required>
                            <option value="">Seleccione</option>
<?php foreach ($productos as $p): ?>
                            <option value="<?php echo (int)$p->idprod; ?>" data-meses="<?php echo (int)$p->garantia_meses; ?>"><?php echo e($p->nomprd); ?> (<?php echo (int)$p->garantia_meses; ?> meses)</option>
<?php endforeach; ?>
                        </select>
<?php if (!$venta): ?>
                        <div class="fila-campos">
                            <div>
                                <label for="fecha_compra">Fecha de compra <span class="req">*</span></label>
                                <input type="date" name="fecha_compra" id="fecha_compra" max="<?php echo $hoy; ?>" required>
                            </div>
                            <div>
                                <label for="comprobante">Comprobante</label>
                                <input type="text" name="comprobante" id="comprobante" maxlength="20" class="mayusculas" placeholder="ej. B001-00000123">
                            </div>
                        </div>
<?php endif; ?>
                        <p id="vigencia-manual" class="estado-pagos"></p>
<?php endif; ?>
                        <label for="numero_serie">N° de serie del equipo</label>
                        <input type="text" name="numero_serie" id="numero_serie" maxlength="60" placeholder="Lo indica la etiqueta del equipo">
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-user"></span> Cliente</h2>
                        <label for="cliente_nombre">Nombre o razón social <span class="req">*</span></label>
                        <input type="text" name="cliente_nombre" id="cliente_nombre" maxlength="150" required value="<?php echo e($venta && $venta->nomcl !== 'CLIENTES VARIOS' ? $venta->nomcl : ''); ?>" placeholder="ej. Rosa María Medina Paz">
                        <div class="fila-campos">
                            <div>
                                <label for="cliente_doc">DNI o RUC</label>
                                <input type="text" name="cliente_doc" id="cliente_doc" maxlength="11" inputmode="numeric" value="<?php echo e($venta->doc_num ?? ''); ?>" placeholder="8 u 11 dígitos">
                            </div>
                            <div>
                                <label for="cliente_tel">Teléfono</label>
                                <input type="text" name="cliente_tel" id="cliente_tel" maxlength="15" inputmode="tel" value="<?php echo e($venta->telfcl ?? ''); ?>" placeholder="Para avisarle">
                            </div>
                        </div>
                    </section>

                    <section class="tarjeta-form ancho-completo">
                        <h2><span class="las la-clipboard-check"></span> Recepción del equipo</h2>
                        <div class="fila-campos">
                            <div>
                                <label for="estado_fisico">Estado físico <span class="req">*</span></label>
                                <input type="text" name="estado_fisico" id="estado_fisico" maxlength="255" required placeholder="ej. rayones leves en la tapa, caja completa">
                            </div>
                            <div>
                                <label for="accesorios">Accesorios que deja</label>
                                <input type="text" name="accesorios" id="accesorios" maxlength="255" placeholder="ej. cable de poder, cable USB">
                            </div>
                        </div>
                        <label for="falla">Falla reportada por el cliente <span class="req">*</span></label>
                        <textarea name="falla" id="falla" required maxlength="2000" placeholder="Describe la falla con las palabras del cliente"></textarea>

                        <label class="check-fuera" id="check-fuera" hidden>
                            <input type="checkbox" name="fuera_garantia" value="1">
                            La garantía está vencida: registrar la orden de todas formas (la marca podría rechazarla o cobrar la reparación)
                        </label>
                    </section>
                </form>
<?php endif; ?>

            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_garantia.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        var form = document.getElementById('form-garantia');
        if (!form) { return; }
        var check = document.getElementById('check-fuera');
        var aviso = document.getElementById('vigencia-manual');
        var hoy = <?php echo json_encode($hoy); ?>;

        function vigenteSeleccion() {
            var radio = form.querySelector('input[name=idprod]:checked');
            if (radio) { return radio.dataset.vigente === '1'; }
            var sel = document.getElementById('idprod');
            var fecha = document.getElementById('fecha_compra');
            var opcion = sel && sel.selectedOptions[0];
            var compra = fecha ? fecha.value : <?php echo json_encode($venta ? substr($venta->placed_on, 0, 10) : ''); ?>;
            if (!opcion || !opcion.dataset.meses || !compra) { aviso.textContent = ''; return true; }
            var d = new Date(compra + 'T00:00:00');
            d.setMonth(d.getMonth() + parseInt(opcion.dataset.meses, 10));
            var vence = d.toISOString().slice(0, 10);
            var ok = vence >= hoy;
            aviso.textContent = (ok ? 'Garantía vigente hasta ' : 'Garantía vencida el ') + vence.split('-').reverse().join('/');
            aviso.className = 'estado-pagos ' + (ok ? 'ok' : 'falta');
            return ok;
        }

        function refrescar() {
            var vencida = !vigenteSeleccion();
            check.hidden = !vencida;
            check.querySelector('input').required = vencida;
        }
        form.addEventListener('change', refrescar);
        refrescar();
    })();
    </script>
</body>
</html>
