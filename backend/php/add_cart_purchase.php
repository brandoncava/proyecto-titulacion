<?php
require_once __DIR__ . '/../config/app.php';

requiere_permiso_api('compras');

if (isset($_POST['add_to_cart_purchase'])) {

    csrf_validar();

    // usuario de la sesion, producto de la bd, el costo lo escribe el usuario
    $user_id  = (int)$_SESSION['id'];
    $idprod   = filter_input(INPUT_POST, 'prdt', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'p_qty', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    // costo unitario sin igv (el de la factura del proveedor)
    $cost     = filter_input(INPUT_POST, 'p_cost', FILTER_VALIDATE_FLOAT);

    if (!$idprod || !$quantity) {
        avisar_y_redirigir('Error!', 'Indica una cantidad válida (1 o más).', 'error', 'nuevo.php');
        return;
    }

    if ($cost === false || $cost === null || $cost <= 0) {
        avisar_y_redirigir('Error!', 'Indica el costo unitario sin IGV (mayor que 0).', 'error', 'nuevo.php');
        return;
    }
    $cost = round($cost, 2);

    $buscar = $connect->prepare('SELECT idprod, nomprd FROM productos WHERE idprod = ? AND state = 1');
    $buscar->execute([$idprod]);
    $producto = $buscar->fetch();

    if (!$producto) {
        avisar_y_redirigir('Error!', 'El producto no existe o está dado de baja.', 'error', 'nuevo.php');
        return;
    }

    carrito_agregar($connect, 'compra', $user_id, $idprod, $producto->nomprd, $cost, $quantity);
}
