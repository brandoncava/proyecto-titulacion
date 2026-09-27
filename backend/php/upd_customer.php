<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('clientes');

if (isset($_POST['upd_customer'])) {

    csrf_validar();

    $idcli  = filter_input(INPUT_POST, 'clid', FILTER_VALIDATE_INT);
    $tipd   = trim($_POST['tipcl'] ?? '');
    $nudoc  = trim($_POST['numcl'] ?? '');
    $nocl   = trim($_POST['namcl'] ?? '');
    $apcl   = trim($_POST['apecl'] ?? '');
    $telfcl = trim($_POST['telcl'] ?? '');

    if (!$idcli || $tipd === '' || $nudoc === '' || $telfcl === '') {
        avisar_y_redirigir('Error!', 'Completa el tipo y número de documento y el teléfono.', 'error', '../clientes/editar.php?id=' . (int)$idcli);
        return;
    }

    try {
        $sql = $connect->prepare(
            'UPDATE clientes SET tipd = ?, nudoc = ?, nocl = ?, apcl = ?, telfcl = ? WHERE idcli = ? LIMIT 1'
        );
        $sql->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl, $idcli]);

        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente', 'success', '../clientes/mostrar.php');

    } catch (PDOException $e) {
        error_log('Error al actualizar el cliente: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar el cliente. Comuníquese con el administrador.', 'error', '../clientes/mostrar.php');
    }
}
