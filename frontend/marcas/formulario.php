<?php
// formulario de marca, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define $marca: fila a editar o null si es nueva
if (!function_exists('csrf_campo')) {
    exit;
}

$esNuevo = empty($marca);
$cabecera = [
    'titulo'    => $esNuevo ? 'Nueva marca' : 'Editar marca',
    'subtitulo' => $esNuevo ? 'Fabricante de los productos que vendes' : 'Cambia el nombre de la marca',
    'form'      => 'form-marca',
    'boton'     => $esNuevo ? 'ins_marca' : 'upd_marca',
    'texto'     => $esNuevo ? 'Guardar marca' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-marca" action="" method="POST" autocomplete="off" class="tarjetas-form simple">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="idmar" value="<?php echo (int)$marca->idmar; ?>">
<?php endif; ?>
                    <section class="tarjeta-form">
                        <h2><span class="las la-tags"></span> Datos de la marca</h2>
                        <label for="nommar">Nombre <span class="req">*</span></label>
                        <input type="text" id="nommar" name="nommar" value="<?php echo e($marca->nomarc ?? ''); ?>" maxlength="100" placeholder="ej. Epson" required autofocus>
                    </section>
                </form>
