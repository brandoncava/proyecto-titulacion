<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('compras');

if (isset($_POST['update_qty'])) {

    csrf_validar();

    $idcpr    = filter_input(INPUT_POST, 'prdt', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'p_qty', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    // costo unitario sin igv
    $cost     = filter_input(INPUT_POST, 'p_cost', FILTER_VALIDATE_FLOAT);

    if (!$idcpr || !$quantity || $cost === false || $cost === null || $cost <= 0) {
        avisar_y_redirigir('Error!', 'Indica una cantidad válida (1 o más) y un costo mayor que 0.', 'error', '../compra/cart.php');
        return;
    }

    // solo lineas del carrito propio
    $sql = $connect->prepare('UPDATE cart_purchase SET quantity = ?, price = ? WHERE idcpr = ? AND user_id = ? LIMIT 1');
    $sql->execute([$quantity, round($cost, 2), $idcpr, (int)$_SESSION['id']]);

    avisar_y_redirigir('¡Actualizado!', 'Actualizado correctamente', 'success', '../compra/cart.php');
}
