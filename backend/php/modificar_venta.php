<?php
// "Modificar venta": carga los productos de la venta en el carrito del usuario.
// Al finalizar (add_check.php) la venta original se anula y se emite otra que la reemplaza.
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['modificar_venta'])) {

    requiere_permiso_api('ventas_anular');
    csrf_validar();

    $idord   = filter_input(INPUT_POST, 'idord', FILTER_VALIDATE_INT);
    $user_id = (int)$_SESSION['id'];

    $buscar = $connect->prepare('SELECT idord, anulada, serie, numero, nomcl, doc_tipo, doc_num FROM orders WHERE idord = ?');
    $buscar->execute([$idord ?: 0]);
    $venta = $buscar->fetch();

    if (!$venta || (int)$venta->anulada === 1) {
        avisar_y_redirigir('No se puede modificar', 'La venta no existe o ya está anulada.', 'error', '../ventas/mostrar.php');
        return;
    }

    $detalle = $connect->prepare('SELECT idprod, nombre, precio, cantidad FROM orders_detalle WHERE idord = ?');
    $detalle->execute([$venta->idord]);
    $lineas = $detalle->fetchAll(PDO::FETCH_ASSOC);

    if (!$lineas) {
        avisar_y_redirigir('No se puede modificar', 'Es una venta antigua sin detalle de productos. Anúlala y registra la venta de nuevo.', 'warning', '../ventas/mostrar.php');
        return;
    }

    // el carrito se reemplaza por los productos de la venta, con su precio original
    $connect->beginTransaction();
    $connect->prepare('DELETE FROM cart WHERE user_id = ?')->execute([$user_id]);
    $insertar = $connect->prepare('INSERT INTO cart (user_id, idprod, name, price, quantity) VALUES (?, ?, ?, ?, ?)');
    foreach ($lineas as $l) {
        $insertar->execute([$user_id, $l['idprod'], $l['nombre'], $l['precio'], $l['cantidad']]);
    }
    $connect->commit();

    $_SESSION['modifica_venta'] = [
        'idord'       => (int)$venta->idord,
        'comprobante' => formato_comprobante($venta->serie, $venta->numero),
        'nomcl'       => $venta->doc_num ? '' : ($venta->nomcl === 'CLIENTES VARIOS' ? '' : $venta->nomcl),
        'doc_tipo'    => (string)$venta->doc_tipo,
        'doc_num'     => (string)$venta->doc_num,
    ];

    header('Location: ../ventas/cart.php');
    exit;
}
