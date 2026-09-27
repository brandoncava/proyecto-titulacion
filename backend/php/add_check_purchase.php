<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('compras');

if (isset($_POST['order'])) {

    csrf_validar();

    // el user_id sale de la sesion, no del form
    $user_id = (int)$_SESSION['id'];
    $idprov = filter_input(INPUT_POST, 'cxtprov', FILTER_VALIDATE_INT);
    $method = trim($_POST['cxtcre'] ?? '');
    $tipc = trim($_POST['cxcom'] ?? '');

    if (!$idprov) {
        avisar_y_redirigir('Falta el proveedor', 'Selecciona el proveedor de la compra.', 'warning', 'cart.php');
        return;
    }

    try {
        $connect->beginTransaction();

        $consulta = $connect->prepare(
            'SELECT cp.idcpr, cp.idprod, cp.quantity, p.nomprd, p.precio
               FROM cart_purchase cp
               INNER JOIN productos p ON cp.idprod = p.idprod
              WHERE cp.user_id = ?
              FOR UPDATE'
        );
        $consulta->execute([$user_id]);
        $items = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (!$items) {
            $connect->rollBack();
            avisar_y_redirigir('Carrito vacío', 'Agrega productos antes de registrar la compra.', 'warning', 'nuevo.php');
            return;
        }

        $resumen = [];
        $total = 0;

        foreach ($items as $item) {
            $subtotal = $item['precio'] * $item['quantity'];
            $total   += $subtotal;
            $resumen[] = $item['nomprd'] . ' ( ' . $item['quantity'] . ' )';
        }

        $insertar = $connect->prepare(
            'INSERT INTO orders_purchase (user_id, idprov, method, total_products, total_price, placed_on, payment_status, tipc)
             VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)'
        );
        $insertar->execute([
            $user_id, $idprov, $method, implode(', ', $resumen), $total, 'Aceptado', $tipc
        ]);

        $idordpur = (int)$connect->lastInsertId();

        // una compra suma stock, al reves que una venta
        $detalle = $connect->prepare(
            'INSERT INTO orders_purchase_detalle (idordpur, idprod, nombre, precio, cantidad, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $sumar = $connect->prepare('UPDATE productos SET stock = stock + ? WHERE idprod = ?');

        foreach ($items as $item) {
            $detalle->execute([
                $idordpur,
                $item['idprod'],
                $item['nomprd'],
                $item['precio'],
                $item['quantity'],
                $item['precio'] * $item['quantity'],
            ]);

            $sumar->execute([$item['quantity'], $item['idprod']]);
        }

        $connect->prepare('DELETE FROM cart_purchase WHERE user_id = ?')->execute([$user_id]);

        $connect->commit();

        avisar_y_redirigir('Compra registrada', 'La compra se guardó y el stock quedó actualizado.', 'success', 'mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al registrar la compra: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo registrar la compra. No se modificó nada.', 'error', 'cart.php');
    }
}
