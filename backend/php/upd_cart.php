<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('ventas');

if (isset($_POST['update_qty'])) {

    csrf_validar();

    $idv      = filter_input(INPUT_POST, 'prdt', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'p_qty', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if (!$idv || !$quantity) {
        avisar_y_redirigir('Error!', 'Indica una cantidad válida (1 o más).', 'error', '../ventas/cart.php');
        return;
    }

    // solo lineas del carrito propio
    $sql = $connect->prepare('UPDATE cart SET quantity = ? WHERE idv = ? AND user_id = ? LIMIT 1');
    $sql->execute([$quantity, $idv, (int)$_SESSION['id']]);

    avisar_y_redirigir('¡Actualizado!', 'Actualizado correctamente', 'success', '../ventas/cart.php');
}
