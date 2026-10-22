<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('series');

$seccion = 'series';
$migas = 'Series';
$tablas = true;
$listado = [
    'titulo'    => 'Series',
    'subtitulo' => 'Numeración de boletas y facturas (una activa por comprobante)',
    'buscar'    => 'Buscar serie o comprobante',
    'nuevo'     => ['Nueva serie', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Comprobante', 'Serie', 'Último número', 'Emitidos', 'Estado', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
<?php include_once '../../backend/php/upd_serie.php'; ?>
</body>
</html>
