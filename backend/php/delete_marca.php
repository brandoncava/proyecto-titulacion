<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_marca'])) {

    requiere_permiso_api('catalogos');
    csrf_validar();

    $id = filter_input(INPUT_POST, 'idmar', FILTER_VALIDATE_INT);

    if (!$id) {
        avisar_y_redirigir('Error!', 'Identificador no válido.', 'error', '../marcas/mostrar.php');
        return;
    }

    // no dejar productos activos con una marca dada de baja
    $enUso = $connect->prepare('SELECT COUNT(*) FROM productos WHERE idmar = ? AND state = 1');
    $enUso->execute([$id]);
    $productos = (int)$enUso->fetchColumn();

    if ($productos > 0) {
        avisar_y_redirigir('No se puede', "La marca la usan $productos producto(s). Cámbiales la marca primero.", 'warning', '../marcas/mostrar.php');
        return;
    }

    // baja logica, state = 0
    $sql = $connect->prepare('UPDATE marca SET state = 0 WHERE idmar = ? AND state = 1');
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', 'La marca ya no aparece en el listado.', 'success', '../marcas/mostrar.php');
    } else {
        avisar_y_redirigir('Sin cambios', 'La marca no existe o ya estaba dada de baja.', 'warning', '../marcas/mostrar.php');
    }
}
