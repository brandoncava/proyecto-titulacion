<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('catalogos');

if (isset($_POST['upd_marca'])) {

    csrf_validar();

    $idmar  = filter_input(INPUT_POST, 'idmar', FILTER_VALIDATE_INT);
    $nombre = trim($_POST['nommar'] ?? '');

    if (!$idmar || $nombre === '') {
        avisar_y_redirigir('Error!', 'Escribe el nombre de la marca.', 'error', '../marcas/mostrar.php');
        return;
    }

    $repetida = $connect->prepare('SELECT 1 FROM marca WHERE nomarc = ? AND idmar <> ?');
    $repetida->execute([$nombre, $idmar]);

    if ($repetida->fetch()) {
        avisar_y_redirigir('Error!', 'Ya existe otra marca con ese nombre.', 'error', '../marcas/editar.php?id=' . $idmar);
        return;
    }

    $connect->prepare('UPDATE marca SET nomarc = ? WHERE idmar = ? LIMIT 1')->execute([$nombre, $idmar]);
    avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente.', 'success', '../marcas/mostrar.php');
}
