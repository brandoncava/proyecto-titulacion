<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_customer'])) {

    requiere_permiso_api('clientes');
    csrf_validar();

    $id = filter_input(INPUT_POST, 'idcli', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        avisar_y_redirigir('Error!', 'Identificador no valido.', 'error', '../clientes/mostrar.php');
        return;
    }

    // baja logica, state = 0
    $sql = $connect->prepare('UPDATE `clientes` SET state = 0 WHERE `idcli` = ? AND state = 1');
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', 'El cliente ya no aparece en el listado.', 'success', '../clientes/mostrar.php');
    } else {
        avisar_y_redirigir('Sin cambios', 'El cliente no existe o ya estaba dado de baja.', 'warning', '../clientes/mostrar.php');
    }
}
