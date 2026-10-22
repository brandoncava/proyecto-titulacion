<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('usuarios');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare('SELECT id, nombre, username FROM usuarios WHERE id = ?');
$b->execute([$id]);
$usuario = $b->fetch();

$seccion = 'accesos';
$migas = 'Accesos / Contraseña';
require __DIR__ . '/../layout/cabecera.php';

$cabecera = [
    'titulo'    => 'Cambiar contraseña',
    'subtitulo' => $usuario ? 'Nueva contraseña para ' . $usuario->nombre . ' (' . $usuario->username . ')' : '',
    'form'      => 'form-clave',
    'boton'     => 'upd_acceso_pwd',
    'texto'     => 'Guardar contraseña',
];
?>

            <div class="page-content">
<?php if (!$usuario): ?>
                <p class="alert">No se encontró el usuario.</p>
<?php else: ?>
<?php include __DIR__ . '/../layout/cabecera_form.php'; ?>

                <form id="form-clave" action="" method="POST" autocomplete="off" class="tarjetas-form simple">
                    <?php echo csrf_campo(); ?>
                    <input type="hidden" name="useid" value="<?php echo (int)$usuario->id; ?>">
                    <section class="tarjeta-form">
                        <h2><span class="las la-key"></span> Nueva contraseña</h2>
                        <label for="pswuse">Contraseña <span class="req">*</span></label>
                        <input type="password" id="pswuse" name="pswuse" minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres" required autofocus>
                        <label class="casilla">
                            <input type="checkbox" onclick="document.getElementById('pswuse').type = this.checked ? 'text' : 'password';">
                            Mostrar contraseña
                        </label>
                        <p class="nota"><span class="las la-info-circle"></span> El usuario la usará desde su próximo ingreso.</p>
                    </section>
                </form>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_acceso_pwd.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
