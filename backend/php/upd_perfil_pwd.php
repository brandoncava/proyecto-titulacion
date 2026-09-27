<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('clientes');
if(isset($_POST['upd_perfil_pwd']))
{

    csrf_validar();

    $idcli = $_POST['clid'];
    $password = password_hash($_POST['pswcl'], PASSWORD_DEFAULT);
    
    
    try {

        $query = "UPDATE clientes SET password=:password WHERE idcli=:idcli LIMIT 1";
        $statement = $connect->prepare($query);

        $data = [
            ':password' => $password,
            ':idcli' => $idcli
        ];
        $query_execute = $statement->execute($data);

        if($query_execute)
        {
            echo '<script type="text/javascript">
swal("¡Actualizado!", "Contraseña actualizada correctamente", "success").then(function() {
            window.location = "../clientes/mostrar.php";
        });
        </script>';
            exit(0);
        }
        else
        {
           echo '<script type="text/javascript">
swal("Error!", "No se pueden agregar datos,  comuníquese con el administrador ", "error").then(function() {
            window.location = "../clientes/mostrar.php";
        });
        </script>';
            exit(0);
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>



