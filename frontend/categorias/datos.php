<?php
// pagina del listado de categorias (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('catalogos');

tabla_paginada(
    $connect,
    'c.idcate, c.nocate, (SELECT COUNT(*) FROM productos p WHERE p.idcate = c.idcate AND p.state = 1) AS productos',
    'FROM categoria c WHERE c.state = 1',
    [],
    [0 => 'c.idcate', 1 => 'c.nocate', 2 => 'productos', 'defecto' => 'c.idcate DESC'],
    ['c.nocate'],
    fn($d) => [
        (string)(int)$d->idcate,
        '<b>' . e($d->nocate) . '</b>',
        (string)(int)$d->productos,
        accion_enlace('editar.php?id=' . (int)$d->idcate, 'la-pen', 'Editar')
            . accion_baja('idcate', $d->idcate, 'delete_category', '¿Dar de baja esta categoría?'),
    ]
);
