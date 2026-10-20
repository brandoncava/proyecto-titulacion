-- 12: la razon social de una empresa no entra en 35 caracteres
-- (Finalizar venta acepta hasta 150). Ejecutar DESPUES de 11.

ALTER TABLE `clientes`
  MODIFY `nocl` varchar(150) NOT NULL,
  MODIFY `apcl` varchar(60) NOT NULL;
