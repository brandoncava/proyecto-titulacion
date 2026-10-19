-- 05: enlaza ventas con clientes y compras con usuarios
-- Ejecutar sobre ckcomputers_db (haz un respaldo antes).
-- Orden: 1) revisar datos, 2) aplicar cambios, 3) completar ventas antiguas.

-- ---------------------------------------------------------------
-- 1) REVISION PREVIA (las dos consultas deben devolver 0 filas)
-- ---------------------------------------------------------------

-- compras cuyo usuario no existe: impedirian crear la clave foranea
SELECT idordpur, user_id
  FROM orders_purchase
 WHERE user_id NOT IN (SELECT id FROM usuarios);

-- documentos repetidos en clientes: impedirian el indice UNIQUE (paso 2c)
SELECT nudoc, COUNT(*) AS veces
  FROM clientes
 GROUP BY nudoc
HAVING COUNT(*) > 1;

-- ---------------------------------------------------------------
-- 2) CAMBIOS
-- ---------------------------------------------------------------

-- 2a) compras -> usuarios (1:N): clave foranea que faltaba
ALTER TABLE `orders_purchase`
  ADD KEY `idx_orders_purchase_usuario` (`user_id`),
  ADD CONSTRAINT `orders_purchase_ibfk_2`
      FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`);

-- 2b) ventas -> clientes (1:N)
-- idcli admite NULL porque las ventas anteriores solo tienen el nombre en texto.
-- nomcl se conserva como "nombre del cliente al momento de la venta":
-- asi la boleta no cambia si despues se edita el cliente.
ALTER TABLE `orders`
  ADD COLUMN `idcli` INT(11) NULL AFTER `user_id`,
  ADD KEY `idx_orders_cliente` (`idcli`),
  ADD CONSTRAINT `orders_ibfk_2`
      FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`)
      ON UPDATE CASCADE;

-- 2c) el documento del cliente pasa a ser unico tambien en la base
ALTER TABLE `clientes`
  ADD UNIQUE KEY `uq_clientes_nudoc` (`nudoc`);

-- ---------------------------------------------------------------
-- 3) VENTAS ANTIGUAS: enlazar por nombre cuando coincide con un solo cliente
-- ---------------------------------------------------------------
UPDATE `orders` o
  JOIN (
        SELECT LOWER(CONCAT(TRIM(nocl), ' ', TRIM(apcl))) AS nombre,
               MIN(idcli) AS idcli
          FROM `clientes`
         GROUP BY nombre
        HAVING COUNT(*) = 1
       ) c ON LOWER(TRIM(o.nomcl)) = c.nombre
   SET o.idcli = c.idcli
 WHERE o.idcli IS NULL;

-- ventas que no se pudieron enlazar (revisar a mano o dejar sin cliente)
SELECT idord, nomcl FROM `orders` WHERE idcli IS NULL;
