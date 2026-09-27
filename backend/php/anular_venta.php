<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['anular_venta'])) {

    // solo el admin puede anular
    requiere_permiso_api('ventas_anular');
    csrf_validar();

    $idord = filter_input(INPUT_POST, 'idord', FILTER_VALIDATE_INT);
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$idord) {
        avisar_y_redirigir('Error!', 'Venta no válida.', 'error', '../ventas/mostrar.php');
        return;
    }

    if ($motivo === '') {
        avisar_y_redirigir('Falta el motivo', 'Indica por qué se anula la venta.', 'warning', '../ventas/mostrar.php');
        return;
    }

    try {
        $connect->beginTransaction();

        // bloquea la fila para que no se anule dos veces a la vez
        $consulta = $connect->prepare('SELECT idord, anulada FROM orders WHERE idord = ? FOR UPDATE');
        $consulta->execute([$idord]);
        $venta = $consulta->fetch();

        if (!$venta) {
            $connect->rollBack();
            avisar_y_redirigir('Error!', 'La venta no existe.', 'error', '../ventas/mostrar.php');
            return;
        }

        if ((int)$venta->anulada === 1) {
            $connect->rollBack();
            avisar_y_redirigir('Sin cambios', 'Esta venta ya estaba anulada.', 'warning', '../ventas/mostrar.php');
            return;
        }

        // devuelve el stock, solo si la venta tiene detalle guardado
        $detalle = $connect->prepare('SELECT idprod, cantidad FROM orders_detalle WHERE idord = ?');
        $detalle->execute([$idord]);
        $lineas = $detalle->fetchAll(PDO::FETCH_ASSOC);

        $devolver = $connect->prepare('UPDATE productos SET stock = stock + ? WHERE idprod = ?');
        foreach ($lineas as $linea) {
            $devolver->execute([(int)$linea['cantidad'], (int)$linea['idprod']]);
        }

        $anular = $connect->prepare(
            "UPDATE orders
                SET anulada = 1,
                    anulada_on = NOW(),
                    motivo_anulacion = ?,
                    payment_status = 'Anulado'
              WHERE idord = ?"
        );
        $anular->execute([$motivo, $idord]);

        $connect->commit();

        $aviso = $lineas
            ? 'La venta se anuló y se devolvió el stock.'
            : 'La venta se anuló. (Es una venta antigua sin detalle, no se pudo devolver stock automáticamente.)';

        avisar_y_redirigir('Venta anulada', $aviso, 'success', '../ventas/mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al anular la venta: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo anular la venta. No se modificó nada.', 'error', '../ventas/mostrar.php');
    }
}
