<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('usuarios');

if (isset($_POST['upd_acceso'])) {

    csrf_validar();

    $id     = filter_input(INPUT_POST, 'useid', FILTER_VALIDATE_INT);
    $volver = '../accesos/editar.php?id=' . (int)$id;
    $error  = '';
    $datos  = leer_datos_usuario($_POST['nomuse'] ?? '', $_POST['namuse'] ?? '', $_POST['corruse'] ?? '', $error);

    if (!$id || $datos === null) {
        avisar_y_redirigir('Revisa los datos', $error ?: 'Usuario no válido.', 'warning', $volver);
        return;
    }
    [$nombre, $username, $correo] = $datos;

    $repetido = $connect->prepare('SELECT 1 FROM usuarios WHERE username = ? AND id <> ? LIMIT 1');
    $repetido->execute([$username, $id]);
    if ($repetido->fetch()) {
        avisar_y_redirigir('Usuario repetido', 'Ese nombre de usuario ya lo tiene otra persona.', 'warning', $volver);
        return;
    }

    try {
        $connect->prepare('UPDATE usuarios SET nombre = ?, username = ?, correo = ? WHERE id = ? LIMIT 1')
                ->execute([$nombre, $username, $correo, $id]);
        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente.', 'success', '../accesos/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al actualizar el usuario: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar. Comuníquese con el administrador.', 'error', $volver);
    }
}
