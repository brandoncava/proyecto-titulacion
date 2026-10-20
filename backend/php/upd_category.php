<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');

if (isset($_POST['upd_category'])) {

    csrf_validar();

    $idcate = filter_input(INPUT_POST, 'cateid', FILTER_VALIDATE_INT);
    $nocate = mb_substr(trim($_POST['catnom'] ?? ''), 0, 100);
    $volver = '../categorias/editar.php?id=' . (int)$idcate;

    if (!$idcate || $nocate === '') {
        avisar_y_redirigir('Error!', 'Escribe el nombre de la categoría.', 'error', $volver);
        return;
    }

    $repetida = $connect->prepare('SELECT 1 FROM categoria WHERE nocate = ? AND idcate <> ?');
    $repetida->execute([$nocate, $idcate]);
    if ($repetida->fetch()) {
        avisar_y_redirigir('Error!', 'Ya existe otra categoría con ese nombre.', 'error', $volver);
        return;
    }

    try {
        $connect->prepare('UPDATE categoria SET nocate = ? WHERE idcate = ? LIMIT 1')->execute([$nocate, $idcate]);
        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente.', 'success', '../categorias/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al actualizar la categoria: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar. Comuníquese con el administrador.', 'error', $volver);
    }
}
