<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_supplier'])) {

    requiere_permiso_api('proveedores');
    csrf_validar();

    dar_de_baja($connect, 'proveedores', 'idprov', filter_input(INPUT_POST, 'idprov', FILTER_VALIDATE_INT),
                ['El proveedor', 'dado'], '../proveedores/mostrar.php');
}
