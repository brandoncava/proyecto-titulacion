<?php
// perfiles del sistema y que puede hacer cada uno (editar solo $PERMISOS)

define('ROL_ADMINISTRADOR', 1);
define('ROL_CAJERO', 2);
define('ROL_EMPLEADO', 3);

$ROLES = [
    ROL_ADMINISTRADOR => 'Administrador',
    ROL_CAJERO => 'Cajero',
    ROL_EMPLEADO => 'Empleado',
];

// claves por defecto al crear un usuario nuevo, se pueden cambiar despues
$PASSWORDS_POR_ROL = [
    ROL_ADMINISTRADOR => 'Admin2026!',
    ROL_CAJERO => 'Cajero2026!',
    ROL_EMPLEADO => 'Empleado2026!',
];

function password_predeterminada($rol)
{
    global $PASSWORDS_POR_ROL;
    return $PASSWORDS_POR_ROL[(int)$rol] ?? '';
}

// ventas/compras/productos.../reportes: cada uno controla su seccion del menu
$PERMISOS = [
    ROL_ADMINISTRADOR => [
        'ventas', 'ventas_anular', 'compras', 'productos', 'productos_ed', 'productos_el',
        'clientes', 'proveedores', 'catalogos', 'usuarios', 'reportes',
    ],
    ROL_CAJERO => [
        'ventas', 'productos', 'clientes',
    ],
    ROL_EMPLEADO => [
        'compras', 'productos', 'productos_ed', 'proveedores', 'catalogos',
    ],
];

function nombre_rol($rol)
{
    global $ROLES;
    return $ROLES[(int)$rol] ?? 'Desconocido';
}

function rol_tiene_permiso($rol, $permiso)
{
    global $PERMISOS;
    return in_array($permiso, $PERMISOS[(int)$rol] ?? [], true);
}

// a donde entra cada perfil despues de loguearse
function panel_inicial($rol)
{
    switch ((int)$rol) {
        case ROL_ADMINISTRADOR: return 'administrador/escritorio.php';
        case ROL_CAJERO: return 'ventas/mostrar.php';
        case ROL_EMPLEADO: return 'productos/mostrar.php';
        default: return 'login.php';
    }
}

function rol_valido($rol)
{
    global $ROLES;
    return array_key_exists((int)$rol, $ROLES);
}
