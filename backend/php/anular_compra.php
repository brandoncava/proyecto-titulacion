<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['anular_compra'])) {

    // solo el admin puede anular
    requiere_permiso_api('compras_anular');
    csrf_validar();

    $idordpur = filter_input(INPUT_POST, 'idordpur', FILTER_VALIDATE_INT);
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$idordpur) {
        avisar_y_redirigir('Error!', 'Compra no válida.', 'error', '../compra/mostrar.php');
        return;
    }

    if ($motivo === '') {
        avisar_y_redirigir('Falta el motivo', 'Indica por qué se anula la compra.', 'warning', '../compra/mostrar.php');
        return;
    }

    try {
        $connect->beginTransaction();

        // bloquea la fila para que no se anule dos veces a la vez
        $consulta = $connect->prepare('SELECT idordpur, anulada FROM orders_purchase WHERE idordpur = ? FOR UPDATE');
        $consulta->execute([$idordpur]);
        $compra = $consulta->fetch();

        if (!$compra) {
            $connect->rollBack();
            avisar_y_redirigir('Error!', 'La compra no existe.', 'error', '../compra/mostrar.php');
            return;
        }

        if ((int)$compra->anulada === 1) {
            $connect->rollBack();
            avisar_y_redirigir('Sin cambios', 'Esta compra ya estaba anulada.', 'warning', '../compra/mostrar.php');
            return;
        }

        $detalle = $connect->prepare('SELECT idprod, cantidad FROM orders_purchase_detalle WHERE idordpur = ?');
        $detalle->execute([$idordpur]);
        $lineas = $detalle->fetchAll(PDO::FETCH_ASSOC);

        // la compra subio el stock, al anularla se descuenta; si ya se vendio parte no alcanza
        $descontar = $connect->prepare('UPDATE productos SET stock = stock - ? WHERE idprod = ? AND stock >= ?');
        foreach ($lineas as $linea) {
            $descontar->execute([(int)$linea['cantidad'], (int)$linea['idprod'], (int)$linea['cantidad']]);

            if ($descontar->rowCount() === 0) {
                $connect->rollBack();
                avisar_y_redirigir('No se puede anular', 'Ya se vendió parte de la mercadería de esta compra, no hay stock suficiente para descontarla.', 'error', '../compra/mostrar.php');
                return;
            }

            registrar_movimiento($connect, (int)$linea['idprod'], (int)$_SESSION['id'], 'Anulacion', -(int)$linea['cantidad'], null, $idordpur);
        }

        $anular = $connect->prepare(
            "UPDATE orders_purchase
                SET anulada = 1,
                    anulada_on = NOW(),
                    motivo_anulacion = ?,
                    payment_status = 'Anulado'
              WHERE idordpur = ?"
        );
        $anular->execute([$motivo, $idordpur]);

        $connect->commit();

        $aviso = $lineas
            ? 'La compra se anuló y se descontó el stock.'
            : 'La compra se anuló. (Es una compra antigua sin detalle, no se pudo descontar stock automáticamente.)';

        avisar_y_redirigir('Compra anulada', $aviso, 'success', '../compra/mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al anular la compra: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo anular la compra. No se modificó nada.', 'error', '../compra/mostrar.php');
    }
}
