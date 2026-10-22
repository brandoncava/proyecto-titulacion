<?php
// pagina del listado de productos (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('productos');

$editar   = puede('productos_ed');
$eliminar = puede('productos_el');

tabla_paginada(
    $connect,
    'p.idprod, p.codpro, p.nomprd, p.foto, p.precio, p.stock, p.modelo, m.nomarc, c.nocate',
    'FROM productos p INNER JOIN marca m ON m.idmar = p.idmar INNER JOIN categoria c ON c.idcate = p.idcate WHERE p.state = 1',
    [],
    [1 => 'p.codpro', 2 => 'p.nomprd', 3 => 'm.nomarc', 4 => 'p.modelo', 5 => 'c.nocate', 6 => 'p.precio', 7 => 'p.stock', 'defecto' => 'p.idprod DESC'],
    ['p.codpro', 'p.nomprd', 'm.nomarc', 'p.modelo', 'c.nocate'],
    function ($d) use ($editar, $eliminar) {
        $stock = (int)$d->stock;
        $aviso = $stock <= 0 ? ' <span class="chip peligro">Sin stock</span>'
               : ($stock <= STOCK_BAJO ? ' <span class="chip alerta">Stock bajo</span>' : '');
        return [
            '<img src="../../backend/img/subidas/' . e($d->foto) . '" alt="" width="50" class="miniatura">',
            e($d->codpro),
            '<b>' . e($d->nomprd) . '</b>',
            e($d->nomarc),
            e($d->modelo),
            e($d->nocate),
            '<span class="sin-salto">S/ ' . number_format($d->precio, 2) . '</span>',
            '<span class="sin-salto">' . $stock . $aviso . '</span>',
            ($editar ? accion_enlace('editar.php?id=' . (int)$d->idprod, 'la-pen', 'Editar (datos, imagen, precio y stock)') : '')
                . ($eliminar ? accion_baja('idprod', $d->idprod, 'delete_product', '¿Dar de baja este producto?') : ''),
        ];
    }
);
