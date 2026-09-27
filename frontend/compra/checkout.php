<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('compras');

$seccion = 'compras';
$migas = 'Compras / Finalizar pago';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">

                <div class="input-block">
    
           <?php
    require_once('../../backend/config/Conexion.php');
    $user_id = $_SESSION['id'];
      $cart_grand_total = 0;
      $select_cart_items = $connect->prepare("SELECT cart_purchase.idcpr, cart_purchase.user_id, productos.precio,productos.stock,productos.foto,productos.idprod, productos.codpro, productos.nomprd, cart_purchase.name, cart_purchase.price, cart_purchase.quantity FROM cart_purchase INNER JOIN productos ON cart_purchase.idprod = productos.idprod WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['precio'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?php echo e($fetch_cart_items['name']); ?> <span>(<?= 'S/'.$fetch_cart_items['precio'].'/- x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty"><p class="alert alert-warning">Tu carrito esta vació</p></p>';

   }
   ?>
   <div class="grand-total">Precio Total : <span>S/<?php echo number_format($cart_grand_total, 2); ?></span></div>
   
 </div>  

            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Finalizar compra</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  
    <label for="email"><b>Proveedores</b></label><span class="badge-warning">*</span>
    <select required name="cxtprov" id="provee">
        <option>Seleccione</option>
       
       
    </select>

   <label for="psw"><b>Comprobante de pago</b></label><span class="badge-warning">*</span>
    <select required name="cxcom" id="cat">
        <option>Seleccione</option>
        <option value="Boleta">Boleta</option>
       
    </select>

    <label for="psw"><b>Método de pago</b></label><span class="badge-warning">*</span>
        <select required name="cxtcre" id="cat">
            <option>Seleccione</option>
            <option value="Contado">Contado</option>
            <option value="Tarjeta">Tarjeta</option>
        </select>

    <hr>
   
    <button type="submit" id="validate"  name="order" class="registerbtn <?= ($cart_grand_total > 1)?'':'disabled'; ?>">Guardar</button>
    <button onclick="location.href='cart.php'" class="pabtn ">Cancelar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php
    include_once '../../backend/php/add_check_purchase.php'
?>
    <script>
$('#validate').click(function() {

    if ($('#cxtcre').val().trim() === '') {
        
        swal("Debe seleccionar una opción");

    } else {
       swal("Campos correctos");
    }
});
</script>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript" src="../../backend/js/provee.js"></script>
</body>
</html>
