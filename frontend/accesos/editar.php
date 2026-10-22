<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('usuarios');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$b = $connect->prepare('SELECT id, nombre, username, correo FROM usuarios WHERE id = ?');
$b->execute([$id]);
$usuario = $b->fetch();

$seccion = 'accesos';
$migas = 'Accesos / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$usuario): ?>
                <p class="alert">No se encontró el usuario.</p>
<?php else: ?>
<?php include __DIR__ . '/formulario.php'; ?>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_acceso.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
</body>
</html>
