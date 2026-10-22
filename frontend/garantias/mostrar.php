<?php
ob_start();
require_once __DIR__ . '/../../backend/config/app.php';

requiere_permiso('garantias');

// por defecto solo las que siguen en proceso (los filtros validos estan en datos_filtros.php)
require_once __DIR__ . '/datos_filtros.php';
$filtro = filtro_garantias($_GET['estado'] ?? '');

$opciones = '';
foreach (FILTROS_GARANTIA as $valor => $texto) {
    $opciones .= '<option value="' . e($valor) . '"' . ($valor === $filtro ? ' selected' : '') . '>' . e($texto) . '</option>';
}

$seccion = 'garantias';
$migas = 'Garantías';
$tablas = true;
$listado = [
    'titulo'    => 'Garantías',
    'subtitulo' => 'Órdenes de recepción y su trámite con la marca',
    'buscar'    => 'Buscar cliente o producto',
    'nuevo'     => ['Nueva orden de garantía', 'nuevo.php'],
    'datos'     => 'datos.php?estado=' . urlencode($filtro),
    'columnas'  => ['N° orden', 'Ingreso', 'Cliente', 'Producto', 'Compra', ['Garantía', false], 'Estado', ['Acciones', false]],
    'filtros'   => '<form method="GET" action=""><select name="estado" class="filtro-cabecera" aria-label="Estado" onchange="this.form.submit()">'
                 . $opciones . '</select></form>',
];
require __DIR__ . '/../layout/cabecera.php';
include __DIR__ . '/../layout/listado.php';
require __DIR__ . '/../layout/pie.php';
?>
</body>
</html>
