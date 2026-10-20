<?php
// cabecera de los formularios en tarjetas: titulo, subtitulo y botones Cancelar / Guardar.
// La pagina define antes de incluirlo:
//   $cabecera = [
//       'titulo'    => 'Nuevo cliente',
//       'subtitulo' => 'Registra a una persona...',
//       'form'      => 'form-cliente',      id del <form> que envia el boton
//       'boton'     => 'add_customer',      name del boton (el manejador lo busca en $_POST)
//       'texto'     => 'Guardar cliente',
//       'volver'    => 'mostrar.php',       opcional
//   ];
if (!isset($cabecera)) {
    exit;
}
?>
                <div class="cabecera-form">
                    <div>
                        <h1><?php echo e($cabecera['titulo']); ?></h1>
<?php if (!empty($cabecera['subtitulo'])): ?>
                        <p><?php echo e($cabecera['subtitulo']); ?></p>
<?php endif; ?>
                    </div>
                    <div class="cabecera-form-botones">
                        <a href="<?php echo e($cabecera['volver'] ?? 'mostrar.php'); ?>" class="btn-secundario">Cancelar</a>
                        <button type="submit" form="<?php echo e($cabecera['form']); ?>" name="<?php echo e($cabecera['boton']); ?>" class="btn-primario">
                            <span class="las la-save"></span> <?php echo e($cabecera['texto']); ?>
                        </button>
                    </div>
                </div>
