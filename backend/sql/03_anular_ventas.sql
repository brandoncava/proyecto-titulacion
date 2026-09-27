-- anulacion de ventas: se marca anulada y se devuelve el stock

ALTER TABLE `orders`
  ADD COLUMN `anulada`          TINYINT(1)    NOT NULL DEFAULT 0 AFTER `payment_status`,
  ADD COLUMN `anulada_on`       DATETIME      NULL               AFTER `anulada`,
  ADD COLUMN `motivo_anulacion` VARCHAR(255)  NULL               AFTER `anulada_on`;
