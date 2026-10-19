-- 06: precio de compra separado del precio de venta, e IGV (18%) en ventas y compras
-- Criterio:
--   * productos.precio         = precio de VENTA, ya incluye IGV (no cambia)
--   * productos.precio_compra  = ultimo costo unitario SIN IGV, tal como figura en la factura del proveedor
--   * orders / orders_purchase guardan base (subtotal_sin_igv), igv y total_price (total con IGV)
-- Haz un respaldo antes de ejecutar.

ALTER TABLE `productos`
  ADD COLUMN `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0.00
      COMMENT 'Ultimo costo unitario sin IGV'
      AFTER `precio`;

ALTER TABLE `orders`
  ADD COLUMN `subtotal_sin_igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_price`,
  ADD COLUMN `igv`              DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `subtotal_sin_igv`;

ALTER TABLE `orders_purchase`
  ADD COLUMN `subtotal_sin_igv` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_price`,
  ADD COLUMN `igv`              DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `subtotal_sin_igv`;

-- Ventas anteriores: el total ya incluia IGV, se desglosa (tasa 18%)
UPDATE `orders`
   SET `subtotal_sin_igv` = ROUND(`total_price` / 1.18, 2),
       `igv`              = `total_price` - ROUND(`total_price` / 1.18, 2);

-- Compras anteriores: se valorizaron con el precio de VENTA (con IGV), no con el costo real.
-- Si son datos de prueba, lo mas limpio es borrarlas antes de empezar a registrar costos reales.
-- Si las conservas, se desglosan igual que las ventas para que los reportes no queden en 0.
UPDATE `orders_purchase`
   SET `subtotal_sin_igv` = ROUND(`total_price` / 1.18, 2),
       `igv`              = `total_price` - ROUND(`total_price` / 1.18, 2);
