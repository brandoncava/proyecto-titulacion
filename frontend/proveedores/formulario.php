<?php
// formulario de proveedor, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define $proveedor: fila a editar o null si es nuevo.
// Los name de los campos son distintos en alta y edicion (los leen add_supplier y upd_supplier)
if (!function_exists('csrf_campo')) {
    exit;
}

$esNuevo = empty($proveedor);
$p = $proveedor ?: (object)['idprov' => 0, 'rucprv' => '', 'nomprv' => '', 'corrprv' => ''];
$cabecera = [
    'titulo'    => $esNuevo ? 'Nuevo proveedor' : 'Editar proveedor',
    'subtitulo' => $esNuevo ? 'Empresa a la que le compras mercadería' : 'Actualiza los datos del proveedor',
    'form'      => 'form-proveedor',
    'boton'     => $esNuevo ? 'add_supplier' : 'upd_supplier',
    'texto'     => $esNuevo ? 'Guardar proveedor' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-proveedor" action="" method="POST" autocomplete="off" class="tarjetas-form">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="suppid" value="<?php echo (int)$p->idprov; ?>">
<?php endif; ?>
                    <section class="tarjeta-form">
                        <h2><span class="las la-id-card"></span> Empresa</h2>
                        <label for="rcprv">RUC <span class="req">*</span></label>
                        <input type="text" id="rcprv" name="rcprv" value="<?php echo e($p->rucprv); ?>" inputmode="numeric" maxlength="11" placeholder="11 dígitos" required>
                        <label for="nomprv">Razón social <span class="req">*</span></label>
                        <input type="text" id="nomprv" name="<?php echo $esNuevo ? 'nomprv' : 'noprv'; ?>" value="<?php echo e($p->nomprv); ?>" maxlength="150" placeholder="ej. Distribuidora Tecnológica S.A.C." required>
                        <p class="nota"><span class="las la-info-circle"></span> El RUC se valida con su dígito verificador.</p>
                    </section>

                    <section class="tarjeta-form">
                        <h2><span class="las la-envelope"></span> Contacto</h2>
                        <label for="corrprv">Correo electrónico</label>
                        <input type="email" id="corrprv" name="<?php echo $esNuevo ? 'corrprv' : 'corprv'; ?>" value="<?php echo e($p->corrprv); ?>" maxlength="35" placeholder="ej. ventas@proveedor.pe (opcional)">
                        <p class="nota"><span class="las la-info-circle"></span> Para enviarle pedidos o consultas de garantía.</p>
                    </section>
                </form>
