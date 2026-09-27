<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('usuarios');

$seccion = 'accesos';
$migas = 'Accesos / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
<form action="" method="POST" autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nuevo usuario</h1>

    <div class="alert-danger">
      <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
      <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
    </div>

    <hr>
    <br>

    <label for="nombre"><b>Nombre completo</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="100" placeholder="ejm: Juan Pérez" name="nombre" id="nombre" required>

    <label for="username"><b>Nombre de usuario</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="50" placeholder="ejm: jperez" name="username" id="username" required>

    <label for="correo"><b>Correo electrónico</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="100" placeholder="ejm: usuario@gmail.com" name="correo" id="correo" required>

    <label for="rol"><b>Perfil</b></label><span class="badge-warning">*</span>
    <select name="rol" id="rol" required>
        <?php foreach ($ROLES as $valor => $etiqueta): ?>
            <option value="<?php echo (int)$valor; ?>"><?php echo e($etiqueta); ?></option>
        <?php endforeach; ?>
    </select>

    <label for="password"><b>Contraseña</b></label><span class="badge-warning">*</span>
    <input type="text" minlength="6" placeholder="Contraseña del usuario" name="password" id="password"
           value="<?php echo e(password_predeterminada(ROL_EMPLEADO)); ?>" required>

    <small style="display:block;margin-top:-10px;margin-bottom:20px;color:#666;">
        Se coloca automáticamente la contraseña predeterminada del perfil. Puedes modificarla antes de crear el usuario.
    </small>

    <label for="state"><b>Estado</b></label><span class="badge-warning">*</span>
    <select name="state" id="state" required>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
    </select>

    <hr>

    <button type="submit" name="crear_usuario" class="registerbtn">Guardar</button>
  </div>
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/ins_acceso.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const rol = document.getElementById('rol');
        const password = document.getElementById('password');

        const contrasenasPorRol = <?php echo json_encode($PASSWORDS_POR_ROL, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        rol.addEventListener('change', function () {
            if (Object.prototype.hasOwnProperty.call(contrasenasPorRol, this.value)) {
                password.value = contrasenasPorRol[this.value];
            }
        });
    });
    </script>
</body>
</html>
