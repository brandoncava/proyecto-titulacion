-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ckcomputers_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `ckcomputers_db`
--



--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `idv` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `idprod` int(11) NOT NULL,
  `name` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`idv`),
  KEY `user_id` (`user_id`),
  KEY `idprod` (`idprod`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_purchase`
--

DROP TABLE IF EXISTS `cart_purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_purchase` (
  `idcpr` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `idprod` int(11) NOT NULL,
  `name` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`idcpr`),
  KEY `user_id` (`user_id`),
  KEY `idprod` (`idprod`),
  CONSTRAINT `cart_purchase_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `cart_purchase_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_purchase`
--

LOCK TABLES `cart_purchase` WRITE;
/*!40000 ALTER TABLE `cart_purchase` DISABLE KEYS */;
INSERT INTO `cart_purchase` VALUES (4,1,1,'LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD',2054.24,1),(5,1,2,' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP',2626.27,3);
/*!40000 ALTER TABLE `cart_purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categoria` (
  `idcate` int(11) NOT NULL AUTO_INCREMENT,
  `nocate` varchar(100) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idcate`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (2,'LAPTOPS',0,'2026-09-15 03:20:47'),(4,'PCS',0,'2026-09-15 03:20:53'),(5,'SERVIDORESS',1,'2026-09-15 03:21:06'),(6,'PROYECTORES',1,'2026-08-15 02:16:16'),(7,'IMPRESORAS',1,'2026-08-15 02:35:15'),(8,'MONITORES',1,'2026-08-15 02:16:44'),(9,'COMPONENTES',1,'2026-08-15 02:17:11'),(11,'SUMINISTROS',1,'2026-08-20 14:10:00');
/*!40000 ALTER TABLE `categoria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `idcli` int(11) NOT NULL AUTO_INCREMENT,
  `tipd` varchar(25) NOT NULL,
  `nudoc` varchar(11) NOT NULL,
  `nocl` varchar(150) NOT NULL,
  `apcl` varchar(60) NOT NULL,
  `telfcl` char(9) NOT NULL DEFAULT '',
  `emailcl` varchar(100) DEFAULT NULL,
  `acepta_promos` tinyint(1) NOT NULL DEFAULT 0,
  `acepta_promos_on` datetime DEFAULT NULL COMMENT 'cuando autorizo recibir promociones',
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idcli`),
  UNIQUE KEY `uq_clientes_nudoc` (`nudoc`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'DNI','78885848','Julian','Juarez Lopez','968586757','julian.juarez@gmail.com',1,'2026-09-11 04:57:39',1,'2026-10-20 02:16:59'),(2,'DNI','76546564','Karla','Martinez','976575665',NULL,0,NULL,1,'2026-08-15 08:08:04'),(4,'DNI','76564564','Leonardo','Flores','986858658',NULL,0,NULL,1,'2026-08-19 08:56:52'),(5,'RUC','20511697353','EQUIPOS Y ACCESORIOS DE COMPUTO SOCIEDAD ANONIMA CERRADA','','987654321',NULL,0,NULL,1,'2026-10-20 02:06:41'),(6,'DNI','45871236','María Elena','Quispe Huamán','987451236','maria.quispe@gmail.com',1,'2026-08-21 11:10:00',1,'2026-10-20 02:16:59'),(7,'DNI','70215489','Carlos Alberto','Rojas Paredes','965874123','carlos.rojas@hotmail.com',0,NULL,1,'2026-10-20 02:16:59'),(8,'DNI','41236985','Rosa Isabel','Chávez Torres','951236478','rosa.chavez@gmail.com',1,'2026-08-21 11:12:00',1,'2026-10-20 02:16:59'),(9,'DNI','72548963','Luis Fernando','Mendoza Silva','978541236',NULL,0,NULL,1,'2026-08-22 15:13:00'),(10,'DNI','46985231','Patricia','Gutiérrez Ramos','942158763','patricia.gutierrez@outlook.com',1,'2026-08-22 11:14:00',1,'2026-10-20 02:16:59'),(11,'DNI','73659814','Jorge Luis','Castillo Vega','913625874',NULL,0,NULL,1,'2026-08-22 15:15:00'),(12,'DNI','40789652','Gabriela','Sánchez Díaz','956321478',NULL,0,NULL,1,'2026-08-23 15:16:00'),(13,'DNI','75896321','Diego Armando','Vargas Ríos','987123654','diego.vargas@gmail.com',0,NULL,1,'2026-10-20 02:16:59'),(14,'RUC','20601589738','ESTUDIO CONTABLE RAMOS & ASOCIADOS S.A.C.','','914578963','contabilidad@ramosasociados.pe',1,'2026-08-23 11:18:00',1,'2026-10-20 02:16:59'),(15,'RUC','20487152634','COLEGIO PARTICULAR SAN MARTIN DE PORRES E.I.R.L.','','956874521','administracion@colegiosanmartin.edu.pe',0,NULL,1,'2026-10-20 02:16:59'),(16,'RUC','10458712366','QUISPE HUAMAN MARIA ELENA','','987451236',NULL,0,NULL,1,'2026-08-24 15:20:00');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `garantias`
--

DROP TABLE IF EXISTS `garantias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `garantias` (
  `idgar` int(11) NOT NULL AUTO_INCREMENT,
  `idord` int(11) DEFAULT NULL COMMENT 'venta original, NULL si se vendio fuera del sistema',
  `idprod` int(11) NOT NULL,
  `idcli` int(11) DEFAULT NULL,
  `cliente_nombre` varchar(150) NOT NULL,
  `cliente_doc` varchar(11) DEFAULT NULL,
  `cliente_tel` varchar(15) DEFAULT NULL,
  `comprobante` varchar(20) DEFAULT NULL COMMENT 'boleta o factura de la compra original',
  `fecha_compra` date NOT NULL,
  `vence` date NOT NULL,
  `vigente` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'garantia en plazo al recibir el producto',
  `numero_serie` varchar(60) DEFAULT NULL,
  `estado_fisico` varchar(255) NOT NULL,
  `accesorios` varchar(255) DEFAULT NULL,
  `falla` text NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Ingresado',
  `idprov` int(11) DEFAULT NULL COMMENT 'proveedor o servicio tecnico de la marca (RMA)',
  `rma_codigo` varchar(50) DEFAULT NULL,
  `rma_fecha_envio` date DEFAULT NULL,
  `resultado` varchar(15) DEFAULT NULL COMMENT 'Reparado, Reemplazado o Rechazado',
  `resultado_detalle` varchar(255) DEFAULT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`idgar`),
  KEY `idx_gar_estado` (`estado`),
  KEY `idx_gar_venta` (`idord`),
  KEY `idx_gar_producto` (`idprod`),
  KEY `idx_gar_cliente` (`idcli`),
  KEY `idx_gar_proveedor` (`idprov`),
  KEY `idx_gar_usuario` (`user_id`),
  CONSTRAINT `gar_ibfk_1` FOREIGN KEY (`idord`) REFERENCES `orders` (`idord`),
  CONSTRAINT `gar_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`),
  CONSTRAINT `gar_ibfk_3` FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`),
  CONSTRAINT `gar_ibfk_4` FOREIGN KEY (`idprov`) REFERENCES `proveedores` (`idprov`),
  CONSTRAINT `gar_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `garantias`
--

LOCK TABLES `garantias` WRITE;
/*!40000 ALTER TABLE `garantias` DISABLE KEYS */;
INSERT INTO `garantias` VALUES (1,26,14,14,'ESTUDIO CONTABLE RAMOS & ASOCIADOS S.A.C.','20601589738','914578963','F001-00000004','2026-09-30','2027-09-30',1,'X5NY012345','Buen estado, sin golpes','Cable de poder y cable USB','No jala el papel, se atasca en la bandeja trasera','Entregado',5,'RMA-24518','2026-10-03','Reparado','Se cambió el rodillo de alimentación','2026-10-01 11:20:00','2026-10-14 17:30:00',4),(2,36,15,1,'Julian Juarez Lopez','78885848','968586757','B001-00000030','2026-10-09','2027-10-09',1,'E78123K4N9','Leve rayón en la tapa del escáner','Cable de poder','Imprime con rayas horizontales en el negro','En evaluacion',6,'GR-0031877','2026-10-12',NULL,NULL,'2026-10-10 15:05:00',NULL,4),(3,42,9,10,'Patricia Gutiérrez Ramos','46985231','942158763','B001-00000035','2026-10-14','2027-10-14',1,'X9PA067812','Buen estado','Cable de poder, cable USB y botellas de tinta','No enciende','Ingresado',NULL,NULL,NULL,NULL,NULL,'2026-10-17 12:40:00',NULL,4);
/*!40000 ALTER TABLE `garantias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `garantias_historial`
--

DROP TABLE IF EXISTS `garantias_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `garantias_historial` (
  `idhis` int(11) NOT NULL AUTO_INCREMENT,
  `idgar` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`idhis`),
  KEY `idx_his_garantia` (`idgar`,`fecha`),
  KEY `idx_his_usuario` (`user_id`),
  CONSTRAINT `his_ibfk_1` FOREIGN KEY (`idgar`) REFERENCES `garantias` (`idgar`),
  CONSTRAINT `his_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `garantias_historial`
--

LOCK TABLES `garantias_historial` WRITE;
/*!40000 ALTER TABLE `garantias_historial` DISABLE KEYS */;
INSERT INTO `garantias_historial` VALUES (1,1,'Ingresado','Producto recibido en tienda',4,'2026-10-01 11:20:00'),(2,1,'Enviado','Enviado a DISTRIBUIDORA TECNOLOGICA DEL NORTE S.A.C. (RMA/guía RMA-24518). Se envía con su guía de remisión',1,'2026-10-03 09:40:00'),(3,1,'En evaluacion','La marca confirmó la recepción',1,'2026-10-06 16:00:00'),(4,1,'Resuelto','Reparado: Se cambió el rodillo de alimentación',1,'2026-10-12 12:10:00'),(5,1,'Listo','Se llamó al cliente para que recoja',1,'2026-10-13 10:00:00'),(6,1,'Entregado','Entregado al titular de la compra',1,'2026-10-14 17:30:00'),(7,2,'Ingresado','Producto recibido en tienda',4,'2026-10-10 15:05:00'),(8,2,'Enviado','Enviado a IMPORTACIONES PRINT PERU E.I.R.L. (RMA/guía GR-0031877)',1,'2026-10-12 10:30:00'),(9,2,'En evaluacion','En revisión por el servicio técnico de la marca',1,'2026-10-15 11:45:00'),(10,3,'Ingresado','Producto recibido en tienda',4,'2026-10-17 12:40:00');
/*!40000 ALTER TABLE `garantias_historial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marca`
--

DROP TABLE IF EXISTS `marca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marca` (
  `idmar` int(11) NOT NULL AUTO_INCREMENT,
  `nomarc` text NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idmar`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marca`
--

LOCK TABLES `marca` WRITE;
/*!40000 ALTER TABLE `marca` DISABLE KEYS */;
INSERT INTO `marca` VALUES (1,'Lenovo',1,'2026-08-16 01:24:00'),(2,'hp',1,'2026-08-19 09:00:23'),(3,'Epson',1,'2026-08-20 14:05:00'),(4,'Brother',1,'2026-08-20 14:06:00'),(5,'Canon',1,'2026-08-20 14:07:00');
/*!40000 ALTER TABLE `marca` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_inventario` (
  `idmov` int(11) NOT NULL AUTO_INCREMENT,
  `idprod` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tipo` varchar(15) NOT NULL COMMENT 'Saldo inicial, Compra, Venta, Anulacion, Modificacion o Ajuste',
  `cantidad` int(11) NOT NULL COMMENT 'positiva si entra, negativa si sale',
  `stock_resultante` int(11) NOT NULL,
  `idord` int(11) DEFAULT NULL COMMENT 'venta o anulacion',
  `idordpur` int(11) DEFAULT NULL COMMENT 'compra',
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`idmov`),
  KEY `idx_mov_producto` (`idprod`,`fecha`),
  KEY `idx_mov_usuario` (`user_id`),
  KEY `idx_mov_venta` (`idord`),
  KEY `idx_mov_compra` (`idordpur`),
  CONSTRAINT `mov_ibfk_1` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`),
  CONSTRAINT `mov_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `mov_ibfk_3` FOREIGN KEY (`idord`) REFERENCES `orders` (`idord`),
  CONSTRAINT `mov_ibfk_4` FOREIGN KEY (`idordpur`) REFERENCES `orders_purchase` (`idordpur`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (1,1,1,'Saldo inicial',99,99,NULL,NULL,'2026-10-19 20:38:39'),(2,2,1,'Saldo inicial',20,20,NULL,NULL,'2026-10-19 20:38:39'),(3,4,1,'Saldo inicial',90,90,NULL,NULL,'2026-10-19 20:38:39'),(4,5,1,'Compra',8,8,NULL,2,'2026-08-25 10:30:00'),(5,6,1,'Compra',8,8,NULL,2,'2026-08-25 10:30:00'),(6,7,1,'Compra',6,6,NULL,2,'2026-08-25 10:30:00'),(7,8,1,'Compra',5,5,NULL,2,'2026-08-25 10:30:00'),(8,9,1,'Compra',4,4,NULL,2,'2026-08-25 10:30:00'),(9,10,1,'Compra',3,3,NULL,2,'2026-08-25 10:30:00'),(10,17,1,'Compra',40,40,NULL,2,'2026-08-25 10:30:00'),(11,18,1,'Compra',40,40,NULL,2,'2026-08-25 10:30:00'),(12,19,1,'Compra',30,30,NULL,2,'2026-08-25 10:30:00'),(13,20,1,'Compra',20,20,NULL,2,'2026-08-25 10:30:00'),(14,11,1,'Compra',6,6,NULL,3,'2026-08-28 15:10:00'),(15,12,1,'Compra',6,6,NULL,3,'2026-08-28 15:10:00'),(16,13,1,'Compra',5,5,NULL,3,'2026-08-28 15:10:00'),(17,14,1,'Compra',4,4,NULL,3,'2026-08-28 15:10:00'),(18,15,1,'Compra',3,3,NULL,3,'2026-08-28 15:10:00'),(19,16,1,'Compra',3,3,NULL,3,'2026-08-28 15:10:00'),(20,23,3,'Compra',30,30,NULL,4,'2026-09-02 11:45:00'),(21,24,3,'Compra',30,30,NULL,4,'2026-09-02 11:45:00'),(22,25,3,'Compra',25,25,NULL,4,'2026-09-02 11:45:00'),(23,26,3,'Compra',15,15,NULL,4,'2026-09-02 11:45:00'),(24,27,3,'Compra',10,10,NULL,4,'2026-09-02 11:45:00'),(25,22,3,'Compra',30,30,NULL,4,'2026-09-02 11:45:00'),(26,21,3,'Compra',30,30,NULL,4,'2026-09-02 11:45:00'),(27,12,4,'Venta',-3,3,9,NULL,'2026-09-15 15:21:17'),(28,11,4,'Venta',-1,5,10,NULL,'2026-09-15 16:40:00'),(29,21,1,'Venta',-1,29,11,NULL,'2026-09-17 15:39:11'),(30,13,3,'Venta',-3,2,12,NULL,'2026-09-17 17:29:27'),(31,12,4,'Venta',-1,2,13,NULL,'2026-09-18 16:38:41'),(32,24,4,'Venta',-1,29,13,NULL,'2026-09-18 16:38:41'),(33,21,4,'Venta',-1,28,14,NULL,'2026-09-19 09:09:08'),(34,26,4,'Venta',-2,13,14,NULL,'2026-09-19 09:09:08'),(35,17,4,'Venta',-1,39,15,NULL,'2026-09-19 14:05:38'),(36,13,1,'Venta',-1,1,16,NULL,'2026-09-22 10:08:31'),(37,24,1,'Venta',-1,28,16,NULL,'2026-09-22 10:08:31'),(38,13,3,'Venta',-1,0,17,NULL,'2026-09-22 14:59:21'),(39,18,3,'Venta',-2,38,17,NULL,'2026-09-22 14:59:21'),(40,25,4,'Venta',-3,22,18,NULL,'2026-09-23 11:51:46'),(41,27,4,'Venta',-2,8,18,NULL,'2026-09-23 11:51:46'),(42,18,4,'Venta',-3,35,19,NULL,'2026-09-23 18:48:39'),(43,20,4,'Venta',-2,18,19,NULL,'2026-09-23 18:48:39'),(44,23,4,'Venta',-1,29,19,NULL,'2026-09-23 18:48:39'),(45,12,4,'Venta',-1,1,20,NULL,'2026-09-24 12:44:56'),(46,26,4,'Venta',-2,11,20,NULL,'2026-09-24 12:44:56'),(47,8,1,'Venta',-1,4,21,NULL,'2026-09-24 13:52:55'),(48,26,1,'Venta',-2,9,21,NULL,'2026-09-24 13:52:55'),(49,5,1,'Compra',6,14,NULL,5,'2026-09-25 09:50:00'),(50,6,1,'Compra',6,14,NULL,5,'2026-09-25 09:50:00'),(51,17,1,'Compra',30,69,NULL,5,'2026-09-25 09:50:00'),(52,18,1,'Compra',30,65,NULL,5,'2026-09-25 09:50:00'),(53,19,1,'Compra',20,50,NULL,5,'2026-09-25 09:50:00'),(54,22,3,'Venta',-2,28,22,NULL,'2026-09-25 10:59:44'),(55,22,1,'Anulacion',2,30,22,NULL,'2026-09-26 10:20:00'),(56,11,4,'Venta',-3,2,23,NULL,'2026-09-26 12:45:30'),(57,22,4,'Venta',-2,28,23,NULL,'2026-09-26 12:45:30'),(58,5,4,'Venta',-1,13,24,NULL,'2026-09-28 14:41:38'),(59,17,4,'Venta',-1,68,25,NULL,'2026-09-30 13:54:16'),(60,19,4,'Venta',-1,49,25,NULL,'2026-09-30 13:54:16'),(61,24,4,'Venta',-2,26,25,NULL,'2026-09-30 13:54:16'),(62,14,1,'Venta',-2,2,26,NULL,'2026-09-30 17:26:12'),(63,24,1,'Venta',-2,24,26,NULL,'2026-09-30 17:26:12'),(64,21,3,'Venta',-1,27,27,NULL,'2026-10-01 13:38:03'),(65,16,4,'Venta',-1,2,28,NULL,'2026-10-01 15:16:22'),(66,24,4,'Venta',-3,21,29,NULL,'2026-10-02 09:03:47'),(67,22,4,'Venta',-2,26,30,NULL,'2026-10-02 15:52:52'),(68,11,1,'Venta',-1,1,31,NULL,'2026-10-03 12:12:20'),(69,22,1,'Venta',-2,24,31,NULL,'2026-10-03 12:12:20'),(70,10,3,'Venta',-1,2,32,NULL,'2026-10-05 13:27:12'),(71,19,3,'Venta',-2,47,32,NULL,'2026-10-05 13:27:12'),(72,19,4,'Venta',-1,46,33,NULL,'2026-10-05 16:21:14'),(73,27,4,'Venta',-2,6,33,NULL,'2026-10-05 16:21:14'),(74,5,4,'Venta',-1,12,34,NULL,'2026-10-07 17:47:52'),(75,23,3,'Compra',20,49,NULL,6,'2026-10-08 16:20:00'),(76,24,3,'Compra',20,41,NULL,6,'2026-10-08 16:20:00'),(77,22,3,'Compra',20,44,NULL,6,'2026-10-08 16:20:00'),(78,11,1,'Compra',4,5,NULL,7,'2026-10-09 10:15:00'),(79,12,1,'Compra',4,5,NULL,7,'2026-10-09 10:15:00'),(80,13,1,'Compra',3,3,NULL,7,'2026-10-09 10:15:00'),(81,14,4,'Venta',-1,1,35,NULL,'2026-10-09 10:16:39'),(82,15,1,'Venta',-1,2,36,NULL,'2026-10-09 15:49:23'),(83,9,3,'Venta',-1,3,37,NULL,'2026-10-10 15:05:46'),(84,18,3,'Venta',-2,63,37,NULL,'2026-10-10 15:05:46'),(85,7,4,'Venta',-3,3,38,NULL,'2026-10-10 15:57:54'),(86,21,4,'Venta',-2,25,38,NULL,'2026-10-10 15:57:54'),(87,6,4,'Venta',-1,13,39,NULL,'2026-10-13 11:06:33'),(88,25,4,'Venta',-2,20,39,NULL,'2026-10-13 11:06:33'),(89,17,4,'Venta',-1,67,40,NULL,'2026-10-13 14:55:14'),(90,21,4,'Venta',-3,22,40,NULL,'2026-10-13 14:55:14'),(91,15,1,'Venta',-1,1,41,NULL,'2026-10-14 10:12:51'),(92,9,3,'Venta',-1,2,42,NULL,'2026-10-14 15:08:19'),(93,26,3,'Venta',-2,7,42,NULL,'2026-10-14 15:08:19'),(94,27,4,'Venta',-1,5,43,NULL,'2026-10-16 11:06:42'),(95,21,4,'Venta',-3,19,44,NULL,'2026-10-19 12:48:07'),(96,26,4,'Venta',-1,6,44,NULL,'2026-10-19 12:48:07'),(97,27,4,'Venta',-3,2,44,NULL,'2026-10-19 12:48:07');
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `idord` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `idcli` int(11) DEFAULT NULL,
  `nomcl` text NOT NULL,
  `method` varchar(50) NOT NULL,
  `total_products` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `subtotal_sin_igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `placed_on` datetime NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `anulada` tinyint(1) NOT NULL DEFAULT 0,
  `anulada_on` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `reemplaza_a` int(11) DEFAULT NULL,
  `tipc` varchar(15) NOT NULL,
  `idserie` int(11) DEFAULT NULL,
  `serie` char(4) DEFAULT NULL,
  `numero` int(10) unsigned DEFAULT NULL,
  `doc_tipo` varchar(10) DEFAULT NULL,
  `doc_num` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`idord`),
  UNIQUE KEY `uq_orders_comprobante` (`serie`,`numero`),
  KEY `orders_ibfk_1` (`user_id`),
  KEY `idx_orders_cliente` (`idcli`),
  KEY `idx_orders_serie` (`idserie`),
  KEY `idx_orders_reemplaza` (`reemplaza_a`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`idcli`) REFERENCES `clientes` (`idcli`) ON UPDATE CASCADE,
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`idserie`) REFERENCES `series` (`idserie`),
  CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`reemplaza_a`) REFERENCES `orders` (`idord`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,NULL,'Manuel Jose Flores Ayala','Contado','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD ( 3 ),  PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 4 )',19668.00,16667.80,3000.20,'2026-08-17 00:00:00','Anulado',1,'2026-09-27 02:47:44','anulación',NULL,'Boleta',1,'B001',1,NULL,NULL),(2,1,NULL,'Renato Fautisno Velarde Trelles','Contado',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 3 )',9297.00,7878.81,1418.19,'2026-08-18 00:00:00','Anulado',1,'2026-09-27 02:54:56','a',NULL,'Boleta',1,'B001',2,NULL,NULL),(3,1,1,'Karla Solis Urbina','Contado',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 1 )',3099.00,2626.27,472.73,'2026-08-18 00:00:00','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',3,NULL,NULL),(4,1,1,'OSVALDO SALAZAR YOVERA','Contado','LAPTO hP ULTRA ( 1 )',2400.00,2033.90,366.10,'2026-08-19 00:00:00','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',4,NULL,NULL),(5,1,NULL,'Sofia Castillo Perez','Contado','LAPTOP HP ULTRA ( 1 )',2400.00,2033.90,366.10,'2026-09-11 00:00:00','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',5,NULL,NULL),(6,1,NULL,'Ana Vera Cruz','Tarjeta','LAPTOP HP ULTRA ( 1 )',2400.00,2033.90,366.10,'2026-09-11 00:00:00','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',6,NULL,NULL),(9,4,14,'ESTUDIO CONTABLE RAMOS & ASOCIADOS S.A.C.','Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI ( 3 )',2187.00,1853.39,333.61,'2026-09-15 15:21:17','Aceptado',0,NULL,NULL,NULL,'Factura',2,'F001',1,'RUC','20601589738'),(10,4,9,'Luis Fernando Mendoza Silva','Mixto','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220 ( 1 )',629.00,533.05,95.95,'2026-09-15 16:40:00','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',7,'DNI','72548963'),(11,1,1,'Julian Juarez Lopez','Contado','BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 1 )',49.00,41.53,7.47,'2026-09-17 15:39:11','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',8,'DNI','78885848'),(12,3,14,'ESTUDIO CONTABLE RAMOS & ASOCIADOS S.A.C.','Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI ( 3 )',2547.00,2158.47,388.53,'2026-09-17 17:29:27','Aceptado',0,NULL,NULL,NULL,'Factura',2,'F001',2,'RUC','20601589738'),(13,4,2,'Karla Martinez','Mixto','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI ( 1 ), CARTUCHO DE TINTA HP 662 NEGRO ( 1 )',798.00,676.27,121.73,'2026-09-18 16:38:41','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',9,'DNI','76546564'),(14,4,11,'Jorge Luis Castillo Vega','Yape','BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 1 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 2 )',287.00,243.22,43.78,'2026-09-19 09:09:08','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',10,'DNI','73659814'),(15,4,2,'Karla Martinez','Yape','BOTELLA DE TINTA EPSON T504 NEGRO 127ML ( 1 )',45.00,38.14,6.86,'2026-09-19 14:05:38','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',11,'DNI','76546564'),(16,1,12,'Gabriela Sánchez Díaz','Contado','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI ( 1 ), CARTUCHO DE TINTA HP 662 NEGRO ( 1 )',918.00,777.97,140.03,'2026-09-22 10:08:31','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',12,'DNI','40789652'),(17,3,2,'Karla Martinez','Contado','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI ( 1 ), BOTELLA DE TINTA EPSON T544 NEGRO 65ML ( 2 )',927.00,785.59,141.41,'2026-09-22 14:59:21','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',13,'DNI','76546564'),(18,4,NULL,'Pedro Alvarado','Contado','CARTUCHO DE TINTA HP 122 NEGRO ( 3 ), CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET ( 2 )',475.00,402.54,72.46,'2026-09-23 11:51:46','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',14,NULL,NULL),(19,4,10,'Patricia Gutiérrez Ramos','Yape','BOTELLA DE TINTA EPSON T544 NEGRO 65ML ( 3 ), BOTELLA DE TINTA EPSON T673 NEGRO 70ML ( 2 ), CARTUCHO DE TINTA HP 46 TRICOLOR ( 1 )',266.00,225.42,40.58,'2026-09-23 18:48:39','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',15,'DNI','46985231'),(20,4,1,'Julian Juarez Lopez','Contado','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI ( 1 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 2 )',967.00,819.49,147.51,'2026-09-24 12:44:56','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',16,'DNI','78885848'),(21,1,4,'Leonardo Flores','Contado','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L4260 DUPLEX WIFI ( 1 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 2 )',1337.00,1133.05,203.95,'2026-09-24 13:52:55','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',17,'DNI','76564564'),(22,3,1,'Julian Juarez Lopez','Yape','BOTELLA DE TINTA HP GT52 CIAN 70ML ( 2 )',78.00,66.10,11.90,'2026-09-25 10:59:44','Anulado',1,'2026-09-26 10:20:00','El cliente desistió de la compra y se le devolvió el dinero',NULL,'Boleta',1,'B001',18,'DNI','78885848'),(23,4,16,'QUISPE HUAMAN MARIA ELENA','Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220 ( 3 ), BOTELLA DE TINTA HP GT52 CIAN 70ML ( 2 )',1965.00,1665.25,299.75,'2026-09-26 12:45:30','Aceptado',0,NULL,NULL,NULL,'Factura',2,'F001',3,'RUC','10458712366'),(24,4,6,'María Elena Quispe Huamán','Contado','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210 ( 1 )',699.00,592.37,106.63,'2026-09-28 14:41:38','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',19,'DNI','45871236'),(25,4,2,'Karla Martinez','Yape','BOTELLA DE TINTA EPSON T504 NEGRO 127ML ( 1 ), BOTELLA DE TINTA EPSON T664 NEGRO 70ML ( 1 ), CARTUCHO DE TINTA HP 662 NEGRO ( 2 )',218.00,184.75,33.25,'2026-09-30 13:54:16','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',20,'DNI','76546564'),(26,1,14,'ESTUDIO CONTABLE RAMOS & ASOCIADOS S.A.C.','Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI ( 2 ), CARTUCHO DE TINTA HP 662 NEGRO ( 2 )',2236.00,1894.92,341.08,'2026-09-30 17:26:12','Aceptado',0,NULL,NULL,NULL,'Factura',2,'F001',4,'RUC','20601589738'),(27,3,11,'Jorge Luis Castillo Vega','Yape','BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 1 )',49.00,41.53,7.47,'2026-10-01 13:38:03','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',21,'DNI','73659814'),(28,4,1,'Julian Juarez Lopez','Contado','IMPRESORA MULTIFUNCIONAL BROTHER MFC-T920DW DUPLEX WIFI FAX ( 1 )',1299.00,1100.85,198.15,'2026-10-01 15:16:22','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',22,'DNI','78885848'),(29,4,4,'Leonardo Flores','Contado','CARTUCHO DE TINTA HP 662 NEGRO ( 3 )',207.00,175.42,31.58,'2026-10-02 09:03:47','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',23,'DNI','76564564'),(30,4,NULL,'Miguel Ángel Torres','Contado','BOTELLA DE TINTA HP GT52 CIAN 70ML ( 2 )',78.00,66.10,11.90,'2026-10-02 15:52:52','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',24,NULL,NULL),(31,1,8,'Rosa Isabel Chávez Torres','Mixto','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220 ( 1 ), BOTELLA DE TINTA HP GT52 CIAN 70ML ( 2 )',707.00,599.15,107.85,'2026-10-03 12:12:20','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',25,'DNI','41236985'),(32,3,12,'Gabriela Sánchez Díaz','Tarjeta','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L6270 DUPLEX WIFI ( 1 ), BOTELLA DE TINTA EPSON T664 NEGRO 70ML ( 2 )',1469.00,1244.92,224.08,'2026-10-05 13:27:12','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',26,'DNI','40789652'),(33,4,11,'Jorge Luis Castillo Vega','Contado','BOTELLA DE TINTA EPSON T664 NEGRO 70ML ( 1 ), CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET ( 2 )',333.00,282.20,50.80,'2026-10-05 16:21:14','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',27,'DNI','73659814'),(34,4,4,'Leonardo Flores','Mixto','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210 ( 1 )',699.00,592.37,106.63,'2026-10-07 17:47:52','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',28,'DNI','76564564'),(35,4,8,'Rosa Isabel Chávez Torres','Tarjeta','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI ( 1 )',1049.00,888.98,160.02,'2026-10-09 10:16:39','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',29,'DNI','41236985'),(36,1,1,'Julian Juarez Lopez','Contado','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI ( 1 )',1149.00,973.73,175.27,'2026-10-09 15:49:23','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',30,'DNI','78885848'),(37,3,12,'Gabriela Sánchez Díaz','Tarjeta','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF ( 1 ), BOTELLA DE TINTA EPSON T544 NEGRO 65ML ( 2 )',1277.00,1082.20,194.80,'2026-10-10 15:05:46','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',31,'DNI','40789652'),(38,4,16,'QUISPE HUAMAN MARIA ELENA','Transferencia','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3260 WIFI ( 3 ), BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 2 )',2645.00,2241.53,403.47,'2026-10-10 15:57:54','Aceptado',0,NULL,NULL,NULL,'Factura',2,'F001',5,'RUC','10458712366'),(39,4,10,'Patricia Gutiérrez Ramos','Contado','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI ( 1 ), CARTUCHO DE TINTA HP 122 NEGRO ( 2 )',917.00,777.12,139.88,'2026-10-13 11:06:33','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',32,'DNI','46985231'),(40,4,12,'Gabriela Sánchez Díaz','Yape','BOTELLA DE TINTA EPSON T504 NEGRO 127ML ( 1 ), BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 3 )',192.00,162.71,29.29,'2026-10-13 14:55:14','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',33,'DNI','40789652'),(41,1,10,'Patricia Gutiérrez Ramos','Contado','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI ( 1 )',1149.00,973.73,175.27,'2026-10-14 10:12:51','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',34,'DNI','46985231'),(42,3,10,'Patricia Gutiérrez Ramos','Contado','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF ( 1 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 2 )',1437.00,1217.80,219.20,'2026-10-14 15:08:19','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',35,'DNI','46985231'),(43,4,NULL,'CLIENTES VARIOS','Yape','CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET ( 1 )',149.00,126.27,22.73,'2026-10-16 11:06:42','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',36,NULL,NULL),(44,4,11,'Jorge Luis Castillo Vega','Contado','BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 3 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 1 ), CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET ( 3 )',713.00,604.24,108.76,'2026-10-19 12:48:07','Aceptado',0,NULL,NULL,NULL,'Boleta',1,'B001',37,'DNI','73659814');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders_detalle`
--

DROP TABLE IF EXISTS `orders_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_detalle` (
  `iddet` int(11) NOT NULL AUTO_INCREMENT,
  `idord` int(11) NOT NULL,
  `idprod` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre del producto al momento de la venta',
  `precio` decimal(10,2) NOT NULL COMMENT 'Precio hist├│rico, no el actual',
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`iddet`),
  KEY `idx_orders_detalle_orden` (`idord`),
  KEY `idx_orders_detalle_producto` (`idprod`),
  CONSTRAINT `orders_detalle_ibfk_1` FOREIGN KEY (`idord`) REFERENCES `orders` (`idord`),
  CONSTRAINT `orders_detalle_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_detalle`
--

LOCK TABLES `orders_detalle` WRITE;
/*!40000 ALTER TABLE `orders_detalle` DISABLE KEYS */;
INSERT INTO `orders_detalle` VALUES (3,1,1,'LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD',2424.00,3,7272.00),(4,1,2,' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP',3099.00,4,12396.00),(5,2,2,' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP',3099.00,3,9297.00),(6,3,2,' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP',3099.00,1,3099.00),(7,4,4,'LAPTOP HP ULTRA',2400.00,1,2400.00),(8,5,4,'LAPTOP HP ULTRA',2400.00,1,2400.00),(9,6,4,'LAPTOP HP ULTRA',2400.00,1,2400.00),(10,9,12,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI',729.00,3,2187.00),(11,10,11,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220',629.00,1,629.00),(12,11,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,1,49.00),(13,12,13,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI',849.00,3,2547.00),(14,13,12,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI',729.00,1,729.00),(15,13,24,'CARTUCHO DE TINTA HP 662 NEGRO',69.00,1,69.00),(16,14,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,1,49.00),(17,14,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',119.00,2,238.00),(18,15,17,'BOTELLA DE TINTA EPSON T504 NEGRO 127ML',45.00,1,45.00),(19,16,13,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI',849.00,1,849.00),(20,16,24,'CARTUCHO DE TINTA HP 662 NEGRO',69.00,1,69.00),(21,17,13,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI',849.00,1,849.00),(22,17,18,'BOTELLA DE TINTA EPSON T544 NEGRO 65ML',39.00,2,78.00),(23,18,25,'CARTUCHO DE TINTA HP 122 NEGRO',59.00,3,177.00),(24,18,27,'CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET',149.00,2,298.00),(25,19,18,'BOTELLA DE TINTA EPSON T544 NEGRO 65ML',39.00,3,117.00),(26,19,20,'BOTELLA DE TINTA EPSON T673 NEGRO 70ML',45.00,2,90.00),(27,19,23,'CARTUCHO DE TINTA HP 46 TRICOLOR',59.00,1,59.00),(28,20,12,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI',729.00,1,729.00),(29,20,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',119.00,2,238.00),(30,21,8,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L4260 DUPLEX WIFI',1099.00,1,1099.00),(31,21,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',119.00,2,238.00),(32,22,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',39.00,2,78.00),(33,23,11,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220',629.00,3,1887.00),(34,23,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',39.00,2,78.00),(35,24,5,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210',699.00,1,699.00),(36,25,17,'BOTELLA DE TINTA EPSON T504 NEGRO 127ML',45.00,1,45.00),(37,25,19,'BOTELLA DE TINTA EPSON T664 NEGRO 70ML',35.00,1,35.00),(38,25,24,'CARTUCHO DE TINTA HP 662 NEGRO',69.00,2,138.00),(39,26,14,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI',1049.00,2,2098.00),(40,26,24,'CARTUCHO DE TINTA HP 662 NEGRO',69.00,2,138.00),(41,27,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,1,49.00),(42,28,16,'IMPRESORA MULTIFUNCIONAL BROTHER MFC-T920DW DUPLEX WIFI FAX',1299.00,1,1299.00),(43,29,24,'CARTUCHO DE TINTA HP 662 NEGRO',69.00,3,207.00),(44,30,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',39.00,2,78.00),(45,31,11,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220',629.00,1,629.00),(46,31,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',39.00,2,78.00),(47,32,10,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L6270 DUPLEX WIFI',1399.00,1,1399.00),(48,32,19,'BOTELLA DE TINTA EPSON T664 NEGRO 70ML',35.00,2,70.00),(49,33,19,'BOTELLA DE TINTA EPSON T664 NEGRO 70ML',35.00,1,35.00),(50,33,27,'CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET',149.00,2,298.00),(51,34,5,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210',699.00,1,699.00),(52,35,14,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI',1049.00,1,1049.00),(53,36,15,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI',1149.00,1,1149.00),(54,37,9,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF',1199.00,1,1199.00),(55,37,18,'BOTELLA DE TINTA EPSON T544 NEGRO 65ML',39.00,2,78.00),(56,38,7,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3260 WIFI',849.00,3,2547.00),(57,38,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,2,98.00),(58,39,6,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI',799.00,1,799.00),(59,39,25,'CARTUCHO DE TINTA HP 122 NEGRO',59.00,2,118.00),(60,40,17,'BOTELLA DE TINTA EPSON T504 NEGRO 127ML',45.00,1,45.00),(61,40,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,3,147.00),(62,41,15,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI',1149.00,1,1149.00),(63,42,9,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF',1199.00,1,1199.00),(64,42,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',119.00,2,238.00),(65,43,27,'CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET',149.00,1,149.00),(66,44,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',49.00,3,147.00),(67,44,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',119.00,1,119.00),(68,44,27,'CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET',149.00,3,447.00);
/*!40000 ALTER TABLE `orders_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders_pagos`
--

DROP TABLE IF EXISTS `orders_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_pagos` (
  `idpago` int(11) NOT NULL AUTO_INCREMENT,
  `idord` int(11) NOT NULL,
  `metodo` varchar(20) NOT NULL COMMENT 'Contado, Tarjeta, Yape, Plin o Transferencia',
  `monto` decimal(10,2) NOT NULL,
  `num_operacion` varchar(30) DEFAULT NULL COMMENT 'obligatorio en Yape, Plin y Transferencia',
  PRIMARY KEY (`idpago`),
  KEY `idx_pagos_venta` (`idord`),
  KEY `idx_pagos_metodo` (`metodo`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`idord`) REFERENCES `orders` (`idord`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_pagos`
--

LOCK TABLES `orders_pagos` WRITE;
/*!40000 ALTER TABLE `orders_pagos` DISABLE KEYS */;
INSERT INTO `orders_pagos` VALUES (1,1,'Contado',19668.00,NULL),(2,2,'Contado',9297.00,NULL),(3,3,'Contado',3099.00,NULL),(4,4,'Contado',2400.00,NULL),(5,5,'Contado',2400.00,NULL),(6,6,'Tarjeta',2400.00,NULL),(8,9,'Transferencia',2187.00,'OP25970382'),(9,10,'Tarjeta',315.00,NULL),(10,10,'Contado',314.00,NULL),(11,11,'Contado',49.00,NULL),(12,12,'Transferencia',2547.00,'OP93891039'),(13,13,'Tarjeta',399.00,NULL),(14,13,'Contado',399.00,NULL),(15,14,'Yape',287.00,'56217921'),(16,15,'Yape',45.00,'95128649'),(17,16,'Contado',918.00,NULL),(18,17,'Contado',927.00,NULL),(19,18,'Contado',475.00,NULL),(20,19,'Yape',266.00,'65605045'),(21,20,'Contado',967.00,NULL),(22,21,'Contado',1337.00,NULL),(23,22,'Yape',78.00,'67585255'),(24,23,'Transferencia',1965.00,'OP19904160'),(25,24,'Contado',699.00,NULL),(26,25,'Yape',218.00,'44951923'),(27,26,'Transferencia',2236.00,'OP13395640'),(28,27,'Yape',49.00,'30577535'),(29,28,'Contado',1299.00,NULL),(30,29,'Contado',207.00,NULL),(31,30,'Contado',78.00,NULL),(32,31,'Tarjeta',354.00,NULL),(33,31,'Contado',353.00,NULL),(34,32,'Tarjeta',1469.00,NULL),(35,33,'Contado',333.00,NULL),(36,34,'Tarjeta',350.00,NULL),(37,34,'Contado',349.00,NULL),(38,35,'Tarjeta',1049.00,NULL),(39,36,'Contado',1149.00,NULL),(40,37,'Tarjeta',1277.00,NULL),(41,38,'Transferencia',2645.00,'OP42792138'),(42,39,'Contado',917.00,NULL),(43,40,'Yape',192.00,'56962342'),(44,41,'Contado',1149.00,NULL),(45,42,'Contado',1437.00,NULL),(46,43,'Yape',149.00,'86104442'),(47,44,'Contado',713.00,NULL);
/*!40000 ALTER TABLE `orders_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders_purchase`
--

DROP TABLE IF EXISTS `orders_purchase`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_purchase` (
  `idordpur` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `idprov` int(11) NOT NULL,
  `method` varchar(50) NOT NULL,
  `total_products` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `subtotal_sin_igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `placed_on` datetime NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `tipc` varchar(15) NOT NULL,
  `anulada` tinyint(1) NOT NULL DEFAULT 0,
  `anulada_on` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `serie_doc` char(4) DEFAULT NULL,
  `numero_doc` char(8) DEFAULT NULL,
  `fecha_emision` date DEFAULT NULL,
  `modificada_on` datetime DEFAULT NULL,
  PRIMARY KEY (`idordpur`),
  UNIQUE KEY `uq_compra_comprobante` (`idprov`,`tipc`,`serie_doc`,`numero_doc`),
  KEY `idprov` (`idprov`),
  KEY `idx_orders_purchase_usuario` (`user_id`),
  CONSTRAINT `orders_purchase_ibfk_1` FOREIGN KEY (`idprov`) REFERENCES `proveedores` (`idprov`),
  CONSTRAINT `orders_purchase_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_purchase`
--

LOCK TABLES `orders_purchase` WRITE;
/*!40000 ALTER TABLE `orders_purchase` DISABLE KEYS */;
INSERT INTO `orders_purchase` VALUES (1,1,1,'Contado','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD ( 1 ),  PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 3 )',11721.00,9933.05,1787.95,'2026-08-18 00:00:00','Aceptado','Boleta',0,NULL,NULL,NULL,NULL,NULL,NULL),(2,1,5,'Transferencia','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210 ( 8 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI ( 8 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3260 WIFI ( 6 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L4260 DUPLEX WIFI ( 5 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF ( 4 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L6270 DUPLEX WIFI ( 3 ), BOTELLA DE TINTA EPSON T504 NEGRO 127ML ( 40 ), BOTELLA DE TINTA EPSON T544 NEGRO 65ML ( 40 ), BOTELLA DE TINTA EPSON T664 NEGRO 70ML ( 30 ), BOTELLA DE TINTA EPSON T673 NEGRO 70ML ( 20 )',29069.30,24635.00,4434.30,'2026-08-25 10:30:00','Aceptado','Factura',0,NULL,NULL,'F001','00004587','2026-08-25',NULL),(3,1,6,'Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220 ( 6 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI ( 6 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI ( 5 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI ( 4 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI ( 3 ), IMPRESORA MULTIFUNCIONAL BROTHER MFC-T920DW DUPLEX WIFI FAX ( 3 )',19062.90,16155.00,2907.90,'2026-08-28 15:10:00','Aceptado','Factura',0,NULL,NULL,'E001','00001203','2026-08-28',NULL),(4,3,1,'Contado','CARTUCHO DE TINTA HP 46 TRICOLOR ( 30 ), CARTUCHO DE TINTA HP 662 NEGRO ( 30 ), CARTUCHO DE TINTA HP 122 NEGRO ( 25 ), CARTUCHO DE TINTA HP 22 TRICOLOR ( 15 ), CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET ( 10 ), BOTELLA DE TINTA HP GT52 CIAN 70ML ( 30 ), BOTELLA DE TINTA CANON GI-190 NEGRO 135ML ( 30 )',8472.40,7180.00,1292.40,'2026-09-02 11:45:00','Aceptado','Factura',0,NULL,NULL,'F002','00008841','2026-09-02',NULL),(5,1,5,'Transferencia','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210 ( 6 ), IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI ( 6 ), BOTELLA DE TINTA EPSON T504 NEGRO 127ML ( 30 ), BOTELLA DE TINTA EPSON T544 NEGRO 65ML ( 30 ), BOTELLA DE TINTA EPSON T664 NEGRO 70ML ( 20 )',9487.20,8040.00,1447.20,'2026-09-25 09:50:00','Aceptado','Factura',0,NULL,NULL,'F001','00004795','2026-09-25',NULL),(6,3,2,'Transferencia','CARTUCHO DE TINTA HP 46 TRICOLOR ( 20 ), CARTUCHO DE TINTA HP 662 NEGRO ( 20 ), BOTELLA DE TINTA HP GT52 CIAN 70ML ( 20 )',2501.60,2120.00,381.60,'2026-10-08 16:20:00','Aceptado','Factura',0,NULL,NULL,'F001','00015320','2026-10-08',NULL),(7,1,6,'Transferencia','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220 ( 4 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI ( 4 ), IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI ( 3 )',6330.70,5365.00,965.70,'2026-10-09 10:15:00','Aceptado','Factura',0,NULL,NULL,'E001','00001288','2026-10-09',NULL);
/*!40000 ALTER TABLE `orders_purchase` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders_purchase_detalle`
--

DROP TABLE IF EXISTS `orders_purchase_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders_purchase_detalle` (
  `iddet` int(11) NOT NULL AUTO_INCREMENT,
  `idordpur` int(11) NOT NULL,
  `idprod` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`iddet`),
  KEY `idx_opd_orden` (`idordpur`),
  KEY `idx_opd_producto` (`idprod`),
  CONSTRAINT `opd_ibfk_1` FOREIGN KEY (`idordpur`) REFERENCES `orders_purchase` (`idordpur`),
  CONSTRAINT `opd_ibfk_2` FOREIGN KEY (`idprod`) REFERENCES `productos` (`idprod`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_purchase_detalle`
--

LOCK TABLES `orders_purchase_detalle` WRITE;
/*!40000 ALTER TABLE `orders_purchase_detalle` DISABLE KEYS */;
INSERT INTO `orders_purchase_detalle` VALUES (1,1,1,'LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD',2054.24,1,2054.24),(2,1,2,' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP',2626.27,3,7878.81),(4,2,5,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210',470.00,8,3760.00),(5,2,6,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI',540.00,8,4320.00),(6,2,7,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3260 WIFI',575.00,6,3450.00),(7,2,8,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L4260 DUPLEX WIFI',745.00,5,3725.00),(8,2,9,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF',815.00,4,3260.00),(9,2,10,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L6270 DUPLEX WIFI',950.00,3,2850.00),(10,2,17,'BOTELLA DE TINTA EPSON T504 NEGRO 127ML',28.00,40,1120.00),(11,2,18,'BOTELLA DE TINTA EPSON T544 NEGRO 65ML',24.00,40,960.00),(12,2,19,'BOTELLA DE TINTA EPSON T664 NEGRO 70ML',21.00,30,630.00),(13,2,20,'BOTELLA DE TINTA EPSON T673 NEGRO 70ML',28.00,20,560.00),(14,3,11,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220',420.00,6,2520.00),(15,3,12,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI',490.00,6,2940.00),(16,3,13,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI',575.00,5,2875.00),(17,3,14,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI',710.00,4,2840.00),(18,3,15,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI',780.00,3,2340.00),(19,3,16,'IMPRESORA MULTIFUNCIONAL BROTHER MFC-T920DW DUPLEX WIFI FAX',880.00,3,2640.00),(20,4,23,'CARTUCHO DE TINTA HP 46 TRICOLOR',38.00,30,1140.00),(21,4,24,'CARTUCHO DE TINTA HP 662 NEGRO',44.00,30,1320.00),(22,4,25,'CARTUCHO DE TINTA HP 122 NEGRO',38.00,25,950.00),(23,4,26,'CARTUCHO DE TINTA HP 22 TRICOLOR',78.00,15,1170.00),(24,4,27,'CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET',98.00,10,980.00),(25,4,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',24.00,30,720.00),(26,4,21,'BOTELLA DE TINTA CANON GI-190 NEGRO 135ML',30.00,30,900.00),(27,5,5,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210',470.00,6,2820.00),(28,5,6,'IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI',540.00,6,3240.00),(29,5,17,'BOTELLA DE TINTA EPSON T504 NEGRO 127ML',28.00,30,840.00),(30,5,18,'BOTELLA DE TINTA EPSON T544 NEGRO 65ML',24.00,30,720.00),(31,5,19,'BOTELLA DE TINTA EPSON T664 NEGRO 70ML',21.00,20,420.00),(32,6,23,'CARTUCHO DE TINTA HP 46 TRICOLOR',38.00,20,760.00),(33,6,24,'CARTUCHO DE TINTA HP 662 NEGRO',44.00,20,880.00),(34,6,22,'BOTELLA DE TINTA HP GT52 CIAN 70ML',24.00,20,480.00),(35,7,11,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220',420.00,4,1680.00),(36,7,12,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI',490.00,4,1960.00),(37,7,13,'IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI',575.00,3,1725.00);
/*!40000 ALTER TABLE `orders_purchase_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `idprod` int(11) NOT NULL AUTO_INCREMENT,
  `codpro` char(14) NOT NULL,
  `nomprd` text NOT NULL,
  `desprd` text NOT NULL,
  `foto` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Ultimo costo unitario sin IGV',
  `stock` int(11) NOT NULL DEFAULT 0,
  `garantia_meses` tinyint(3) unsigned NOT NULL DEFAULT 12,
  `idmar` int(11) NOT NULL,
  `idcate` int(11) NOT NULL,
  `modelo` text NOT NULL,
  `peso` text NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idprod`),
  UNIQUE KEY `uq_productos_codpro` (`codpro`),
  KEY `idmar` (`idmar`),
  KEY `idcate` (`idcate`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`idmar`) REFERENCES `marca` (`idmar`),
  CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`idcate`) REFERENCES `categoria` (`idcate`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'33333333333333','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD','Procesador: Intel Core i3\r\nTipo de disco duro: SATA\r\nTarjeta gráfica: Intel® UHD Graphics\r\nDisco Duro: 1TB\r\nMemoria RAM: 4GB','825226.jpg',2424.00,0.00,99,12,1,2,'Lenovo','A partir de 1.6 Kg',1,'2026-09-15 03:17:45'),(2,'74355345345324',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP','Intel® Core™ i5-10400T (2.0 / 3.6 GHz, 6 núcleos) Décima generación \r\nRAM 8 GB DDR4 ampliable\r\nDisco Duro de 1TB SATA\r\nPantalla 24\" Full HD (1920x1080) IPS, marcos reducidos.\r\nWiFi, Buetooth, Cámara web 720p\r\nTeclado & Mouse alámbricos\r\nWindows 10 Home SP','651402.jpg',3099.00,0.00,20,12,1,4,'Lenovo','30kg',1,'2026-08-17 02:26:23'),(4,'96785756756756','LAPTOP HP ULTRA','ESTA ES UNA LAPTOP HP ULTRA','211473.jpg',2400.00,0.00,90,12,2,2,'hP','100',1,'2026-09-15 03:35:05'),(5,'77500000001001','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3210','Imprime, copia y escanea\r\nSistema de tanque de tinta EcoTank\r\nHasta 33 ppm en negro\r\nConexión USB','l3210.png',699.00,470.00,12,12,3,7,'L3210','3.9 Kg',1,'2026-10-20 02:11:17'),(6,'77500000001038','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3250 WIFI','Imprime, copia y escanea\r\nWiFi y WiFi Direct\r\nImpresión desde el celular con Epson Smart Panel','L3250.png',799.00,540.00,13,12,3,7,'L3250','3.9 Kg',1,'2026-10-20 02:11:17'),(7,'77500000001075','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L3260 WIFI','Imprime, copia y escanea\r\nWiFi, pantalla LCD de 1.44\"\r\nHasta 33 ppm en negro','L3260.png',849.00,575.00,3,12,3,7,'L3260','4.0 Kg',1,'2026-10-20 02:11:17'),(8,'77500000001112','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L4260 DUPLEX WIFI','Impresión dúplex automática\r\nWiFi y WiFi Direct\r\nPantalla LCD de 1.44\"','l4260.png',1099.00,745.00,4,12,3,7,'L4260','5.0 Kg',1,'2026-10-20 02:11:17'),(9,'77500000001149','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L5290 WIFI ADF','Imprime, copia, escanea y envía fax\r\nAlimentador automático de 30 hojas\r\nWiFi y Ethernet','L5290.png',1199.00,815.00,2,12,3,7,'L5290','6.7 Kg',1,'2026-10-20 02:11:17'),(10,'77500000001186','IMPRESORA MULTIFUNCIONAL EPSON ECOTANK L6270 DUPLEX WIFI','Dúplex automático\r\nWiFi, WiFi Direct y Ethernet\r\nHasta 15.5 ppm ISO','L6270.png',1399.00,950.00,2,12,3,7,'L6270','6.6 Kg',1,'2026-10-20 02:11:17'),(11,'77500000001223','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T220','Imprime, copia y escanea\r\nSistema de tanque de tinta\r\nConexión USB','t220.jpg',629.00,420.00,5,12,4,7,'DCP-T220','6.2 Kg',1,'2026-10-20 02:11:17'),(12,'77500000001260','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T420W WIFI','Imprime, copia y escanea\r\nWiFi\r\nHasta 28 ppm en negro','t420.jpg',729.00,490.00,5,12,4,7,'DCP-T420W','6.2 Kg',1,'2026-10-20 02:11:17'),(13,'77500000001297','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T520W WIFI','Imprime, copia y escanea\r\nWiFi y WiFi Direct\r\nHasta 30 ppm en negro','t520.png',849.00,575.00,3,12,4,7,'DCP-T520W','7.0 Kg',1,'2026-10-20 02:11:17'),(14,'77500000001334','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T720DW DUPLEX WIFI','Dúplex automático\r\nAlimentador automático de 20 hojas\r\nWiFi','t720.png',1049.00,710.00,1,12,4,7,'DCP-T720DW','8.8 Kg',1,'2026-10-20 02:11:17'),(15,'77500000001371','IMPRESORA MULTIFUNCIONAL BROTHER DCP-T820DW DUPLEX WIFI','Dúplex automático\r\nWiFi y Ethernet\r\nPantalla LCD','t820.png',1149.00,780.00,1,12,4,7,'DCP-T820DW','9.0 Kg',1,'2026-10-20 02:11:17'),(16,'77500000001408','IMPRESORA MULTIFUNCIONAL BROTHER MFC-T920DW DUPLEX WIFI FAX','Imprime, copia, escanea y envía fax\r\nDúplex automático\r\nWiFi y Ethernet','t920.png',1299.00,880.00,2,12,4,7,'MFC-T920DW','9.3 Kg',1,'2026-10-20 02:11:17'),(17,'77500000001445','BOTELLA DE TINTA EPSON T504 NEGRO 127ML','Tinta original Epson T504\r\nCompatible con L4150, L4160, L6161, L6171','504.jpg',45.00,28.00,67,3,3,11,'T504120','0.15 Kg',1,'2026-10-20 02:11:17'),(18,'77500000001482','BOTELLA DE TINTA EPSON T544 NEGRO 65ML','Tinta original Epson T544\r\nCompatible con L3110, L3150, L3210, L3250','544epson.jpg',39.00,24.00,63,3,3,11,'T544120','0.10 Kg',1,'2026-10-20 02:11:17'),(19,'77500000001519','BOTELLA DE TINTA EPSON T664 NEGRO 70ML','Tinta original Epson T664\r\nCompatible con L120, L220, L375, L395','664epson.jpg',35.00,21.00,46,3,3,11,'T664120','0.10 Kg',1,'2026-10-20 02:11:17'),(20,'77500000001556','BOTELLA DE TINTA EPSON T673 NEGRO 70ML','Tinta original Epson T673\r\nCompatible con L800, L805, L1800','673epson.jpg',45.00,28.00,18,3,3,11,'T673120','0.10 Kg',1,'2026-10-20 02:11:17'),(21,'77500000001593','BOTELLA DE TINTA CANON GI-190 NEGRO 135ML','Tinta original Canon GI-190\r\nCompatible con PIXMA G1110, G2110, G3110','190canon.jpg',49.00,30.00,19,3,5,11,'GI-190 BK','0.16 Kg',1,'2026-10-20 02:11:17'),(22,'77500000001630','BOTELLA DE TINTA HP GT52 CIAN 70ML','Tinta original HP GT52\r\nCompatible con Ink Tank 315, 415 y Smart Tank 515','gt52hp.jpg',39.00,24.00,44,3,2,11,'M0H54AL','0.10 Kg',1,'2026-10-20 02:11:17'),(23,'77500000001667','CARTUCHO DE TINTA HP 46 TRICOLOR','Cartucho original HP 46 Ink Advantage\r\nCompatible con DeskJet 2020hc, 2520hc, 4729','46hp.jpg',59.00,38.00,49,3,2,11,'CZ638AL','0.05 Kg',1,'2026-10-20 02:11:17'),(24,'77500000001704','CARTUCHO DE TINTA HP 662 NEGRO','Cartucho original HP 662 Ink Advantage\r\nCompatible con DeskJet 1015, 2515, 3515','662hp.jpg',69.00,44.00,41,3,2,11,'CZ103AL','0.05 Kg',1,'2026-10-20 02:11:17'),(25,'77500000001741','CARTUCHO DE TINTA HP 122 NEGRO','Cartucho original HP 122\r\nCompatible con DeskJet 1000, 2050, 3050','hp122.jpg',59.00,38.00,20,3,2,11,'CH561HL','0.05 Kg',1,'2026-10-20 02:11:17'),(26,'77500000001778','CARTUCHO DE TINTA HP 22 TRICOLOR','Cartucho original HP 22\r\nCompatible con DeskJet 3920, PSC 1410','22hp.jpg',119.00,78.00,6,3,2,11,'C9352AL','0.05 Kg',1,'2026-10-20 02:11:17'),(27,'77500000001815','CARTUCHO DE TINTA HP 712 NEGRO 38ML DESIGNJET','Cartucho original HP 712\r\nCompatible con DesignJet T230, T630','712hp.jpg',149.00,98.00,2,3,2,11,'3ED70A','0.06 Kg',1,'2026-10-20 02:11:17');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `idprov` int(11) NOT NULL AUTO_INCREMENT,
  `rucprv` char(11) NOT NULL,
  `nomprv` text NOT NULL,
  `corrprv` varchar(35) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idprov`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'20568005491','TIENDAS DE COMPUTO EIRL','',1,'2026-08-15 19:14:16'),(2,'20511697353','EQUIPOS Y ACCESORIOS DE COMPUTO S.A.C','',1,'2026-08-15 19:15:17'),(4,'10399333426','MANRIQUE SA','',1,'2026-08-19 08:58:35'),(5,'20604123870','DISTRIBUIDORA TECNOLOGICA DEL NORTE S.A.C.','ventas@distecnorte.pe',1,'2026-08-20 14:15:00'),(6,'20558931745','IMPORTACIONES PRINT PERU E.I.R.L.','pedidos@printperu.pe',1,'2026-08-20 14:18:00');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series` (
  `idserie` int(11) NOT NULL AUTO_INCREMENT,
  `tipc` varchar(15) NOT NULL COMMENT 'Boleta o Factura',
  `serie` char(4) NOT NULL,
  `ultimo` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'ultimo numero emitido',
  `state` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idserie`),
  UNIQUE KEY `uq_series` (`tipc`,`serie`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series`
--

LOCK TABLES `series` WRITE;
/*!40000 ALTER TABLE `series` DISABLE KEYS */;
INSERT INTO `series` VALUES (1,'Boleta','B001',37,1),(2,'Factura','F001',5,1);
/*!40000 ALTER TABLE `series` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(35) NOT NULL,
  `username` varchar(25) NOT NULL,
  `correo` varchar(35) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` tinyint(1) NOT NULL DEFAULT 3 COMMENT '1=Administrador, 2=Cajero, 3=Empleado',
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `state` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador1','admin01','admin01@gmail.com','$2y$10$cuDEfnABx26/QlkBCqbB7.3FJRgo2Lt3T3UZX5LI4kquezVLxAgQC',1,'2026-09-15 03:11:57',1),(3,'Jorge Luna','admin02','jorge@gmail.com','$2y$10$cuDEfnABx26/QlkBCqbB7.3FJRgo2Lt3T3UZX5LI4kquezVLxAgQC',1,'2026-09-15 03:11:57',1),(4,'Ana Cajera','cajero01','cajero@ck.cl','$2y$10$eZp4canmuAHv3wT4Pu1rP.KzS93BKc/OkJCMauKOd7XT5SEfh9rBe',2,'2026-09-15 03:14:33',1),(5,'Beto Empleado','emple01','emple@ck.cl','$2y$10$qwHYEBz16cZnOP/FFcEKleJql1/2GH1/sUkAIYZobqNnt0.tQJPt6',3,'2026-09-15 03:14:33',1);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ckcomputers_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
