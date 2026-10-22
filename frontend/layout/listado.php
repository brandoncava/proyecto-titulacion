<?php
// Listado: pagina de un modulo con su TablaPaginada (encabezado con buscador y boton "Nuevo...").
// Las filas las trae $listado['datos'] (un datos.php del modulo que usa tabla_paginada()).
// La pagina define antes de incluirlo:
//   $listado = [
//       'titulo'    => 'Productos',
//       'subtitulo' => 'Catálogo con precio y stock',
//       'buscar'    => 'Buscar producto, código o marca',     texto del buscador
//       'nuevo'     => ['Nuevo producto', 'nuevo.php'],        opcional (sin permiso: null)
//       'datos'     => 'datos.php',
//       'columnas'  => ['Código', 'Producto', ['Acciones', false]],   [titulo, false] = no se ordena
//       'filtros'   => '<select ...>',                        opcional, html junto al buscador
//   ];
if (!isset($listado)) {
    exit;
}
?>
            <div class="page-content">
                <div class="cabecera-form">
                    <div>
                        <h1><?php echo e($listado['titulo']); ?></h1>
<?php if (!empty($listado['subtitulo'])): ?>
                        <p><?php echo e($listado['subtitulo']); ?></p>
<?php endif; ?>
                    </div>
                    <div class="cabecera-form-botones">
                        <?php echo $listado['filtros'] ?? ''; ?>
                        <div id="buscador-tabla" class="buscador-cabecera" data-placeholder="<?php echo e($listado['buscar'] ?? 'Buscar'); ?>"></div>
<?php if (!empty($listado['nuevo'])): ?>
                        <a href="<?php echo e($listado['nuevo'][1]); ?>" class="btn-primario"><span class="las la-plus"></span> <?php echo e($listado['nuevo'][0]); ?></a>
<?php endif; ?>
                    </div>
                </div>

                <div class="records table-responsive">
                    <table width="100%" id="example" data-tabla-paginada="<?php echo e($listado['datos']); ?>">
                        <thead>
                            <tr>
<?php foreach ($listado['columnas'] as $col): ?>
<?php [$texto, $ordena] = is_array($col) ? $col : [$col, true]; ?>
                                <th<?php echo $ordena ? '' : ' data-orderable="false"'; ?>><?php echo $ordena ? '<span class="las la-sort"></span>' : ''; ?><?php echo e($texto); ?></th>
<?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
