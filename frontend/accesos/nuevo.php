<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('usuarios');

$usuario = null;

$seccion = 'accesos';
$migas = 'Accesos / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_acceso.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script>
    // al cambiar el perfil se pone su contraseña predeterminada
    (function () {
        var rol = document.getElementById('rol');
        var password = document.getElementById('password');
        var porRol = <?php echo json_encode($PASSWORDS_POR_ROL, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
        rol.addEventListener('change', function () {
            if (Object.prototype.hasOwnProperty.call(porRol, rol.value)) {
                password.value = porRol[rol.value];
            }
        });
    })();
    </script>
</body>
</html>
