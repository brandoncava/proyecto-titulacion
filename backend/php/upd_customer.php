<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('clientes');

if (isset($_POST['upd_customer'])) {

    csrf_validar();

    $idcli  = filter_input(INPUT_POST, 'clid', FILTER_VALIDATE_INT) ?: 0;
    $volver = '../clientes/editar.php?id=' . $idcli;

    $datos = leer_datos_cliente($error);
    if (!$idcli || $datos === null) {
        avisar_y_redirigir('Revisa los datos', $error ?: 'Cliente no válido.', 'warning', $volver);
        return;
    }
    [$tipd, $nudoc, $nocl, $apcl, $telfcl, $email, $promos] = $datos;

    // el documento o telefono no pueden ser de otro cliente
    $consulta = 'SELECT 1 FROM clientes WHERE idcli <> ? AND (nudoc = ?';
    $params = [$idcli, $nudoc];
    if ($telfcl !== '') {
        $consulta .= ' OR telfcl = ?';
        $params[] = $telfcl;
    }
    $repetido = $connect->prepare($consulta . ') LIMIT 1');
    $repetido->execute($params);
    if ($repetido->fetch()) {
        avisar_y_redirigir('Cliente repetido', 'Ese documento o teléfono ya pertenece a otro cliente.', 'warning', $volver);
        return;
    }

    try {
        // la fecha de la autorizacion se conserva si ya habia aceptado; va antes de
        // acepta_promos porque mysql asigna en orden y aqui se lee el valor anterior
        $connect->prepare('UPDATE clientes SET tipd = ?, nudoc = ?, nocl = ?, apcl = ?, telfcl = ?, emailcl = ?,
                                  acepta_promos_on = IF(? = 1, IF(acepta_promos = 1, acepta_promos_on, NOW()), NULL),
                                  acepta_promos = ?
                            WHERE idcli = ? LIMIT 1')
                ->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl, $email, $promos, $promos, $idcli]);

        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente', 'success', '../clientes/mostrar.php');

    } catch (PDOException $e) {
        error_log('Error al actualizar el cliente: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar el cliente. Comuníquese con el administrador.', 'error', $volver);
    }
}
