<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_supplier'])) {

    requiere_permiso_api('proveedores');
    csrf_validar();

    $id = filter_input(INPUT_POST, 'idprov', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        avisar_y_redirigir('Error!', 'Identificador no valido.', 'error', '../proveedores/mostrar.php');
        return;
    }

    // baja logica, state = 0
    $sql = $connect->prepare('UPDATE `proveedores` SET state = 0 WHERE `idprov` = ? AND state = 1');
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', 'El proveedor ya no aparece en el listado.', 'success', '../proveedores/mostrar.php');
    } else {
        avisar_y_redirigir('Sin cambios', 'El proveedor no existe o ya estaba dado de baja.', 'warning', '../proveedores/mostrar.php');
    }
}
