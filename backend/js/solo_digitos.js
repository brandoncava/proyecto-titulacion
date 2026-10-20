// campos con inputmode="numeric" (ruc, dni, telefono): se quita todo lo que no sea digito al escribir o pegar
document.querySelectorAll('input[inputmode="numeric"], input[inputmode="tel"]').forEach(function (campo) {
    campo.addEventListener('input', function () { campo.value = campo.value.replace(/\D/g, ''); });
});
