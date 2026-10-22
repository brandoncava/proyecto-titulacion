<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('ventas');

$seccion = 'ventas';
$migas = 'Ventas';
$tablas = true;
$listado = [
    'titulo'    => 'Ventas',
    'subtitulo' => 'Boletas y facturas emitidas',
    'buscar'    => 'Buscar cliente o comprobante',
    'nuevo'     => ['Nueva venta', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Comprobante', 'Fecha', 'Cliente', 'Productos', 'Total', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
<script type="text/javascript">
// pide el motivo antes de anular (lo usa el boton de anular de cada fila)
function anularVenta(form){
    var motivo = prompt('Motivo de la anulación de esta venta:');
    if (motivo === null) return false;
    motivo = motivo.trim();
    if (motivo === '') { alert('Debes indicar un motivo.'); return false; }
    form.motivo.value = motivo;
    return confirm('¿Anular esta venta? Se devolverá el stock al inventario y no se puede deshacer.');
}
</script>
<?php include_once '../../backend/php/anular_venta.php'; ?>
<?php include_once '../../backend/php/modificar_venta.php'; ?>
</body>
</html>
