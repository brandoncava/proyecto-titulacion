<?php
// formulario de categoria, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define $categoria: fila a editar o null si es nueva
if (!function_exists('csrf_campo')) {
    exit;
}

$esNuevo = empty($categoria);
$cabecera = [
    'titulo'    => $esNuevo ? 'Nueva categoría' : 'Editar categoría',
    'subtitulo' => $esNuevo ? 'Agrupa los productos del catálogo' : 'Cambia el nombre de la categoría',
    'form'      => 'form-categoria',
    'boton'     => $esNuevo ? 'add_category' : 'upd_category',
    'texto'     => $esNuevo ? 'Guardar categoría' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-categoria" action="" method="POST" autocomplete="off" class="tarjetas-form simple">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="cateid" value="<?php echo (int)$categoria->idcate; ?>">
<?php endif; ?>
                    <section class="tarjeta-form">
                        <h2><span class="las la-paperclip"></span> Datos de la categoría</h2>
                        <label for="catnom">Nombre <span class="req">*</span></label>
                        <input type="text" id="catnom" name="catnom" value="<?php echo e($categoria->nocate ?? ''); ?>" maxlength="100" placeholder="ej. Impresoras" required autofocus>
                    </section>
                </form>
