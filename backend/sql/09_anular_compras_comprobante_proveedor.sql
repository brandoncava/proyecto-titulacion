-- 09: anulacion de compras, comprobante del proveedor, series activas, codigo unico y stock numerico
-- Ejecutar DESPUES de 05 a 08.
-- Haz un respaldo antes de ejecutar.

-- revisar antes (debe devolver 0 filas): codigos de producto repetidos
SELECT `codpro`, COUNT(*) AS veces
  FROM `productos`
 GROUP BY `codpro`
HAVING COUNT(*) > 1;

-- 1) compras: anulacion (igual que las ventas) y datos del comprobante que entrega el proveedor
ALTER TABLE `orders_purchase`
  ADD COLUMN `anulada`          TINYINT(1)   NOT NULL DEFAULT 0 AFTER `tipc`,
  ADD COLUMN `anulada_on`       DATETIME     NULL AFTER `anulada`,
  ADD COLUMN `motivo_anulacion` VARCHAR(255) NULL AFTER `anulada_on`,
  ADD COLUMN `serie_doc`        CHAR(4)      NULL AFTER `motivo_anulacion`,
  ADD COLUMN `numero_doc`       CHAR(8)      NULL AFTER `serie_doc`,
  ADD COLUMN `fecha_emision`    DATE         NULL AFTER `numero_doc`,
  ADD UNIQUE KEY `uq_compra_comprobante` (`idprov`, `tipc`, `serie_doc`, `numero_doc`);

-- 2) series: activa o inactiva (la venta usa la primera serie activa de cada comprobante)
ALTER TABLE `series`
  ADD COLUMN `state` TINYINT(1) NOT NULL DEFAULT 1 AFTER `ultimo`;

-- 3) productos: codigo unico y stock como numero entero (antes CHAR(3))
ALTER TABLE `productos`
  MODIFY `stock` INT(11) NOT NULL DEFAULT 0,
  ADD UNIQUE KEY `uq_productos_codpro` (`codpro`);
