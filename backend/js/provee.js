$(function(){

    // lista de proveedores; data-valor deja elegido uno (al modificar una compra)
    $.post( '../../frontend/funciones/provee.php' ).done( function(respuesta)
    {
        var lista = $( '#provee' );
        lista.html( respuesta );
        if (lista.data('valor')) {
            lista.val(String(lista.data('valor')));
        }
    });

})
