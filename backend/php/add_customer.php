<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('clientes');

if (isset($_POST['add_customer'])) {

    csrf_validar();

    $tipd = trim($_POST['tipcl'] ?? '');
    $nudoc = trim($_POST['numcl'] ?? '');
    $nocl = trim($_POST['nomcl'] ?? '');
    $apcl = trim($_POST['apecl'] ?? '');
    $telfcl = trim($_POST['telcl'] ?? '');

    $tipd = strtoupper($tipd);

    if ($nudoc === '' || $nocl === '') {
        avisar_y_redirigir('Error!', 'El numero de documento y el nombre (o razon social) son obligatorios.', 'error', '../clientes/nuevo.php');
        return;
    }

    if ($tipd === 'DNI' && (!validar_dni($nudoc) || $apcl === '')) {
        avisar_y_redirigir('Error!', 'El DNI debe tener 8 digitos y el apellido es obligatorio.', 'error', '../clientes/nuevo.php');
        return;
    }

    if ($tipd === 'RUC' && !validar_ruc($nudoc)) {
        avisar_y_redirigir('Error!', 'RUC no valido: debe tener 11 digitos y un digito verificador correcto.', 'error', '../clientes/nuevo.php');
        return;
    }

    if ($tipd !== 'DNI' && $tipd !== 'RUC') {
        avisar_y_redirigir('Error!', 'Elige el tipo de documento (DNI o RUC).', 'error', '../clientes/nuevo.php');
        return;
    }

    // documento y telefono (si lo ponen) deben ser unicos
    $consultaDup = 'SELECT idcli, state FROM clientes WHERE nudoc = ?';
    $paramsDup = [$nudoc];
    if ($telfcl !== '') {
        $consultaDup .= ' OR telfcl = ?';
        $paramsDup[] = $telfcl;
    }
    $buscar = $connect->prepare($consultaDup . ' LIMIT 1');
    $buscar->execute($paramsDup);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Error!', 'Ya existe un cliente con ese documento o telefono.', 'error', '../clientes/nuevo.php');
            return;
        }

        $connect->prepare('UPDATE clientes SET tipd = ?, nudoc = ?, nocl = ?, apcl = ?, telfcl = ?, state = 1 WHERE idcli = ?')
                ->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl, $existente->idcli]);
        avisar_y_redirigir('Reactivado!', 'El cliente estaba dado de baja y se volvio a activar.', 'success', '../clientes/mostrar.php');
        return;
    }

    $insertar = $connect->prepare(
        'INSERT INTO clientes (tipd, nudoc, nocl, apcl, telfcl, state) VALUES (?, ?, ?, ?, ?, 1)'
    );

    if ($insertar->execute([$tipd, $nudoc, $nocl, $apcl, $telfcl])) {
        avisar_y_redirigir('Registrado!', 'Se agrego correctamente.', 'success', '../clientes/mostrar.php');
    } else {
        avisar_y_redirigir('Error!', 'No se pudo guardar. Comuniquese con el administrador.', 'error', '../clientes/nuevo.php');
    }
}
