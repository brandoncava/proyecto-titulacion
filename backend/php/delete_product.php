<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_product'])) {

    requiere_permiso_api('productos_el');
    csrf_validar();

    $id = filter_input(INPUT_POST, 'idprod', FILTER_VALIDATE_INT);

    if ($id === false || $id === null) {
        avisar_y_redirigir('Error!', 'Identificador no valido.', 'error', '../productos/mostrar.php');
        return;
    }

    // baja logica, state = 0
    $sql = $connect->prepare('UPDATE `productos` SET state = 0 WHERE `idprod` = ? AND state = 1');
    $sql->execute([$id]);

    if ($sql->rowCount() > 0) {
        avisar_y_redirigir('Dado de baja!', 'El producto ya no aparece en el listado.', 'success', '../productos/mostrar.php');
    } else {
        avisar_y_redirigir('Sin cambios', 'El producto no existe o ya estaba dado de baja.', 'warning', '../productos/mostrar.php');
    }
}
