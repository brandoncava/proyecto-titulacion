<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('compras');

$seccion = 'compras';
$migas = 'Compras / Carrito de compras';
$tablas = true;
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
            <div class="records table-responsive">
                     
                    <div>
                    

    <table width="100%" id="example">
        <thead>
            <tr>
            
            <th><span class="las la-sort"></span>Código</th>
            <th><span class="las la-sort"></span>Producto</th>
            <th><span class="las la-sort"></span>Precio</th>
            <th><span class="las la-sort"></span>Stock</th>
            <th><span class="las la-sort"></span>Cantidad</th>
            <th><span class="las la-sort"></span>Subtotal</th>
            <th><span class="las la-sort"></span></th>
        </tr>
        </thead>
        <tbody>
        <?php 
require '../../backend/config/Conexion.php';
$grand_total = 0;
$select_cart = $connect->prepare("SELECT cart_purchase.idcpr, cart_purchase.user_id, productos.precio,productos.stock,productos.foto,productos.idprod, productos.codpro, productos.nomprd, cart_purchase.name, cart_purchase.price, cart_purchase.quantity FROM cart_purchase INNER JOIN productos ON cart_purchase.idprod = productos.idprod;");
 $select_cart->execute();
 if($select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){ 
     ?>
     <tr>
   
         <td><h4><?php echo e($fetch_cart['codpro']); ?></h4></td>
         <td><h4><?php echo e($fetch_cart['nomprd']); ?></h4></td>
         <td><h4>S/<?php echo number_format($fetch_cart['precio'],2) ?></h4></td>
         <td><h4><?php echo e($fetch_cart['stock']); ?></h4></td>
        <td>
    <form action="" method="POST">
<?php echo csrf_campo(); ?>
        <div class="form-group">
            <input type="hidden" name="prdt" value="<?php echo e($fetch_cart['idcpr']); ?>">
                <input type="number" name="p_qty" value="<?php echo e($fetch_cart['quantity']); ?>" style="width:100px;" min="1" max="99" class="form-control" placeholder="Cantidad">
        </div>
    <button type="submit" name="update_qty" class="btn btn-primary" style="cursor: pointer;"> <i class="fa fa-refresh"></i></button>
    </form>    
        </td>
        <td><h4>S/<?= number_format($sub_total = ($fetch_cart['precio'] * $fetch_cart['quantity']),2); ?></h4></td>
    <td style="width:260px;">
    <a title="Eliminar" onclick="return confirm('Eliminar del carrito?');" href="../compra/eliminar.php?id=<?php echo e($fetch_cart['idcpr']); ?>" class="fa fa-trash"></a>                            
    </td>
     </tr>
     <?php
      $grand_total += $sub_total;
      }
   }else{
      echo '<p class="alert alert-warning">Tu carrito esta vació</p>';
   }
   ?>
        </tbody>
    </table>
     <h1 style="font-size:42px; color:#000000;">Precio Total: S/<?php echo number_format($grand_total, 2); ?>
                    </div>


                    <div>
                        <button  onclick="location.href='nuevo.php'" class="registerbtn">CONTINUAR COMPRANDO </button>
                       
                        <button class="pabtn <?= ($grand_total > 1)?'':'disabled'; ?>" onclick="location.href='checkout.php'">PRECEDER PAGO</button>
                    </div>

                </div>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
   <?php include_once '../../backend/php/upd_cart_purchase.php' ?>
</body>
</html>
