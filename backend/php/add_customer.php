<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('clientes');

if (isset($_POST['add_customer'])) {

    csrf_validar();

    $volver = '../clientes/nuevo.php';
    $datos = leer_datos_cliente($error);
    if ($datos === null) {
        avisar_y_redirigir('Revisa los datos', $error, 'warning', $volver);
        return;
    }
    [$tipd, $nudoc, $nocl, $apcl, $telfcl, $email, $promos] = $datos;

    // documento y telefono (si lo ponen) no se repiten
    $consulta = 'SELECT idcli, state FROM clientes WHERE nudoc = ?';
    $params = [$nudoc];
    if ($telfcl !== '') {
        $consulta .= ' OR telfcl = ?';
        $params[] = $telfcl;
    }
    $buscar = $connect->prepare($consulta . ' LIMIT 1');
    $buscar->execute($params);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Cliente repetido', 'Ya existe un cliente con ese documento o teléfono.', 'warning', $volver);
            return;
        }
        // estaba dado de baja: se reactiva con los datos nuevos
        $connect->prepare('UPDATE clientes SET tipd = ?, nudoc = ?, nocl = ?, apcl = ?, telfcl = ?, emailcl = ?,
                                  acepta_promos = ?, acepta_promos_on = IF(? = 1, NOW(), NULL), state = 1 WHERE idcli = ?')
                ->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl, $email, $promos, $promos, $existente->idcli]);
        avisar_y_redirigir('Reactivado!', 'El cliente estaba dado de baja y se volvió a activar.', 'success', '../clientes/mostrar.php');
        return;
    }

    try {
        $connect->prepare('INSERT INTO clientes (tipd, nudoc, nocl, apcl, telfcl, emailcl, acepta_promos, acepta_promos_on, state)
                           VALUES (?, ?, ?, ?, ?, ?, ?, IF(? = 1, NOW(), NULL), 1)')
                ->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl, $email, $promos, $promos]);
        avisar_y_redirigir('Registrado!', 'Cliente agregado correctamente.', 'success', '../clientes/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al registrar el cliente: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo guardar el cliente. Comuníquese con el administrador.', 'error', $volver);
    }
}
