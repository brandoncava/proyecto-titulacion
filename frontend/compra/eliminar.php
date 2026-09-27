<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/Conexion.php';

requiere_permiso('compras');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    header('Location: cart.php');
    exit;
}

// Solo se puede eliminar una linea del propio carrito de compra
$sentencia = $connect->prepare('DELETE FROM cart_purchase WHERE idcpr = ? AND user_id = ?');
$resultado = $sentencia->execute([$id, $_SESSION['id']]);

if ($resultado) {
    header('Location: cart.php');
    exit;
}

avisar_y_redirigir(
    'Error!',
    'No se pudo eliminar el producto del carrito. Comuniquese con el administrador.',
    'error',
    'cart.php'
);
