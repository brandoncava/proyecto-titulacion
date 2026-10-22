<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('proveedores');

if (isset($_POST['add_supplier'])) {

    csrf_validar();

    $volver  = '../proveedores/nuevo.php';
    $rucprv  = preg_replace('/\D/', '', (string)($_POST['rcprv'] ?? ''));
    $nomprv  = mb_substr(trim($_POST['nomprv'] ?? ''), 0, 150);
    $corrprv = leer_correo($_POST['corrprv'] ?? '', 35);

    if (!validar_ruc($rucprv)) {
        avisar_y_redirigir('RUC no válido', 'El RUC debe tener 11 dígitos y un dígito verificador correcto.', 'warning', $volver);
        return;
    }
    if ($nomprv === '') {
        avisar_y_redirigir('Error!', 'Escribe la razón social del proveedor.', 'error', $volver);
        return;
    }
    if ($corrprv === null) {
        avisar_y_redirigir('Error!', 'Revisa el correo del proveedor.', 'error', $volver);
        return;
    }

    // si esta dado de baja se reactiva en vez de duplicar
    $buscar = $connect->prepare('SELECT idprov, state FROM proveedores WHERE rucprv = ? LIMIT 1');
    $buscar->execute([$rucprv]);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Error!', 'Ya existe un proveedor con ese RUC.', 'error', $volver);
            return;
        }

        $connect->prepare('UPDATE proveedores SET nomprv = ?, corrprv = ?, state = 1 WHERE idprov = ?')
                ->execute([$nomprv, $corrprv, $existente->idprov]);
        avisar_y_redirigir('Reactivado!', 'El proveedor estaba dado de baja y se volvió a activar.', 'success', '../proveedores/mostrar.php');
        return;
    }

    try {
        $connect->prepare('INSERT INTO proveedores (rucprv, nomprv, corrprv, state) VALUES (?, ?, ?, 1)')
                ->execute([$rucprv, $nomprv, $corrprv]);
        avisar_y_redirigir('Registrado!', 'Se agregó correctamente.', 'success', '../proveedores/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al registrar el proveedor: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo guardar. Comuníquese con el administrador.', 'error', $volver);
    }
}
