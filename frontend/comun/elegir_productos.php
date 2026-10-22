<?php
// lista de productos para armar el carrito (ventas/nuevo.php y compra/nuevo.php).
// La pagina define $tipoCarrito ('venta' o 'compra') y ya incluyo layout/cabecera.php con $tablas = true.
// Es una TablaPaginada: las filas las trae elegir_productos_datos.php de a una pagina.
// Agregar y cambiar cantidades lo hacen backend/php/add_cart(_purchase).php y upd_cart(_purchase).php
if (!isset($tipoCarrito) || !isset(CARRITOS[$tipoCarrito])) {
    exit;
}

require_once __DIR__ . '/../../backend/config/app.php';

$esCompra  = $tipoCarrito === 'compra';
$enCarrito = count(carrito_lineas($connect, $tipoCarrito, (int)$_SESSION['id']));
$modifica  = $_SESSION[CARRITOS[$tipoCarrito]['sesion']] ?? null;
?>

            <div class="page-content">
<?php if ($modifica): ?>
<?php include __DIR__ . '/aviso_modificacion.php'; ?>
<?php endif; ?>

                <div class="cabecera-form">
                    <div>
                        <h1><?php echo $esCompra ? 'Nueva compra' : 'Nueva venta'; ?></h1>
                        <p><?php echo $esCompra ? 'Agrega los productos que llegaron con su costo unitario sin IGV' : 'Busca los productos y agrégalos al carrito'; ?></p>
                    </div>
                    <div class="cabecera-form-botones">
                        <div id="buscador-tabla" class="buscador-cabecera" data-placeholder="Buscar producto, código o marca"></div>
                        <a href="cart.php" class="btn-primario"><span class="las la-shopping-cart"></span> <span class="btn-texto">Ver carrito (<b id="contador-carrito"><?php echo $enCarrito; ?></b>)</span></a>
                    </div>
                </div>

                <div class="records table-responsive">
                    <table width="100%" id="example" data-tabla-paginada="../comun/elegir_productos_datos.php?tipo=<?php echo $tipoCarrito; ?>">
                        <thead>
                            <tr>
                                <th data-orderable="false">Foto</th>
                                <th><span class="las la-sort"></span>Código</th>
                                <th><span class="las la-sort"></span>Producto</th>
                                <th><span class="las la-sort"></span>Marca</th>
                                <th><span class="las la-sort"></span><?php echo $esCompra ? 'Precio venta' : 'Precio'; ?></th>
<?php if ($esCompra): ?>
                                <th><span class="las la-sort"></span>Último costo (sin IGV)</th>
<?php endif; ?>
                                <th><span class="las la-sort"></span>Stock</th>
                                <th data-orderable="false">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
