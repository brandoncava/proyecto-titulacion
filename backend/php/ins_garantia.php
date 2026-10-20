<?php
// registra la orden de recepcion por garantia (estado inicial: Ingresado)
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['ins_garantia'])) {

    requiere_permiso_api('garantias');
    csrf_validar();

    $user_id  = (int)$_SESSION['id'];
    $idord    = filter_input(INPUT_POST, 'idord', FILTER_VALIDATE_INT) ?: null;
    $idprod   = filter_input(INPUT_POST, 'idprod', FILTER_VALIDATE_INT) ?: 0;
    $nombre   = mb_substr(trim($_POST['cliente_nombre'] ?? ''), 0, 150);
    $doc      = preg_replace('/\D/', '', (string)($_POST['cliente_doc'] ?? ''));
    $tel      = mb_substr(preg_replace('/[^\d+ ]/', '', (string)($_POST['cliente_tel'] ?? '')), 0, 15);
    $serie    = mb_substr(trim($_POST['numero_serie'] ?? ''), 0, 60);
    $fisico   = mb_substr(trim($_POST['estado_fisico'] ?? ''), 0, 255);
    $acces    = mb_substr(trim($_POST['accesorios'] ?? ''), 0, 255);
    $falla    = mb_substr(trim($_POST['falla'] ?? ''), 0, 2000);
    $volver   = '../garantias/nuevo.php' . ($idord ? '?idord=' . $idord : '?manual=1');

    if ($nombre === '' || $fisico === '' || $falla === '') {
        avisar_y_redirigir('Faltan datos', 'Completa el cliente, el estado físico y la falla reportada.', 'warning', $volver);
        return;
    }
    if ($doc !== '' && !validar_dni($doc) && !validar_ruc($doc)) {
        avisar_y_redirigir('Documento no válido', 'El DNI debe tener 8 dígitos o el RUC 11 con dígito verificador correcto.', 'warning', $volver);
        return;
    }

    $producto = $connect->prepare('SELECT idprod, garantia_meses FROM productos WHERE idprod = ?');
    $producto->execute([$idprod]);
    $producto = $producto->fetch();
    if (!$producto) {
        avisar_y_redirigir('Falta el producto', 'Elige el producto que falla.', 'warning', $volver);
        return;
    }

    $idcli = null;
    if ($idord) {
        // venta del sistema: la fecha y el comprobante salen de la venta
        $venta = $connect->prepare('SELECT idord, idcli, placed_on, serie, numero FROM orders WHERE idord = ? AND anulada = 0');
        $venta->execute([$idord]);
        $venta = $venta->fetch();
        if (!$venta) {
            avisar_y_redirigir('Venta no válida', 'La venta no existe o fue anulada.', 'error', '../garantias/nuevo.php');
            return;
        }
        // si la venta tiene detalle, el producto tiene que ser uno de los vendidos
        $tieneDetalle = $connect->prepare('SELECT COUNT(*) AS total, SUM(idprod = ?) AS esta FROM orders_detalle WHERE idord = ?');
        $tieneDetalle->execute([$idprod, $idord]);
        $det = $tieneDetalle->fetch();
        if ((int)$det->total > 0 && (int)$det->esta === 0) {
            avisar_y_redirigir('Producto no válido', 'Ese producto no está en la venta elegida.', 'warning', $volver);
            return;
        }
        $fechaCompra = substr($venta->placed_on, 0, 10);
        $comprobante = formato_comprobante($venta->serie, $venta->numero);
        $idcli = $venta->idcli ? (int)$venta->idcli : null;
    } else {
        $fechaCompra = trim($_POST['fecha_compra'] ?? '');
        $f = DateTime::createFromFormat('Y-m-d', $fechaCompra);
        if (!$f || $f->format('Y-m-d') !== $fechaCompra || $fechaCompra > date('Y-m-d')) {
            avisar_y_redirigir('Fecha no válida', 'Indica la fecha de compra (no puede ser futura).', 'warning', $volver);
            return;
        }
        $comprobante = mb_substr(strtoupper(trim($_POST['comprobante'] ?? '')), 0, 20) ?: null;
        if ($doc !== '') {
            $cli = $connect->prepare('SELECT idcli FROM clientes WHERE nudoc = ? LIMIT 1');
            $cli->execute([$doc]);
            $idcli = $cli->fetchColumn() ?: null;
        }
    }

    $vence   = date('Y-m-d', strtotime($fechaCompra . ' +' . (int)$producto->garantia_meses . ' months'));
    $vigente = $vence >= date('Y-m-d');

    if (!$vigente && empty($_POST['fuera_garantia'])) {
        avisar_y_redirigir('Garantía vencida', 'La garantía venció el ' . date('d/m/Y', strtotime($vence)) . '. Marca la casilla para registrarla de todas formas.', 'warning', $volver);
        return;
    }

    try {
        $connect->beginTransaction();

        $connect->prepare(
            'INSERT INTO garantias (idord, idprod, idcli, cliente_nombre, cliente_doc, cliente_tel, comprobante, fecha_compra, vence, vigente,
                                    numero_serie, estado_fisico, accesorios, falla, estado, fecha_ingreso, user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
        )->execute([
            $idord, $idprod, $idcli, $nombre, $doc ?: null, $tel ?: null, $comprobante, $fechaCompra, $vence, $vigente ? 1 : 0,
            $serie ?: null, $fisico, $acces ?: null, $falla, 'Ingresado', $user_id,
        ]);
        $idgar = (int)$connect->lastInsertId();

        $connect->prepare('INSERT INTO garantias_historial (idgar, estado, comentario, user_id, fecha) VALUES (?, ?, ?, ?, NOW())')
                ->execute([$idgar, 'Ingresado', $vigente ? 'Producto recibido en tienda' : 'Producto recibido en tienda (garantía vencida)', $user_id]);

        $connect->commit();

        avisar_y_redirigir('Orden registrada', 'Orden ' . formato_garantia($idgar) . ' creada. Imprímela y entrégale una copia al cliente.', 'success', '../garantias/ver.php?id=' . $idgar);

    } catch (PDOException $e) {
        if ($connect->inTransaction()) {
            $connect->rollBack();
        }
        error_log('Error al registrar la garantia: ' . $e->getMessage());
        avisar_y_redirigir('Error', 'No se pudo registrar la orden de garantía.', 'error', $volver);
    }
}
