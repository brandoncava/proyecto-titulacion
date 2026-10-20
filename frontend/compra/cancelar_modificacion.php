<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/Conexion.php';

requiere_permiso('compras');

// deja la compra como estaba y vacia el carrito que se cargo con ella
if (isset($_SESSION['modifica_compra'])) {
    unset($_SESSION['modifica_compra']);
    $connect->prepare('DELETE FROM cart_purchase WHERE user_id = ?')->execute([(int)$_SESSION['id']]);
}

header('Location: mostrar.php');
exit;
