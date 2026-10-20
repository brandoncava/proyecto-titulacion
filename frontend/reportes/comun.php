<?php
// comun a los reportes: rango de fechas (GET desde/hasta), atajos y pestañas.
// Lo incluyen ventas.php, compras.php, productos.php y clientes.php despues de requiere_permiso('reportes').
if (!function_exists('requiere_permiso')) {
    exit;
}

// fecha Y-m-d valida o el valor por defecto
function fecha_get($campo, $defecto)
{
    $v = $_GET[$campo] ?? '';
    $f = DateTime::createFromFormat('Y-m-d', $v);
    return ($f && $f->format('Y-m-d') === $v) ? $v : $defecto;
}

// valor de un filtro GET si esta entre las opciones permitidas
function opcion_get($campo, $opciones, $defecto)
{
    $v = (string)($_GET[$campo] ?? '');
    return array_key_exists($v, $opciones) ? $v : $defecto;
}

$hoy   = date('Y-m-d');
$desde = fecha_get('desde', date('Y-01-01'));
$hasta = fecha_get('hasta', $hoy);
if ($desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

// el rango incluye el dia "hasta" completo
$rango = ['desde' => $desde . ' 00:00:00', 'hasta' => date('Y-m-d', strtotime($hasta . ' +1 day')) . ' 00:00:00'];

$atajos = [
    'Hoy'      => [$hoy, $hoy],
    'Este mes' => [date('Y-m-01'), $hoy],
    'Este año' => [date('Y-01-01'), $hoy],
];

// enlace de un atajo conservando los demas filtros
function url_atajo($d, $h)
{
    return '?' . e(http_build_query(array_merge($_GET, ['desde' => $d, 'hasta' => $h])));
}

function pestanas_reportes($actual)
{
    $pestanas = [
        'ventas'    => ['ventas.php', 'la-receipt', 'Ventas'],
        'compras'   => ['compras.php', 'la-store', 'Compras'],
        'productos' => ['productos.php', 'la-box', 'Productos'],
        'clientes'  => ['clientes.php', 'la-user-friends', 'Clientes'],
    ];
    echo '<nav class="pestanas-reportes">';
    foreach ($pestanas as $clave => [$url, $icono, $texto]) {
        echo '<a href="' . $url . '"' . ($clave === $actual ? ' class="activa"' : '') . '><span class="las ' . $icono . '"></span> ' . $texto . '</a>';
    }
    echo '</nav>';
}
