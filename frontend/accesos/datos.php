<?php
// pagina del listado de usuarios (TablaPaginada, responde json)
require_once __DIR__ . '/../../backend/config/app.php';
require_once __DIR__ . '/../comun/acciones_tabla.php';

requiere_permiso_api('usuarios');

tabla_paginada(
    $connect,
    'id, nombre, username, correo, rol, state',
    'FROM usuarios WHERE 1 = 1',
    [],
    [0 => 'id', 1 => 'nombre', 2 => 'correo', 3 => 'rol', 4 => 'state', 'defecto' => 'id DESC'],
    ['nombre', 'username', 'correo'],
    fn($d) => [
        (string)(int)$d->id,
        celda_doble($d->nombre, $d->username),
        e($d->correo),
        e(nombre_rol($d->rol)),
        (int)$d->state === 1 ? '<span class="chip ok">Activo</span>' : '<span class="chip">Inactivo</span>',
        accion_enlace('editar.php?id=' . (int)$d->id, 'la-pen', 'Editar')
            . accion_enlace('cambiar.php?id=' . (int)$d->id, 'la-key', 'Cambiar contraseña'),
    ]
);
