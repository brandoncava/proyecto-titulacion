<?php
require_once __DIR__ . "/../../backend/config/auth.php";

requiere_sesion();

 require '../../backend/config/Conexion.php';
 echo '<option value="">Seleccione</option>';
 $stmt = $connect->prepare('SELECT * FROM `proveedores` WHERE state = 1 ORDER BY idprov ASC');

  $stmt->execute();


  while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            ?>
            <option value="<?php echo e($idprov); ?>"><?php echo e($nomprv); ?></option>

            <?php
        }

  ?>


