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
    // 1 = el comprobante del proveedor trae igv, 0 = no lo trae
    $con_igv = (($_POST['cxigv'] ?? '1') !== '0');
    // comprobante que entrego el proveedor
    $serie_doc = strtoupper(trim($_POST['serie_doc'] ?? ''));
    $numero_doc = trim($_POST['numero_doc'] ?? '');
    $fecha_emision = trim($_POST['fecha_emision'] ?? '');

    if (!$idprov) {
        avisar_y_redirigir('Falta el proveedor', 'Selecciona el proveedor de la compra.', 'warning', 'cart.php');
        return;
    }

    if (!in_array($method, METODOS_PAGO, true) || !in_array($tipc, COMPROBANTES_COMPRA, true)) {
        avisar_y_redirigir('Faltan datos', 'Indica el comprobante y el método de pago.', 'warning', 'checkout.php');
        return;
    }

    $emision = DateTime::createFromFormat('Y-m-d', $fecha_emision);
    if (!preg_match('/^[A-Z0-9]{4}$/', $serie_doc) || !preg_match('/^\d{1,8}$/', $numero_doc)
        || !$emision || $emision->format('Y-m-d') !== $fecha_emision || $fecha_emision > date('Y-m-d')) {
        avisar_y_redirigir('Comprobante no válido', 'Revisa la serie (4 caracteres), el número (hasta 8 dígitos) y la fecha de emisión.', 'warning', 'checkout.php');
        return;
    }
    $numero_doc = str_pad($numero_doc, 8, '0', STR_PAD_LEFT);

    // el mismo comprobante del proveedor no se registra dos veces
    $repetido = $connect->prepare('SELECT 1 FROM orders_purchase WHERE idprov = ? AND tipc = ? AND serie_doc = ? AND numero_doc = ? LIMIT 1');
    $repetido->execute([$idprov, $tipc, $serie_doc, $numero_doc]);
    if ($repetido->fetch()) {
        avisar_y_redirigir('Comprobante repetido', 'Ese comprobante del proveedor ya está registrado.', 'warning', 'checkout.php');
        return;
    }

    try {
        $connect->beginTransaction();

        $consulta = $connect->prepare(
            'SELECT cp.idcpr, cp.idprod, cp.quantity, cp.price AS costo, p.nomprd
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
        $base = 0;

        // el costo del carrito va sin igv (el de la factura del proveedor)
        foreach ($items as $item) {
            $base += $item['costo'] * $item['quantity'];
            $resumen[] = $item['nomprd'] . ' ( ' . $item['quantity'] . ' )';
        }

        if ($con_igv) {
            list($base, $igv, $total) = igv_agregar($base);
        } else {
            $base = round($base, 2);
            $igv = 0;
            $total = $base;
        }

        $insertar = $connect->prepare(
            'INSERT INTO orders_purchase (user_id, idprov, method, total_products, total_price, subtotal_sin_igv, igv, placed_on, payment_status, tipc,
                                      serie_doc, numero_doc, fecha_emision)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)'
        );
        $insertar->execute([
            $user_id, $idprov, $method, implode(', ', $resumen), $total, $base, $igv, 'Aceptado', $tipc, $serie_doc, $numero_doc, $fecha_emision
        ]);

        $idordpur = (int)$connect->lastInsertId();

        // una compra suma stock, al reves que una venta
        $detalle = $connect->prepare(
            'INSERT INTO orders_purchase_detalle (idordpur, idprod, nombre, precio, cantidad, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        // suma el stock y guarda el ultimo costo del producto
        $sumar = $connect->prepare('UPDATE productos SET stock = stock + ?, precio_compra = ? WHERE idprod = ?');

        foreach ($items as $item) {
            $detalle->execute([
                $idordpur,
                $item['idprod'],
                $item['nomprd'],
                $item['costo'],
                $item['quantity'],
                round($item['costo'] * $item['quantity'], 2),
            ]);

            $sumar->execute([$item['quantity'], $item['costo'], $item['idprod']]);
            registrar_movimiento($connect, $item['idprod'], $user_id, 'Compra', (int)$item['quantity'], null, $idordpur);
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
