<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('productos_ed');
if(isset($_POST['upd_prodct_stock']))
{

    csrf_validar();

    $idprod = filter_input(INPUT_POST, 'prdid', FILTER_VALIDATE_INT);
    $stock  = filter_input(INPUT_POST, 'prdstock', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if (!$idprod || $stock === false || $stock === null) {
        avisar_y_redirigir('Error!', 'Indica un stock válido (0 o más).', 'error', '../productos/mostrar.php');
        return;
    }
    
    try {

        // stock anterior, para anotar la diferencia en el kardex
        $anterior = $connect->prepare('SELECT stock FROM productos WHERE idprod = ?');
        $anterior->execute([$idprod]);
        $stock_anterior = (int)$anterior->fetchColumn();

        $query = "UPDATE productos SET stock=:stock WHERE idprod=:idprod LIMIT 1";
        $statement = $connect->prepare($query);

        $data = [
            ':stock' => $stock,
            ':idprod' => $idprod
        ];
        $query_execute = $statement->execute($data);

        if($query_execute)
        {
            if ((int)$stock !== $stock_anterior) {
                registrar_movimiento($connect, $idprod, (int)$_SESSION['id'], 'Ajuste', (int)$stock - $stock_anterior);
            }

            echo '<script type="text/javascript">
swal("Actualizado!", "Se actualizó correctamente", "success").then(function() {
            window.location = "../productos/mostrar.php";
        });
        </script>';
            exit(0);
        }
        else
        {
           echo '<script type="text/javascript">
swal("Error!", "No se pueden agregar datos,  comuníquese con el administrador ", "error").then(function() {
            window.location = "../productos/mostrar.php";
        });
        </script>';
            exit(0);
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>



