<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('clientes');

$seccion = 'clientes';
$migas = 'Clientes / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nuevos clientes</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>
  
    <label for="psw"><b>Tipo de documento</b></label><span class="badge-warning">*</span>
    <select required name="tipcl" id="tipcl">
        <option value="">Seleccione</option>
        <option value="DNI">DNI</option>
        <option value="RUC">RUC (razón social)</option>
    </select>

    <label for="email"><b>Número del documento</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="11" id="numcl" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="DNI (8 dígitos) o RUC (11 dígitos)" name="numcl"  required>

    <label for="email"><b>Nombre del cliente o razón social</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: Alberto" name="nomcl"  required>

    <label for="email"><b>Apellido del cliente (solo DNI)</b></label>
    <input type="text" placeholder="ejm: Juarez" name="apecl" id="apecl">

    <label for="email"><b>Teléfono celular del cliente (opcional)</b></label>
    <input type="text" maxlength="9" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 99898787" name="telcl">


    <hr>
   
    <button type="submit" name="add_customer" class="registerbtn">Guardar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_customer.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
    <script type="text/javascript">
    (function () {
        var tipo = document.getElementById('tipcl'), num = document.getElementById('numcl'), ape = document.getElementById('apecl');
        tipo.addEventListener('change', function () {
            num.maxLength = (tipo.value === 'RUC') ? 11 : 8;
            if (num.value.length > num.maxLength) { num.value = num.value.slice(0, num.maxLength); }
            ape.required = (tipo.value === 'DNI');
        });
    })();
    </script>
</body>
</html>
