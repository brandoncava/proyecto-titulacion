<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

require '../../backend/config/Conexion.php';

// producto nuevo: mismo formulario que editar.php, vacio
$producto = null;
$foto = null;
$marcas = $connect->query('SELECT idmar, nomarc, state FROM marca WHERE state = 1 ORDER BY nomarc')->fetchAll();
$categorias = $connect->query('SELECT idcate, nocate, state FROM categoria WHERE state = 1 ORDER BY nocate')->fetchAll();

$seccion = 'productos';
$migas = 'Productos / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>

            <div class="page-content">
<?php include __DIR__ . '/formulario.php'; ?>
            </div>

<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/producto_form.js"></script>
</body>
</html>
