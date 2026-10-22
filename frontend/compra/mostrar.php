<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('compras');

$seccion = 'compras';
$migas = 'Compras';
$tablas = true;
$listado = [
    'titulo'    => 'Compras',
    'subtitulo' => 'Mercadería recibida con el comprobante del proveedor',
    'buscar'    => 'Buscar proveedor o comprobante',
    'nuevo'     => ['Nueva compra', 'nuevo.php'],
    'datos'     => 'datos.php',
    'columnas'  => ['#', 'Comprobante', 'Fecha', 'Proveedor', 'Productos', 'Total', ['Acciones', false]],
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
<script type="text/javascript">
// pide el motivo antes de anular (lo usa el boton de anular de cada fila)
function anularCompra(form){
    var motivo = prompt('Motivo de la anulación de esta compra:');
    if (motivo === null) return false;
    motivo = motivo.trim();
    if (motivo === '') { alert('Debes indicar un motivo.'); return false; }
    form.motivo.value = motivo;
    return confirm('¿Anular esta compra? Se descontará el stock del inventario y no se puede deshacer.');
}
</script>
<?php include_once '../../backend/php/anular_compra.php'; ?>
<?php include_once '../../backend/php/modificar_compra.php'; ?>
</body>
</html>
