<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

if (isset($_POST['delete_category'])) {

    requiere_permiso_api('catalogos');
    csrf_validar();

    dar_de_baja($connect, 'categoria', 'idcate', filter_input(INPUT_POST, 'idcate', FILTER_VALIDATE_INT),
                ['La categoría', 'dada'], '../categorias/mostrar.php',
                ['SELECT COUNT(*) FROM productos WHERE idcate = ? AND state = 1',
                 'La categoría la usan {n} producto(s). Cámbiales la categoría primero.']);
}
