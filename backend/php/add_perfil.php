<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/Conexion.php';

requiere_permiso_api('clientes');
if(isset($_POST['add_perfil']))
{

    csrf_validar();

    $idcli = $_POST['clid'];
    $username = $_POST['usrcl'];
    $password = password_hash($_POST['pswcl'], PASSWORD_DEFAULT);
    $rol = $_POST['rolcl'];
   
    
    try {

        $query = "UPDATE clientes SET username  =:username, password=:password,rol=:rol  WHERE idcli=:idcli LIMIT 1";
        $statement = $connect->prepare($query);

        $data = [
         
            ':username' => $username,
            ':password' => $password,
            ':rol' => $rol,
            ':idcli' => $idcli
        ];
        $query_execute = $statement->execute($data);

        if($query_execute)
        {
            echo '<script type="text/javascript">
swal("¡Registrado!", "Perfil creado correctamente", "success").then(function() {
            window.location = "../clientes/mostrar.php";
        });
        </script>';
            exit(0);
        }
        else
        {
           echo '<script type="text/javascript">
swal("Error!", "No se pueden agregar datos,  comuníquese con el administrador ", "error").then(function() {
            window.location = "../clientes/nuevo.php";
        });
        </script>';
            exit(0);
        }

    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
?>



