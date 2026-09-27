<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('compras');
  

  if(isset($_POST['update_qty']))
{

    csrf_validar();

    $idcpr = $_POST['prdt'];
    $quantity = $_POST['p_qty'];
    

    try {

        $query = "UPDATE cart_purchase SET quantity=:quantity WHERE idcpr=:idcpr LIMIT 1";
        $statement = $connect->prepare($query);

        $data = [
            ':quantity' => $quantity,
            ':idcpr' => $idcpr
        ];
        $query_execute = $statement->execute($data);

        if($query_execute)
        {
            echo '<script type="text/javascript">
swal("¡Actualizado!", "Actualizado correctamente", "success").then(function() {
            window.location = "../compra/cart.php";
        });
        </script>';
            exit(0);
        }
        else
        {
           echo '<script type="text/javascript">
swal("Error!", "Error al actualizar", "error").then(function() {
            window.location = "../compra/cart.php";
        });
        </script>';
            exit(0);
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


?>