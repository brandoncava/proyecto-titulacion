<?php
require_once __DIR__ . "/../../backend/config/auth.php";

requiere_sesion();

 require '../../backend/config/Conexion.php';
 echo '<option value="">Seleccione</option>';
 $stmt = $connect->prepare('SELECT * FROM `marca` WHERE state = 1 ORDER BY idmar ASC');

  $stmt->execute();


  while($row=$stmt->fetch(PDO::FETCH_ASSOC))
        {
            extract($row);
            ?>
            <option value="<?php echo e($idmar); ?>"><?php echo e($nomarc); ?></option>

            <?php
        }

  ?>


