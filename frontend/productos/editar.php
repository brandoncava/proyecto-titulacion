<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

require '../../backend/config/Conexion.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$sentencia = $connect->prepare(
    'SELECT idprod, codpro, nomprd, desprd, foto, precio, stock, idmar, idcate, modelo, peso, garantia_meses
       FROM productos WHERE idprod = ?'
);
$sentencia->execute([$id]);
$producto = $sentencia->fetch();

$marcas = $categorias = [];
$foto = null;
if ($producto) {
    // las activas y la actual del producto aunque este dada de baja; si no, el select
    // elegiria otra y al guardar el producto cambiaria de marca o categoria sin querer
    $m = $connect->prepare('SELECT idmar, nomarc, state FROM marca WHERE state = 1 OR idmar = ? ORDER BY nomarc');
    $m->execute([$producto->idmar]);
    $marcas = $m->fetchAll();
    $c = $connect->prepare('SELECT idcate, nocate, state FROM categoria WHERE state = 1 OR idcate = ? ORDER BY nocate');
    $c->execute([$producto->idcate]);
    $categorias = $c->fetchAll();

    // datos de la imagen actual para mostrarlos junto a la vista previa
    $ruta = __DIR__ . '/../../backend/img/subidas/' . basename((string)$producto->foto);
    if ($producto->foto && is_file($ruta)) {
        $medidas = @getimagesize($ruta);
        $foto = [
            'url'     => '../../backend/img/subidas/' . rawurlencode(basename($producto->foto)),
            'nombre'  => basename($producto->foto),
            'medidas' => $medidas ? $medidas[0] . ' × ' . $medidas[1] . ' px' : '',
            'peso'    => round(filesize($ruta) / 1024) . ' KB',
        ];
    }
}

$seccion = 'productos';
$migas = 'Productos / Editar';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php if (!$producto): ?>
                <p class="alert alert-warning">No se encontró el producto.</p>
<?php else: ?>
<?php include __DIR__ . '/formulario.php'; ?>
<?php endif; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/upd_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/producto_form.js"></script>
</body>
</html>
