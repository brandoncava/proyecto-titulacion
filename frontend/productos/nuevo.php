<?php
ob_start();
require_once __DIR__ . '/../../backend/config/auth.php';

requiere_permiso('productos_ed');

$seccion = 'productos';
$migas = 'Productos / Nuevo';
require __DIR__ . '/../layout/cabecera.php';
?>
            
            <div class="page-content">
            
<form action="" enctype="multipart/form-data" method="POST"  autocomplete="off">
<?php echo csrf_campo(); ?>
  <div class="containerss">
    <h1>Nuevos productos</h1>
    <div class="alert-danger">
  <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
  <strong>Importante!</strong> Es importante rellenar los campos con &nbsp;<span class="badge-warning">*</span>
</div>
    <hr>
    <br>

    <label for="email"><b>Código del producto</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="14" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;" placeholder="ejm: 22478754784578" name="prdcod"  required>
  
    <label for="email"><b>Nombre del producto</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: Laptop Lenovo" name="prdnom"  required>

    <label for="email"><b>Descripción del producto</b></label><span class="badge-warning">*</span>
    <textarea required name="prddes" id="consl" required placeholder="Write something.." style="height:200px"></textarea>

    <label for="email"><b>Precio del producto</b></label><span class="badge-warning">*</span>
    <input type="text" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"  placeholder="ejm: 299" name="prdprec"  required>

    <label for="email"><b>Stock del producto</b></label><span class="badge-warning">*</span>
    <input type="text" maxlength="3" onKeypress="if (event.keyCode < 45 || event.keyCode > 57) event.returnValue = false;"  placeholder="ejm: 999" name="prdstco"  required>

    <label for="psw"><b>Marca del producto</b></label><span class="badge-warning">*</span>
        <div class="botons-modals">
        <label for="btns-modals">
            Nuevo
        </label>
    </div>
    <select required name="prdmarc" id="marc">
        <option>Seleccione</option>
        
    </select>

    <label for="psw"><b>Categoria del producto</b></label><span class="badge-warning">*</span>
    <select required name="prdcate" id="cat">
        <option>Seleccione</option>
    </select>

    <label for="email"><b>Modelo del producto</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: Lenovo" name="prdmod"  required>

    <label for="email"><b>Peso del producto</b></label><span class="badge-warning">*</span>
    <input type="text" placeholder="ejm: 20kg" name="prdpes"  required>

    <label for="email"><b>Foto del producto</b></label><span class="badge-warning">*</span>
    <div class="upload-box">
        <div class="upload-img">
            <img alt="Vista previa" hidden>
        </div>
            <label for="upload-input" class="upload-label">Upload Image</label>
    <input type="file" name="foto" required  id="upload-input">
                   
    </div>


    <hr>
   
    <button type="submit" name="add_prodct" class="registerbtn">Guardar</button>
  </div>
  
</form>
            
            </div>
            
<?php require __DIR__ . '/../layout/pie.php'; ?>
    <?php include_once '../../backend/php/add_prodct.php' ?>
    <script type="text/javascript" src="../../backend/js/reenvio.js"></script>
<script type="text/javascript">
  const uploadInput = document.querySelector('#upload-input') ;
const previewImg = document.querySelector('.upload-img img') ;

uploadInput.addEventListener('change',e => {
    if(e.target.files.length > 0) {
        const url = URL.createObjectURL(e.target.files[0]) ;
        previewImg.src = url ;
        previewImg.hidden = false ;
    }
})
</script>
  <script src="../../backend/js/cat.js"></script>
  <script src="../../backend/js/marc.js"></script>
  <?php include_once '../../backend/modal/md_marc.php' ?>
  <script type="text/javascript">
    // alta de marca sin recargar la pagina, asi no se pierde lo escrito en el producto
    function marca(){
        $.post('../../backend/php/add_marca.php', {
            trat: $('#trat').val(),
            csrf_token: $('input[name=csrf_token]').first().val()
        }, null, 'json').done(function (r) {
            swal(r.ok ? '¡Registrado!' : 'Aviso', r.mensaje, r.ok ? 'success' : 'warning');
            if (r.idmar) {
                $.post('../../frontend/funciones/marc.php').done(function (opciones) {
                    $('#marc').html(opciones).val(r.idmar);
                });
                $('#trat').val('');
                $('#btns-modals').prop('checked', false);
            }
        }).fail(function () {
            swal('Error', 'No se pudo guardar la marca.', 'error');
        });
    }
</script>
</body>
</html>
