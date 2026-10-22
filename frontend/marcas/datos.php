<?php
// pagina del listado de marcas (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('catalogos');

tabla_paginada(
    $connect,
    'm.idmar, m.nomarc, (SELECT COUNT(*) FROM productos p WHERE p.idmar = m.idmar AND p.state = 1) AS productos',
    'FROM marca m WHERE m.state = 1',
    [],
    [0 => 'm.idmar', 1 => 'm.nomarc', 2 => 'productos', 'defecto' => 'm.idmar DESC'],
    ['m.nomarc'],
    fn($d) => [
        (string)(int)$d->idmar,
        '<b>' . e($d->nomarc) . '</b>',
        (string)(int)$d->productos,
        accion_enlace('editar.php?id=' . (int)$d->idmar, 'la-pen', 'Editar')
            . accion_baja('idmar', $d->idmar, 'delete_marca', '¿Dar de baja esta marca?'),
    ]
);
