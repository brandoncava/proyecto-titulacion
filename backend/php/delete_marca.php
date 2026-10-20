<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_marca'])) {

    requiere_permiso_api('catalogos');
    csrf_validar();

    dar_de_baja($connect, 'marca', 'idmar', filter_input(INPUT_POST, 'idmar', FILTER_VALIDATE_INT),
                ['La marca', 'dada'], '../marcas/mostrar.php',
                ['SELECT COUNT(*) FROM productos WHERE idmar = ? AND state = 1',
                 'La marca la usan {n} producto(s). Cámbiales la marca primero.']);
}
