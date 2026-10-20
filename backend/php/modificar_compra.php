<?php
// "Modificar compra": carga la compra en el carrito de compras del usuario.
// Al finalizar (add_check_purchase.php) se edita la misma compra y el stock se ajusta con la diferencia.
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['modificar_compra'])) {

    requiere_permiso_api('compras_anular');
    csrf_validar();

    $idordpur = filter_input(INPUT_POST, 'idordpur', FILTER_VALIDATE_INT);
    $user_id  = (int)$_SESSION['id'];

    $buscar = $connect->prepare(
        'SELECT idordpur, anulada, idprov, method, tipc, serie_doc, numero_doc, fecha_emision, igv
           FROM orders_purchase WHERE idordpur = ?'
    );
    $buscar->execute([$idordpur ?: 0]);
    $compra = $buscar->fetch();

    if (!$compra || (int)$compra->anulada === 1) {
        avisar_y_redirigir('No se puede modificar', 'La compra no existe o ya está anulada.', 'error', '../compra/mostrar.php');
        return;
    }

    $detalle = $connect->prepare('SELECT idprod, nombre, precio, cantidad FROM orders_purchase_detalle WHERE idordpur = ?');
    $detalle->execute([$compra->idordpur]);
    $lineas = $detalle->fetchAll(PDO::FETCH_ASSOC);

    if (!$lineas) {
        avisar_y_redirigir('No se puede modificar', 'Es una compra antigua sin detalle de productos. Anúlala y regístrala de nuevo.', 'warning', '../compra/mostrar.php');
        return;
    }

    // el carrito de compras se reemplaza por la compra, con sus costos
    $connect->beginTransaction();
    $connect->prepare('DELETE FROM cart_purchase WHERE user_id = ?')->execute([$user_id]);
    $insertar = $connect->prepare('INSERT INTO cart_purchase (user_id, idprod, name, price, quantity) VALUES (?, ?, ?, ?, ?)');
    foreach ($lineas as $l) {
        $insertar->execute([$user_id, $l['idprod'], $l['nombre'], $l['precio'], $l['cantidad']]);
    }
    $connect->commit();

    $_SESSION['modifica_compra'] = [
        'idordpur'      => (int)$compra->idordpur,
        'idprov'        => (int)$compra->idprov,
        'method'        => (string)$compra->method,
        'tipc'          => (string)$compra->tipc,
        'serie_doc'     => (string)$compra->serie_doc,
        'numero_doc'    => (string)$compra->numero_doc,
        'fecha_emision' => (string)$compra->fecha_emision,
        'con_igv'       => (float)$compra->igv > 0,
    ];

    header('Location: ../compra/cart.php');
    exit;
}
