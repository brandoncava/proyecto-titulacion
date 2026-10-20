<?php
// acciones sueltas del carrito: quitar una linea o cancelar la modificacion en curso.
// La pagina (ventas/ o compra/) define $tipoCarrito y $accion ('quitar' o 'cancelar') y lo incluye.
require_once __DIR__ . '/../../backend/config/auth.php';
require_once __DIR__ . '/../../backend/config/Conexion.php';

if (!isset($tipoCarrito, $accion) || !isset(CARRITOS[$tipoCarrito])) {
    exit;
}

requiere_permiso(CARRITOS[$tipoCarrito]['permiso']);
$user_id = (int)$_SESSION['id'];

if ($accion === 'quitar') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id && !carrito_quitar($connect, $tipoCarrito, $id, $user_id)) {
        avisar_y_redirigir('Error!', 'No se pudo quitar el producto del carrito. Comuníquese con el administrador.', 'error', 'cart.php');
        exit;
    }
    header('Location: cart.php');
    exit;
}

// cancelar: la venta/compra original queda como estaba y se vacia el carrito que se cargo con ella
$sesion = CARRITOS[$tipoCarrito]['sesion'];
if (isset($_SESSION[$sesion])) {
    unset($_SESSION[$sesion]);
    carrito_vaciar($connect, $tipoCarrito, $user_id);
}
header('Location: mostrar.php');
exit;
