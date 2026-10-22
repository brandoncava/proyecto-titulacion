<?php
require_once __DIR__ . '/../config/app.php';

if (isset($_POST['delete_customer'])) {

    requiere_permiso_api('clientes');
    csrf_validar();

    dar_de_baja($connect, 'clientes', 'idcli', filter_input(INPUT_POST, 'idcli', FILTER_VALIDATE_INT),
                ['El cliente', 'dado'], '../clientes/mostrar.php');
}
