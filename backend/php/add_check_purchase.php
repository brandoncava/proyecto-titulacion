<?php
require_once __DIR__ . '/../config/app.php';

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

    // "Modificar compra": se edita la misma compra en vez de crear otra
    $modifica = isset($_SESSION['modifica_compra']) ? (int)$_SESSION['modifica_compra']['idordpur'] : 0;
    if ($modifica && !puede('compras_anular')) {
        avisar_y_redirigir('Sin permiso', 'Solo el administrador puede modificar compras.', 'error', 'mostrar.php');
        return;
    }

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

    // el mismo comprobante del proveedor no se registra dos veces (sin contar la compra que se modifica)
    $repetido = $connect->prepare('SELECT 1 FROM orders_purchase WHERE idprov = ? AND tipc = ? AND serie_doc = ? AND numero_doc = ? AND idordpur <> ? LIMIT 1');
    $repetido->execute([$idprov, $tipc, $serie_doc, $numero_doc, $modifica]);
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

        if ($modifica) {
            $buscar = $connect->prepare('SELECT idordpur, anulada FROM orders_purchase WHERE idordpur = ? FOR UPDATE');
            $buscar->execute([$modifica]);
            $compra = $buscar->fetch();

            if (!$compra || (int)$compra->anulada === 1) {
                $connect->rollBack();
                unset($_SESSION['modifica_compra']);
                avisar_y_redirigir('No se puede modificar', 'La compra ya no existe o fue anulada.', 'error', 'mostrar.php');
                return;
            }

            // diferencia de unidades por producto: lo nuevo menos lo que tenia la compra
            $antes = $connect->prepare('SELECT idprod, SUM(cantidad) AS cantidad FROM orders_purchase_detalle WHERE idordpur = ? GROUP BY idprod');
            $antes->execute([$modifica]);
            $diferencia = [];
            foreach ($antes->fetchAll(PDO::FETCH_ASSOC) as $l) {
                $diferencia[(int)$l['idprod']] = -(int)$l['cantidad'];
            }
            foreach ($items as $item) {
                $diferencia[(int)$item['idprod']] = ($diferencia[(int)$item['idprod']] ?? 0) + (int)$item['quantity'];
            }

            $sumar  = $connect->prepare('UPDATE productos SET stock = stock + ? WHERE idprod = ?');
            $restar = $connect->prepare('UPDATE productos SET stock = stock - ? WHERE idprod = ? AND stock >= ?');
            foreach ($diferencia as $idprod => $cant) {
                if ($cant > 0) {
                    $sumar->execute([$cant, $idprod]);
                } elseif ($cant < 0) {
                    // quitar unidades de la compra: no se puede si ya se vendieron
                    $restar->execute([-$cant, $idprod, -$cant]);
                    if ($restar->rowCount() === 0) {
                        $connect->rollBack();
                        avisar_y_redirigir('No se puede modificar', 'Ya se vendieron unidades que quieres quitar de la compra; no hay stock suficiente para descontarlas.', 'error', 'cart.php');
                        return;
                    }
                } else {
                    continue;
                }
                registrar_movimiento($connect, $idprod, $user_id, 'Modificacion', $cant, null, $modifica);
            }

            $connect->prepare(
                'UPDATE orders_purchase
                    SET idprov = ?, method = ?, total_products = ?, total_price = ?, subtotal_sin_igv = ?, igv = ?,
                        tipc = ?, serie_doc = ?, numero_doc = ?, fecha_emision = ?, modificada_on = NOW()
                  WHERE idordpur = ?'
            )->execute([
                $idprov, $method, implode(', ', $resumen), $total, $base, $igv,
                $tipc, $serie_doc, $numero_doc, $fecha_emision, $modifica,
            ]);

            $connect->prepare('DELETE FROM orders_purchase_detalle WHERE idordpur = ?')->execute([$modifica]);
            $idordpur = $modifica;
        } else {
            $insertar = $connect->prepare(
                'INSERT INTO orders_purchase (user_id, idprov, method, total_products, total_price, subtotal_sin_igv, igv, placed_on, payment_status, tipc,
                                              serie_doc, numero_doc, fecha_emision)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)'
            );
            $insertar->execute([
                $user_id, $idprov, $method, implode(', ', $resumen), $total, $base, $igv, 'Aceptado', $tipc, $serie_doc, $numero_doc, $fecha_emision
            ]);

            $idordpur = (int)$connect->lastInsertId();
        }

        // una compra suma stock, al reves que una venta
        $detalle = $connect->prepare(
            'INSERT INTO orders_purchase_detalle (idordpur, idprod, nombre, precio, cantidad, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        // guarda el ultimo costo del producto (y en una compra nueva suma el stock)
        $sumarStock  = $connect->prepare('UPDATE productos SET stock = stock + ?, precio_compra = ? WHERE idprod = ?');
        $soloCosto   = $connect->prepare('UPDATE productos SET precio_compra = ? WHERE idprod = ?');

        foreach ($items as $item) {
            $detalle->execute([
                $idordpur,
                $item['idprod'],
                $item['nomprd'],
                $item['costo'],
                $item['quantity'],
                round($item['costo'] * $item['quantity'], 2),
            ]);

            if ($modifica) {
                $soloCosto->execute([$item['costo'], $item['idprod']]);
            } else {
                $sumarStock->execute([$item['quantity'], $item['costo'], $item['idprod']]);
                registrar_movimiento($connect, $item['idprod'], $user_id, 'Compra', (int)$item['quantity'], null, $idordpur);
            }
        }

        $connect->prepare('DELETE FROM cart_purchase WHERE user_id = ?')->execute([$user_id]);

        $connect->commit();
        unset($_SESSION['modifica_compra']);

        if ($modifica) {
            avisar_y_redirigir('Compra modificada', 'Se actualizó la compra N° ' . $modifica . ' y el stock se ajustó con la diferencia.', 'success', 'mostrar.php');
        } else {
            avisar_y_redirigir('Compra registrada', 'La compra se guardó y el stock quedó actualizado.', 'success', 'mostrar.php');
        }

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al registrar la compra: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo registrar la compra. No se modificó nada.', 'error', 'cart.php');
    }
}
