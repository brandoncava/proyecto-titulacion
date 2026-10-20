<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('usuarios');

if (isset($_POST['upd_acceso_pwd'])) {

    csrf_validar();

    $id       = filter_input(INPUT_POST, 'useid', FILTER_VALIDATE_INT);
    $password = (string)($_POST['pswuse'] ?? '');
    $volver   = '../accesos/cambiar.php?id=' . (int)$id;

    if (!$id) {
        avisar_y_redirigir('Error!', 'Usuario no válido.', 'error', '../accesos/mostrar.php');
        return;
    }
    if (strlen($password) < 6) {
        avisar_y_redirigir('Revisa los datos', 'La contraseña debe tener como mínimo 6 caracteres.', 'warning', $volver);
        return;
    }

    try {
        $connect->prepare('UPDATE usuarios SET password = ? WHERE id = ? LIMIT 1')
                ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        avisar_y_redirigir('¡Actualizado!', 'Contraseña actualizada correctamente.', 'success', '../accesos/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al cambiar la contraseña: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo cambiar la contraseña. Comuníquese con el administrador.', 'error', $volver);
    }
}
