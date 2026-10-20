-- 13: correo del cliente (opcional) y su autorizacion para recibir promociones
-- (ley 29733: el envio de publicidad necesita el consentimiento del titular,
-- se guarda la fecha para poder demostrarlo). Ejecutar DESPUES de 12.

ALTER TABLE `clientes`
  ADD COLUMN `emailcl`          varchar(100) DEFAULT NULL AFTER `telfcl`,
  ADD COLUMN `acepta_promos`    tinyint(1)   NOT NULL DEFAULT 0 AFTER `emailcl`,
  ADD COLUMN `acepta_promos_on` datetime     DEFAULT NULL COMMENT 'cuando autorizo recibir promociones' AFTER `acepta_promos`;
