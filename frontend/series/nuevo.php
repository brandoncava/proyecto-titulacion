<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('series');

$seccion = 'series';
$migas = 'Series / Nueva';
require __DIR__ . '/../layout/cabecera.php';

$cabecera = [
    'titulo'    => 'Nueva serie',
    'subtitulo' => 'Numeración de boletas o facturas',
    'form'      => 'form-serie',
    'boton'     => 'ins_serie',
    'texto'     => 'Guardar serie',
];
?>

            <div class="page-content">
<?php include __DIR__ . '/../layout/cabecera_form.php'; ?>

                <form id="form-serie" action="" method="POST" autocomplete="off" class="tarjetas-form simple">
                    <?php echo csrf_campo(); ?>
                    <section class="tarjeta-form">
                        <h2><span class="las la-file-invoice"></span> Datos de la serie</h2>
                        <div class="fila-campos">
                            <div>
                                <label for="tipc">Comprobante <span class="req">*</span></label>
                                <select id="tipc" name="tipc" required>
                                    <option value="">Seleccione</option>
<?php foreach (COMPROBANTES as $comp): ?>
                                    <option value="<?php echo e($comp); ?>"><?php echo e($comp); ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="serie">Serie <span class="req">*</span></label>
                                <input type="text" id="serie" name="serie" maxlength="4" class="mayusculas" placeholder="ej. B002 o F002" required>
                            </div>
                        </div>
                        <p class="nota"><span class="las la-info-circle"></span> 4 caracteres: las de boleta empiezan con B y las de factura con F. Si ya hay una serie en uso, la nueva queda inactiva hasta que la actives.</p>
                    </section>
                </form>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_serie.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
