<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('productos_ed');

if (isset($_POST['upd_prodct'])) {

    csrf_validar();

    $idprod = filter_input(INPUT_POST, 'prdid', FILTER_VALIDATE_INT);
    $codpro = trim($_POST['prdcod'] ?? '');
    $nomprd = trim($_POST['prdnom'] ?? '');
    $desprd = trim($_POST['prddes'] ?? '');
    $precio = trim($_POST['prdprec'] ?? '');
    $idmar  = filter_input(INPUT_POST, 'prdmarc', FILTER_VALIDATE_INT);
    $idcate = filter_input(INPUT_POST, 'prdcate', FILTER_VALIDATE_INT);
    $modelo = trim($_POST['prdmod'] ?? '');
    $peso   = trim($_POST['prdpes'] ?? '');
    $stock  = filter_input(INPUT_POST, 'prdstock', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $garantia = filter_input(INPUT_POST, 'prdgar', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]);
    if ($garantia === false || $garantia === null) {
        $garantia = 12;
    }
    $volver = '../productos/editar.php?id=' . (int)$idprod;

    if (!$idprod || $codpro === '' || $nomprd === '' || !is_numeric($precio) || $precio < 0) {
        avisar_y_redirigir('Error!', 'El código, el nombre y un precio válido son obligatorios.', 'error', $volver);
        return;
    }
    if ($stock === false || $stock === null) {
        avisar_y_redirigir('Error!', 'Indica un stock válido (0 o más).', 'error', $volver);
        return;
    }

    // el codigo no se puede repetir en otro producto
    $existe = $connect->prepare('SELECT 1 FROM productos WHERE codpro = ? AND idprod <> ? LIMIT 1');
    $existe->execute([$codpro, $idprod]);
    if ($existe->fetch()) {
        avisar_y_redirigir('Error!', 'Ya existe otro producto con ese código.', 'error', $volver);
        return;
    }

    try {
        $connect->beginTransaction();

        // stock anterior, para anotar la diferencia en el kardex
        $anterior = $connect->prepare('SELECT stock FROM productos WHERE idprod = ? FOR UPDATE');
        $anterior->execute([$idprod]);
        $stockAnterior = $anterior->fetchColumn();
        if ($stockAnterior === false) {
            $connect->rollBack();
            avisar_y_redirigir('Error!', 'El producto no existe.', 'error', '../productos/mostrar.php');
            return;
        }

        $connect->prepare(
            'UPDATE productos
                SET codpro = ?, nomprd = ?, desprd = ?, precio = ?, idmar = ?, idcate = ?, modelo = ?, peso = ?,
                    stock = ?, garantia_meses = ?
              WHERE idprod = ? LIMIT 1'
        )->execute([$codpro, $nomprd, $desprd, $precio, $idmar, $idcate, $modelo, $peso, $stock, $garantia, $idprod]);

        if ((int)$stock !== (int)$stockAnterior) {
            registrar_movimiento($connect, $idprod, (int)$_SESSION['id'], 'Ajuste', (int)$stock - (int)$stockAnterior);
        }

        $connect->commit();

        avisar_y_redirigir('Actualizado!', 'Se actualizó correctamente', 'success', '../productos/mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al actualizar el producto: ' . $e->getMessage());
        avisar_y_redirigir('Error!', 'No se pudo actualizar el producto. Comuníquese con el administrador.', 'error', $volver);
    }
}
