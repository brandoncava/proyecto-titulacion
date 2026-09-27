<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('ventas');

if (isset($_POST['add_to_cart'])) {

    csrf_validar();

    // usuario de la sesion y producto/precio de la bd, no del form
    $user_id  = (int)$_SESSION['id'];
    $idprod   = filter_input(INPUT_POST, 'prdt', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'p_qty', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$idprod || !$quantity) {
        avisar_y_redirigir('Error!', 'Indica una cantidad válida (1 o más).', 'error', 'nuevo.php');
        return;
    }

    $buscar = $connect->prepare('SELECT idprod, nomprd, precio, stock FROM productos WHERE idprod = ? AND state = 1');
    $buscar->execute([$idprod]);
    $producto = $buscar->fetch();

    if (!$producto) {
        avisar_y_redirigir('Error!', 'El producto no existe o está dado de baja.', 'error', 'nuevo.php');
        return;
    }

    if ($quantity > (int)$producto->stock) {
        avisar_y_redirigir('Stock insuficiente', 'Solo hay ' . (int)$producto->stock . ' unidades de "' . $producto->nomprd . '".', 'warning', 'nuevo.php');
        return;
    }

    $repetido = $connect->prepare('SELECT 1 FROM cart WHERE idprod = ? AND user_id = ?');
    $repetido->execute([$idprod, $user_id]);

    if ($repetido->fetch()) {
        avisar_y_redirigir('Error!', 'Ya está agregado. Cambia la cantidad desde el carrito.', 'error', 'cart.php');
        return;
    }

    $insertar = $connect->prepare('INSERT INTO cart (user_id, idprod, name, price, quantity) VALUES (?, ?, ?, ?, ?)');
    $insertar->execute([$user_id, $idprod, $producto->nomprd, $producto->precio, $quantity]);

    avisar_y_redirigir('¡Registrado!', 'Agregado correctamente', 'success', 'cart.php');
}
