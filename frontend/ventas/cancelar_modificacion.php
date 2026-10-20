<?php
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/Conexion.php';

requiere_permiso('ventas');

// deja la venta original como estaba y vacia el carrito que se cargo con ella
if (isset($_SESSION['modifica_venta'])) {
    unset($_SESSION['modifica_venta']);
    $connect->prepare('DELETE FROM cart WHERE user_id = ?')->execute([(int)$_SESSION['id']]);
}

header('Location: mostrar.php');
exit;
