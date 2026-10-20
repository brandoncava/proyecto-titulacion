<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_product'])) {

    requiere_permiso_api('productos_el');
    csrf_validar();

    dar_de_baja($connect, 'productos', 'idprod', filter_input(INPUT_POST, 'idprod', FILTER_VALIDATE_INT),
                ['El producto', 'dado'], '../productos/mostrar.php');
}
