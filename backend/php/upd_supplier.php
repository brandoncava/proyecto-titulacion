<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('proveedores');

if (isset($_POST['upd_supplier'])) {

    csrf_validar();

    $idprov  = filter_input(INPUT_POST, 'suppid', FILTER_VALIDATE_INT);
    $rucprv  = preg_replace('/\D/', '', (string)($_POST['rcprv'] ?? ''));
    $nomprv  = mb_substr(trim($_POST['noprv'] ?? ''), 0, 150);
    $corrprv = leer_correo($_POST['corprv'] ?? '', 35);
    $volver  = '../proveedores/editar.php?id=' . (int)$idprov;

    $actual = $connect->prepare('SELECT rucprv FROM proveedores WHERE idprov = ?');
    $actual->execute([$idprov]);
    $rucAnterior = $actual->fetchColumn();

    if (!$idprov || $rucAnterior === false) {
        avisar_y_redirigir('Error!', 'Proveedor no válido.', 'error', '../proveedores/mostrar.php');
        return;
    }
    // el RUC se valida solo si se cambia: hay proveedores antiguos con un RUC que no pasa el digito verificador
    if ($rucprv !== $rucAnterior && !validar_ruc($rucprv)) {
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

    $repetido = $connect->prepare('SELECT 1 FROM proveedores WHERE rucprv = ? AND idprov <> ?');
    $repetido->execute([$rucprv, $idprov]);
    if ($repetido->fetch()) {
        avisar_y_redirigir('Error!', 'Ese RUC ya pertenece a otro proveedor.', 'error', $volver);
        return;
    }

    try {
        $connect->prepare('UPDATE proveedores SET rucprv = ?, nomprv = ?, corrprv = ? WHERE idprov = ? LIMIT 1')
                ->execute([$rucprv, $nomprv, $corrprv, $idprov]);
        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente.', 'success', '../proveedores/mostrar.php');
    } catch (PDOException $e) {
        error_log('Error al actualizar el proveedor: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar. Comuníquese con el administrador.', 'error', $volver);
    }
}
