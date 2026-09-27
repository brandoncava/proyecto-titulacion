<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('ventas');

if (isset($_POST['order'])) {

    csrf_validar();

    // el user_id sale de la sesion, no del form
    $user_id = (int)$_SESSION['id'];
    $nomcl = trim($_POST['nomcl'] ?? '');
    $method = trim($_POST['cxtcre'] ?? '');
    $tipc = trim($_POST['cxcom'] ?? '');

    try {
        $connect->beginTransaction();

        // FOR UPDATE para que no se pisen dos ventas del mismo producto
        $consulta = $connect->prepare(
            'SELECT c.idv, c.idprod, c.quantity, p.nomprd, p.precio, p.stock
               FROM cart c
               INNER JOIN productos p ON c.idprod = p.idprod
              WHERE c.user_id = ?
              FOR UPDATE'
        );
        $consulta->execute([$user_id]);
        $items = $consulta->fetchAll(PDO::FETCH_ASSOC);

        if (!$items) {
            $connect->rollBack();
            avisar_y_redirigir('Carrito vacío', 'Agrega productos antes de registrar la venta.', 'warning', 'nuevo.php');
            return;
        }

        // no vender mas de lo que hay
        $faltantes = [];
        foreach ($items as $item) {
            if ((int)$item['quantity'] > (int)$item['stock']) {
                $faltantes[] = $item['nomprd'] . ' (disponible: ' . (int)$item['stock'] . ')';
            }
        }

        if ($faltantes) {
            $connect->rollBack();
            avisar_y_redirigir('Stock insuficiente', 'No hay existencias suficientes de: ' . implode(', ', $faltantes), 'error', 'cart.php');
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
            'INSERT INTO orders (user_id, nomcl, method, total_products, total_price, placed_on, payment_status, tipc)
             VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)'
        );
        $insertar->execute([
            $user_id, $nomcl, $method, implode(', ', $resumen), $total, 'Aceptado', $tipc
        ]);

        $idord = (int)$connect->lastInsertId();

        $detalle = $connect->prepare(
            'INSERT INTO orders_detalle (idord, idprod, nombre, precio, cantidad, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $descontar = $connect->prepare(
            'UPDATE productos SET stock = stock - ? WHERE idprod = ? AND stock >= ?'
        );

        foreach ($items as $item) {
            $detalle->execute([
                $idord,
                $item['idprod'],
                $item['nomprd'],
                $item['precio'],
                $item['quantity'],
                $item['precio'] * $item['quantity'],
            ]);

            $descontar->execute([$item['quantity'], $item['idprod'], $item['quantity']]);

            // si otra venta se adelanto no afecta ninguna fila, se aborta
            if ($descontar->rowCount() === 0) {
                $connect->rollBack();
                avisar_y_redirigir('Stock insuficiente', 'El stock de "' . $item['nomprd'] . '" cambió mientras se procesaba la venta.', 'error', 'cart.php');
                return;
            }
        }

        $connect->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$user_id]);

        $connect->commit();

        avisar_y_redirigir('Venta registrada', 'La venta se guardó y el stock quedó actualizado.', 'success', 'mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al registrar la venta: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo registrar la venta. No se modificó nada.', 'error', 'cart.php');
    }
}
