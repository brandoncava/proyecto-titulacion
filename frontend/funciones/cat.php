<?php
require_once __DIR__ . "/../../backend/config/auth.php";

requiere_sesion();

 require '../../backend/config/Conexion.php';
 echo '<option value="">Seleccione</option>';
 $stmt = $connect->prepare('SELECT * FROM `categoria` WHERE state = 1 ORDER BY idcate ASC');

  $stmt->execute();


  while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            ?>
            <option value="<?php echo e($idcate); ?>"><?php echo e($nocate); ?></option>

            <?php
        }

  ?>


