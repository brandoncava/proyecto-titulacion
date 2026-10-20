<?php
// formulario de usuario, comun a nuevo.php y editar.php.
// Antes de incluirlo la pagina define $usuario: fila a editar o null si es nuevo.
// Al editar solo se cambian los datos; el perfil y la contraseña tienen su propia pantalla.
// Los name de los campos son distintos en alta y edicion (los leen ins_acceso y upd_acceso)
if (!function_exists('csrf_campo')) {
    exit;
}

$esNuevo = empty($usuario);
$u = $usuario ?: (object)['id' => 0, 'nombre' => '', 'username' => '', 'correo' => ''];
$cabecera = [
    'titulo'    => $esNuevo ? 'Nuevo usuario' : 'Editar usuario',
    'subtitulo' => $esNuevo ? 'Crea un acceso al sistema con su perfil' : 'Actualiza los datos del usuario',
    'form'      => 'form-usuario',
    'boton'     => $esNuevo ? 'crear_usuario' : 'upd_acceso',
    'texto'     => $esNuevo ? 'Crear usuario' : 'Guardar cambios',
];
include __DIR__ . '/../layout/cabecera_form.php';
?>

                <form id="form-usuario" action="" method="POST" autocomplete="off" class="tarjetas-form<?php echo $esNuevo ? '' : ' simple'; ?>">
                    <?php echo csrf_campo(); ?>
<?php if (!$esNuevo): ?>
                    <input type="hidden" name="useid" value="<?php echo (int)$u->id; ?>">
<?php endif; ?>
                    <section class="tarjeta-form">
                        <h2><span class="las la-user"></span> Datos del usuario</h2>
                        <label for="nombre">Nombre completo <span class="req">*</span></label>
                        <input type="text" id="nombre" name="<?php echo $esNuevo ? 'nombre' : 'nomuse'; ?>" value="<?php echo e($u->nombre); ?>" maxlength="35" placeholder="ej. Juan Pérez" required>
                        <div class="fila-campos">
                            <div>
                                <label for="username">Usuario <span class="req">*</span></label>
                                <input type="text" id="username" name="<?php echo $esNuevo ? 'username' : 'namuse'; ?>" value="<?php echo e($u->username); ?>" maxlength="25" placeholder="ej. jperez" required>
                            </div>
                            <div>
                                <label for="correo">Correo electrónico <span class="req">*</span></label>
                                <input type="email" id="correo" name="<?php echo $esNuevo ? 'correo' : 'corruse'; ?>" value="<?php echo e($u->correo); ?>" maxlength="35" placeholder="ej. jperez@ck.pe" required>
                            </div>
                        </div>
<?php if (!$esNuevo): ?>
                        <p class="nota"><span class="las la-info-circle"></span> Para cambiar la contraseña usa la opción <a href="cambiar.php?id=<?php echo (int)$u->id; ?>">Cambiar contraseña</a>.</p>
<?php endif; ?>
                    </section>
<?php if ($esNuevo): ?>

                    <section class="tarjeta-form">
                        <h2><span class="las la-key"></span> Acceso</h2>
                        <div class="fila-campos">
                            <div>
                                <label for="rol">Perfil <span class="req">*</span></label>
                                <select id="rol" name="rol" required>
<?php foreach ($ROLES as $valor => $etiqueta): ?>
                                    <option value="<?php echo (int)$valor; ?>"<?php echo (int)$valor === ROL_EMPLEADO ? ' selected' : ''; ?>><?php echo e($etiqueta); ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="state">Estado <span class="req">*</span></label>
                                <select id="state" name="state" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <label for="password">Contraseña <span class="req">*</span></label>
                        <input type="text" id="password" name="password" minlength="6" value="<?php echo e(password_predeterminada(ROL_EMPLEADO)); ?>" required>
                        <p class="nota"><span class="las la-info-circle"></span> Se coloca la contraseña predeterminada del perfil; puedes cambiarla antes de crear el usuario.</p>
                    </section>
<?php endif; ?>
                </form>
