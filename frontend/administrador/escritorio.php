<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('reportes');

$seccion = 'dashboard';
$migas = 'Dashboard';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
                <div class="analytics">

                    <div class="card">
                        <div class="card-head">
                            <?php 
                             require_once('../../backend/config/Conexion.php');
                                            $sql = "SELECT COUNT(*) total FROM clientes WHERE state = 1";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2><?php echo e($total); ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Clientes</small>
                            
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                             <?php 
                                            $sql = "SELECT SUM(total_price) total FROM orders WHERE anulada = 0";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2>S/<?php echo number_format($total,2) ?></h2>
                            <span class="las la-money-bill"></span>
                        </div>
                        <div class="card-progress">
                            <small>Ventas</small>
                           
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <?php 
                             
                                            $sql = "SELECT COUNT(*) total FROM productos WHERE state = 1";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2><?php echo e($total); ?></h2>
                            <span class="las la-shopping-cart"></span>
                        </div>
                        <div class="card-progress">
                            <small>Productos</small>
                          
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <?php 
                             
                                            $sql = "SELECT COUNT(*) total FROM usuarios WHERE state = 1";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2><?php echo e($total); ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Accesos</small>
                            
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                             <?php 
                                            $sql = "SELECT SUM(total_price) total FROM orders_purchase";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2>S/<?php echo number_format($total,2) ?></h2>
                            <span class="las la-store"></span>
                        </div>
                        <div class="card-progress">
                            <small>Compras</small>
                           
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <?php 
                             
                                            $sql = "SELECT COUNT(*) total FROM categoria WHERE state = 1";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2><?php echo e($total); ?></h2>
                            <span class="las la-paperclip"></span>
                        </div>
                        <div class="card-progress">
                            <small>Categorias</small>
                            
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <?php 
                             
                                            $sql = "SELECT COUNT(*) total FROM marca WHERE state = 1";
                                            $result = $connect->query($sql); //$pdo sería el objeto conexión
                                            $total = $result->fetchColumn();

                                             ?>
                            <h2><?php echo e($total); ?></h2>
                            <span class="las la-thumbtack"></span>
                        </div>
                        <div class="card-progress">
                            <small>Marca</small>
                            
                        </div>
                    </div>

                </div>


                <div class="records table-responsive">
                     <div class="record-header">
                        <h1>Clientes nuevos</h1>
                    </div>
                    <div>
                        <?php 
$sentencia = $connect->prepare("SELECT * FROM clientes WHERE state = 1 ORDER BY idcli DESC;");
 $sentencia->execute();
$data =  array();
if($sentencia){
  while($r = $sentencia->fetchObject()){
    $data[] = $r;
  }
}
     ?>
     <?php if(count($data)>0):?>
                        <table width="100%" id="example">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><span class="las la-sort"></span> CLIENTES</th>
                                    <th><span class="las la-sort"></span> TELEFONO</th>
                                </tr>
                            </thead>
                            <tbody>
                                  <?php foreach($data as $d):?>
                                <tr>
                                    <td><?php echo e($d->idcli); ?></td>
                                    <td>
                                        <div class="client">
                                           <div class="client-img bg-img" style="background-image: url(../../backend/img/user13.png)"></div>
                                            <div class="client-info">
                                                <h4><?php echo e($d->nocl); ?>&nbsp;<?php echo e($d->apcl); ?></h4>
                                                <small><?php echo e($d->nudoc); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo e($d->telfcl); ?>
                                    </td>
                                </tr>
                                 <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else:?>
                           <div class="alert">
      <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
      <strong>Danger!</strong> No hay datos.
    </div>
    <?php endif; ?>
                    </div>

                </div>

                <br>
              
                
            </div>

            <div class="page-content">
            
            <div class="records table-responsive">
                    <div class="record-header">
                        <h1>Gráficas</h1>
                    </div>
                    <div>
    

                <hr>
        <div id="chartDiv" class="pie-chart"></div>  
        <div class="text-center"></div>       
                    </div>

                </div>
                
            </div>



<?php require __DIR__ . '/../layout/pie.php'; ?>
 <script src="https://www.google.com/jsapi"></script>


 <script type="text/javascript">
    window.onload = function() {
        google.load("visualization", "1.1", {
            packages: ["corechart"],
            callback: 'drawChart'
        });
    };
  
    function drawChart() {
        <?php
        // json_encode escapa comillas y apostrofes de los nombres
        $filas = [['Producto', 'Stock']];
        foreach ($connect->query('SELECT nomprd, stock FROM productos WHERE state = 1') as $row) {
            $filas[] = [$row->nomprd, (int)$row->stock];
        }
        ?>
        var data = google.visualization.arrayToDataTable(<?php echo json_encode($filas, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE); ?>);

        var options = {
            pieHole: 0.4,
            title: 'Productos por stock',
        };
  
        var chart = new google.visualization.PieChart(document.getElementById('chartDiv'));
        chart.draw(data, options);
    }

</script>


</body>
</html>
