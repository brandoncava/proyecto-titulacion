<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('usuarios');

if (isset($_POST['crear_usuario'])) {

    csrf_validar();

    $volver   = '../accesos/nuevo.php';
    $error    = '';
    $datos    = leer_datos_usuario($_POST['nombre'] ?? '', $_POST['username'] ?? '', $_POST['correo'] ?? '', $error);
    $rol      = (int)($_POST['rol'] ?? ROL_EMPLEADO);
    $state    = (int)($_POST['state'] ?? 1) === 0 ? 0 : 1;
    $password = (string)($_POST['password'] ?? '');

    if ($datos === null) {
        avisar_y_redirigir('Revisa los datos', $error, 'warning', $volver);
        return;
    }
    if (!rol_valido($rol)) {
        avisar_y_redirigir('Revisa los datos', 'El perfil seleccionado no es válido.', 'warning', $volver);
        return;
    }
    if (strlen($password) < 6) {
        avisar_y_redirigir('Revisa los datos', 'La contraseña debe tener como mínimo 6 caracteres.', 'warning', $volver);
        return;
    }
    [$nombre, $username, $correo] = $datos;

    $existe = $connect->prepare('SELECT 1 FROM usuarios WHERE username = ? LIMIT 1');
    $existe->execute([$username]);
    if ($existe->fetch()) {
        avisar_y_redirigir('Usuario repetido', 'El nombre de usuario ya existe.', 'warning', $volver);
        return;
    }

    try {
        $connect->prepare('INSERT INTO usuarios (nombre, username, correo, password, rol, state) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$nombre, $username, $correo, password_hash($password, PASSWORD_DEFAULT), $rol, $state]);
        avisar_y_redirigir('Registrado!', 'Usuario creado correctamente.', 'success', '../accesos/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al crear el usuario: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo crear el usuario. Comuníquese con el administrador.', 'error', $volver);
    }
}
