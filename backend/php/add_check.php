<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('ventas');

if (isset($_POST['order'])) {

    csrf_validar();

    // el user_id sale de la sesion, no del form
    $user_id  = (int)$_SESSION['id'];
    $doc_tipo = strtoupper(trim($_POST['doctipo'] ?? ''));          // '', DNI o RUC
    $doc_num  = preg_replace('/\s+/', '', (string)($_POST['docnum'] ?? ''));
    $nombre   = mb_substr(trim($_POST['nomcl'] ?? ''), 0, 150);

    // "Modificar venta": la venta original se anula y esta la reemplaza
    $reemplaza = isset($_SESSION['modifica_venta']) ? (int)$_SESSION['modifica_venta']['idord'] : 0;
    if ($reemplaza && !puede('ventas_anular')) {
        avisar_y_redirigir('Sin permiso', 'Solo el administrador puede modificar ventas.', 'error', 'mostrar.php');
        return;
    }

    // el documento decide el comprobante: ruc = factura, dni o nada = boleta
    if ($doc_tipo === '') {
        $doc_num = '';
    } elseif ($doc_tipo === 'DNI') {
        if (!validar_dni($doc_num)) {
            avisar_y_redirigir('DNI no válido', 'El DNI debe tener 8 dígitos.', 'warning', 'checkout.php');
            return;
        }
    } elseif ($doc_tipo === 'RUC') {
        if (!validar_ruc($doc_num)) {
            avisar_y_redirigir('RUC no válido', 'Revisa el RUC: debe tener 11 dígitos y un dígito verificador correcto.', 'warning', 'checkout.php');
            return;
        }
    } else {
        avisar_y_redirigir('Documento no válido', 'Elige DNI, RUC o deja el documento vacío.', 'warning', 'checkout.php');
        return;
    }

    $tipc = ($doc_tipo === 'RUC') ? 'Factura' : 'Boleta';

    try {
        $connect->beginTransaction();

        // primero se anula la venta que se reemplaza, asi su stock vuelve antes de validar el nuevo
        $original = null;
        if ($reemplaza) {
            $buscar = $connect->prepare('SELECT idord, anulada, serie, numero FROM orders WHERE idord = ? FOR UPDATE');
            $buscar->execute([$reemplaza]);
            $original = $buscar->fetch();

            if (!$original || (int)$original->anulada === 1) {
                $connect->rollBack();
                unset($_SESSION['modifica_venta']);
                avisar_y_redirigir('No se puede modificar', 'La venta original ya no existe o ya fue anulada.', 'error', 'mostrar.php');
                return;
            }
            anular_venta_tx($connect, $reemplaza, 'Modificada: reemplazada por otra venta', $user_id);
        }

        // FOR UPDATE para que no se pisen dos ventas del mismo producto;
        // se cobra el precio guardado en el carrito (en una modificacion, el de la venta original)
        $consulta = $connect->prepare(
            'SELECT c.idv, c.idprod, c.quantity, c.price AS precio, p.nomprd, p.stock
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
            $total += $item['precio'] * $item['quantity'];
            $resumen[] = $item['nomprd'] . ' ( ' . $item['quantity'] . ' )';
        }

        // el precio ya incluye igv, se guarda base, igv y total
        $total = round($total, 2);
        list($base, $igv) = igv_desglosar($total);

        // pagos: uno o varios medios, la suma tiene que ser el total
        $errorPago = '';
        $pagos = leer_pagos($total, $errorPago);
        if ($pagos === null) {
            $connect->rollBack();
            avisar_y_redirigir('Revisa los pagos', $errorPago, 'warning', 'checkout.php');
            return;
        }

        // boleta de mas de S/ 700 pide el dni
        if ($doc_tipo === '' && $total > LIMITE_BOLETA_SIN_DNI) {
            $connect->rollBack();
            avisar_y_redirigir(
                'Falta el documento',
                'El total supera S/ ' . number_format(LIMITE_BOLETA_SIN_DNI, 2) . '. Ingresa el DNI del cliente (o su RUC para factura).',
                'warning',
                'checkout.php'
            );
            return;
        }

        // cliente: se busca por documento, si no existe se registra
        $idcli = null;
        $nomcl = 'CLIENTES VARIOS';

        if ($doc_tipo !== '') {
            $buscar = $connect->prepare('SELECT idcli, nocl, apcl, state FROM clientes WHERE nudoc = ? LIMIT 1 FOR UPDATE');
            $buscar->execute([$doc_num]);
            $cliente = $buscar->fetch();

            if ($cliente) {
                $idcli = (int)$cliente->idcli;
                $nomcl = trim($cliente->nocl . ' ' . $cliente->apcl);
                if ((int)$cliente->state !== 1) {
                    $connect->prepare('UPDATE clientes SET state = 1 WHERE idcli = ?')->execute([$idcli]);
                }
            } else {
                if ($nombre === '') {
                    $connect->rollBack();
                    avisar_y_redirigir('Cliente nuevo', 'Ese documento no está registrado: escribe el nombre o la razón social del cliente.', 'warning', 'checkout.php');
                    return;
                }
                $connect->prepare('INSERT INTO clientes (tipd, nudoc, nocl, apcl, telfcl, state) VALUES (?, ?, ?, ?, ?, 1)')
                        ->execute([$doc_tipo, $doc_num, $nombre, '', '']);
                $idcli = (int)$connect->lastInsertId();
                $nomcl = $nombre;
            }
        } elseif ($nombre !== '') {
            // boleta sin documento: se guarda el nombre que se escribio
            $nomcl = $nombre;
        }

        // serie y numero del comprobante, la fila se bloquea para que no se repita
        $buscarSerie = $connect->prepare('SELECT idserie, serie, ultimo FROM series WHERE tipc = ? AND state = 1 ORDER BY idserie LIMIT 1 FOR UPDATE');
        $buscarSerie->execute([$tipc]);
        $serieFila = $buscarSerie->fetch();

        if (!$serieFila) {
            $connect->rollBack();
            avisar_y_redirigir('Sin serie', 'No hay una serie configurada para ' . $tipc . '.', 'error', 'checkout.php');
            return;
        }

        $numero = (int)$serieFila->ultimo + 1;
        $connect->prepare('UPDATE series SET ultimo = ? WHERE idserie = ?')->execute([$numero, $serieFila->idserie]);

        $insertar = $connect->prepare(
            'INSERT INTO orders (user_id, idcli, nomcl, method, total_products, total_price, subtotal_sin_igv, igv,
                                 placed_on, payment_status, reemplaza_a, tipc, idserie, serie, numero, doc_tipo, doc_num)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertar->execute([
            $user_id, $idcli, $nomcl, resumen_metodo($pagos), implode(', ', $resumen), $total, $base, $igv,
            'Aceptado', $reemplaza ?: null, $tipc, $serieFila->idserie, $serieFila->serie, $numero,
            $doc_tipo !== '' ? $doc_tipo : null,
            $doc_tipo !== '' ? $doc_num : null,
        ]);

        $idord = (int)$connect->lastInsertId();
        $comprobante = formato_comprobante($serieFila->serie, $numero);

        $guardarPago = $connect->prepare('INSERT INTO orders_pagos (idord, metodo, monto, num_operacion) VALUES (?, ?, ?, ?)');
        foreach ($pagos as $pago) {
            $guardarPago->execute([$idord, $pago['metodo'], $pago['monto'], $pago['num_operacion']]);
        }

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
                round($item['precio'] * $item['quantity'], 2),
            ]);

            $descontar->execute([$item['quantity'], $item['idprod'], $item['quantity']]);

            // si otra venta se adelanto no afecta ninguna fila, se aborta
            if ($descontar->rowCount() === 0) {
                $connect->rollBack();
                avisar_y_redirigir('Stock insuficiente', 'El stock de "' . $item['nomprd'] . '" cambió mientras se procesaba la venta.', 'error', 'cart.php');
                return;
            }

            registrar_movimiento($connect, $item['idprod'], $user_id, 'Venta', -(int)$item['quantity'], $idord);
        }

        if ($original) {
            $connect->prepare('UPDATE orders SET motivo_anulacion = ? WHERE idord = ?')
                    ->execute(['Modificada: reemplazada por la venta ' . $comprobante, $reemplaza]);
        }

        $connect->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$user_id]);

        $connect->commit();
        unset($_SESSION['modifica_venta']);

        $texto = 'Comprobante ' . $comprobante . '. El stock quedó actualizado.';
        if ($original) {
            $texto = 'Se anuló la venta ' . formato_comprobante($original->serie, $original->numero) . ' y se emitió ' . $comprobante . ' en su reemplazo.';
        }
        avisar_y_redirigir($tipc . ' registrada', $texto, 'success', 'mostrar.php');

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al registrar la venta: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo registrar la venta. No se modificó nada.', 'error', 'cart.php');
    }
}
