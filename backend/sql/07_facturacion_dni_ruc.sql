-- 07: clientes con DNI o RUC, series y correlativos, boleta o factura segun el documento
-- Ejecutar DESPUES de 05 y 06 (usa orders.idcli y las columnas de IGV).
-- Reglas del sistema:
--   * cliente con RUC            -> Factura (serie F001)
--   * cliente con DNI o sin dato -> Boleta  (serie B001); si el total supera S/ 700 el DNI es obligatorio
-- Haz un respaldo antes de ejecutar.

-- 1) clientes: el documento admite 11 digitos (RUC) y el nombre admite razon social
ALTER TABLE `clientes`
  MODIFY `nudoc`  VARCHAR(11)  NOT NULL,
  MODIFY `nocl`   VARCHAR(150) NOT NULL,
  MODIFY `telfcl` CHAR(9)      NOT NULL DEFAULT '';

-- 'dni' pasa a 'DNI'
-- fere = fere para no perder la fecha de registro (fere tiene ON UPDATE)
UPDATE `clientes` SET `tipd` = UPPER(`tipd`), `fere` = `fere`;

-- 2) series y correlativos
CREATE TABLE `series` (
  `idserie` int(11) NOT NULL AUTO_INCREMENT,
  `tipc`    varchar(15) NOT NULL COMMENT 'Boleta o Factura',
  `serie`   char(4) NOT NULL,
  `ultimo`  int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'ultimo numero emitido',
  PRIMARY KEY (`idserie`),
  UNIQUE KEY `uq_series` (`tipc`, `serie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- 3) ventas: serie, numero y documento del comprador (copia al momento de la venta)
ALTER TABLE `orders`
  ADD COLUMN `serie`    CHAR(4) NULL AFTER `tipc`,
  ADD COLUMN `numero`   INT UNSIGNED NULL AFTER `serie`,
  ADD COLUMN `doc_tipo` VARCHAR(10) NULL AFTER `numero`,
  ADD COLUMN `doc_num`  VARCHAR(11) NULL AFTER `doc_tipo`,
  ADD UNIQUE KEY `uq_orders_comprobante` (`serie`, `numero`);

-- 4) ventas anteriores: se numeran como boletas B001 con su numero de venta
UPDATE `orders` SET `serie` = 'B001', `numero` = `idord` WHERE `serie` IS NULL;

UPDATE `orders` o
  JOIN `clientes` c ON c.`idcli` = o.`idcli`
   SET o.`doc_tipo` = c.`tipd`, o.`doc_num` = c.`nudoc`
 WHERE o.`idcli` IS NOT NULL;

-- 5) series iniciales: la boleta continua despues de la ultima venta
INSERT INTO `series` (`tipc`, `serie`, `ultimo`)
VALUES ('Boleta',  'B001', (SELECT COALESCE(MAX(`numero`), 0) FROM `orders`)),
       ('Factura', 'F001', 0);
