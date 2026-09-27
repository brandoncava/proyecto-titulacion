<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_category'])) {

    requiere_permiso_api('catalogos');
    csrf_validar();

    $id = filter_input(INPUT_POST, 'idcate', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        avisar_y_redirigir('Error!', 'Identificador no valido.', 'error', '../categorias/mostrar.php');
        return;
    }

    // baja logica, state = 0
    $sql = $connect->prepare('UPDATE `categoria` SET state = 0 WHERE `idcate` = ? AND state = 1');
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', 'La categoria ya no aparece en el listado.', 'success', '../categorias/mostrar.php');
    } else {
        avisar_y_redirigir('Sin cambios', 'La categoria no existe o ya estaba dado de baja.', 'warning', '../categorias/mostrar.php');
    }
}
