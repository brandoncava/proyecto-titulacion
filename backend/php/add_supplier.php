<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('proveedores');

if (isset($_POST['add_supplier'])) {

    $rucprv = trim($_POST['rcprv'] ?? '');
    $nomprv = trim($_POST['nomprv'] ?? '');
    $corrprv = trim($_POST['corrprv'] ?? '');

    if ($rucprv === '' || $nomprv === '') {
        avisar_y_redirigir('Error!', 'El RUC y el nombre del proveedor son obligatorios.', 'error', '../proveedores/nuevo.php');
        return;
    }

    // si esta dado de baja se reactiva en vez de duplicar
    $buscar = $connect->prepare('SELECT idprov, state FROM proveedores WHERE rucprv = ? LIMIT 1');
    $buscar->execute([$rucprv]);
    $existente = $buscar->fetch();

    if ($existente) {
        if ((int)$existente->state === 1) {
            avisar_y_redirigir('Error!', 'Ya existe un proveedor con ese RUC.', 'error', '../proveedores/nuevo.php');
            return;
        }

        $connect->prepare('UPDATE proveedores SET nomprv = ?, corrprv = ?, state = 1 WHERE idprov = ?')
                ->execute([$nomprv, $corrprv, $existente->idprov]);
        avisar_y_redirigir('Reactivado!', 'El proveedor estaba dado de baja y se volvio a activar.', 'success', '../proveedores/mostrar.php');
        return;
    }

    $insertar = $connect->prepare('INSERT INTO proveedores (rucprv, nomprv, corrprv, state) VALUES (?, ?, ?, 1)');

    if ($insertar->execute([$rucprv, $nomprv, $corrprv])) {
        avisar_y_redirigir('Registrado!', 'Se agrego correctamente.', 'success', '../proveedores/mostrar.php');
    } else {
        avisar_y_redirigir('Error!', 'No se pudo guardar. Comuniquese con el administrador.', 'error', '../proveedores/nuevo.php');
    }
}
