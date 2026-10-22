<?php
// pagina de productos para la lista de Nueva venta / Nueva compra (TablaPaginada, responde json).
// ?tipo=venta o ?tipo=compra
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/elegir_productos_fila.php';

$tipo = ($_GET['tipo'] ?? '') === 'compra' ? 'compra' : 'venta';
requiere_permiso_api(CARRITOS[$tipo]['permiso']);

$esCompra    = $tipo === 'compra';
$yaEnCarrito = array_column(carrito_lineas($connect, $tipo, (int)$_SESSION['id']), null, 'idprod');
// al modificar una venta, sus unidades vuelven al confirmar: cuentan como disponibles
$devuelve    = $esCompra ? [] : unidades_venta_modificada($connect);

// columnas: foto, codigo, producto, marca, precio, [ultimo costo], stock, cantidad
$orden = [1 => 'p.codpro', 2 => 'p.nomprd', 3 => 'm.nomarc', 4 => 'p.precio', 'defecto' => 'p.idprod DESC'];
if ($esCompra) {
    $orden[5] = 'p.precio_compra';
    $orden[6] = 'p.stock';
} else {
    $orden[5] = 'p.stock';
}

tabla_paginada(
    $connect,
    'p.idprod, p.codpro, p.nomprd, p.foto, p.precio, p.precio_compra, p.stock, m.nomarc',
    'FROM productos p INNER JOIN marca m ON m.idmar = p.idmar WHERE p.state = 1',
    [],
    $orden,
    ['p.codpro', 'p.nomprd', 'm.nomarc'],
    function ($d) use ($esCompra, $yaEnCarrito, $devuelve) {
        $disponible = (int)$d->stock + ($devuelve[$d->idprod] ?? 0);
        return fila_elegir_producto($d, $esCompra, $yaEnCarrito[$d->idprod] ?? null, $disponible);
    }
);
