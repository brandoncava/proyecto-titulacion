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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_purchase`
--

LOCK TABLES `cart_purchase` WRITE;
/*!40000 ALTER TABLE `cart_purchase` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categoria`
--

LOCK TABLES `categoria` WRITE;
/*!40000 ALTER TABLE `categoria` DISABLE KEYS */;
INSERT INTO `categoria` VALUES (2,'LAPTOPS',0,'2026-09-15 03:20:47'),(4,'PCS',0,'2026-09-15 03:20:53'),(5,'SERVIDORESS',1,'2026-09-15 03:21:06'),(6,'PROYECTORES',1,'2026-08-15 02:16:16'),(7,'IMPRESORAS',1,'2026-08-15 02:35:15'),(8,'MONITORES',1,'2026-08-15 02:16:44'),(9,'COMPONENTES',1,'2026-08-15 02:17:11');
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
  `nudoc` char(8) NOT NULL,
  `nocl` varchar(35) NOT NULL,
  `apcl` varchar(35) NOT NULL,
  `telfcl` char(9) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idcli`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'dni','78885848','Julian','Juarez Lopez','968586757',1,'2026-09-11 04:57:39'),(2,'dni','76546564','Karla','Martinez','976575665',1,'2026-08-15 08:08:04'),(4,'dni','76564564','Leonardo','Flores','986858658',1,'2026-08-19 08:56:52');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marca`
--

LOCK TABLES `marca` WRITE;
/*!40000 ALTER TABLE `marca` DISABLE KEYS */;
INSERT INTO `marca` VALUES (1,'Lenovo',1,'2026-08-16 01:24:00'),(2,'hp',1,'2026-08-19 09:00:23');
/*!40000 ALTER TABLE `marca` ENABLE KEYS */;
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
  `nomcl` text NOT NULL,
  `method` varchar(50) NOT NULL,
  `total_products` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `placed_on` datetime NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `anulada` tinyint(1) NOT NULL DEFAULT 0,
  `anulada_on` datetime DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `tipc` varchar(15) NOT NULL,
  PRIMARY KEY (`idord`),
  KEY `orders_ibfk_1` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,1,'Manuel Jose Flores Ayala','Contado','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD ( 3 ),  PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 4 )',19668.00,'2026-08-17 00:00:00','Anulado',1,'2026-09-27 02:47:44','anulación','Boleta'),(2,1,'Renato Fautisno Velarde Trelles','Contado',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 3 )',9297.00,'2026-08-18 00:00:00','Anulado',1,'2026-09-27 02:54:56','a','Boleta'),(3,1,'Karla Solis Urbina','Contado',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 1 )',3099.00,'2026-08-18 00:00:00','Aceptado',0,NULL,NULL,'Boleta'),(4,1,'OSVALDO SALAZAR YOVERA','Contado','LAPTO hP ULTRA ( 1 )',2400.00,'2026-08-19 00:00:00','Aceptado',0,NULL,NULL,'Boleta'),(5,1,'Sofia Castillo Perez','Contado','LAPTOP HP ULTRA ( 1 )',2400.00,'2026-09-11 00:00:00','Aceptado',0,NULL,NULL,'Boleta'),(6,1,'Ana Vera Cruz','Tarjeta','LAPTOP HP ULTRA ( 1 )',2400.00,'2026-09-11 00:00:00','Aceptado',0,NULL,NULL,'Boleta');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_detalle`
--

LOCK TABLES `orders_detalle` WRITE;
/*!40000 ALTER TABLE `orders_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders_detalle` ENABLE KEYS */;
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
  `placed_on` datetime NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `tipc` varchar(15) NOT NULL,
  PRIMARY KEY (`idordpur`),
  KEY `idprov` (`idprov`),
  CONSTRAINT `orders_purchase_ibfk_1` FOREIGN KEY (`idprov`) REFERENCES `proveedores` (`idprov`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_purchase`
--

LOCK TABLES `orders_purchase` WRITE;
/*!40000 ALTER TABLE `orders_purchase` DISABLE KEYS */;
INSERT INTO `orders_purchase` VALUES (1,1,1,'Contado','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD ( 1 ),  PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24 ( 3 )',11721.00,'2026-08-18 00:00:00','Aceptado','Boleta');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders_purchase_detalle`
--

LOCK TABLES `orders_purchase_detalle` WRITE;
/*!40000 ALTER TABLE `orders_purchase_detalle` DISABLE KEYS */;
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
  `stock` char(3) NOT NULL,
  `idmar` int(11) NOT NULL,
  `idcate` int(11) NOT NULL,
  `modelo` text NOT NULL,
  `peso` text NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT 1,
  `fere` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idprod`),
  KEY `idmar` (`idmar`),
  KEY `idcate` (`idcate`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`idmar`) REFERENCES `marca` (`idmar`),
  CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`idcate`) REFERENCES `categoria` (`idcate`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'33333333333333','LAPTOP LENOVO V14 IIL, INTEL CORE I3-1005G1, 4GB, 1TB, 14″ HD','Procesador: Intel Core i3\r\nTipo de disco duro: SATA\r\nTarjeta gráfica: Intel® UHD Graphics\r\nDisco Duro: 1TB\r\nMemoria RAM: 4GB','825226.jpg',2424.00,'99',1,2,'Lenovo','A partir de 1.6 Kg',1,'2026-09-15 03:17:45'),(2,'74355345345324',' PC Todo en Uno Lenovo IdeaCentre 3, Intel Core i5-10400T 2.4GHz, RAM 8GB, HDD 1TB, Wi-FI, BT, LED 24\" Full HD, Windows 10 Home SP','Intel® Core™ i5-10400T (2.0 / 3.6 GHz, 6 núcleos) Décima generación \r\nRAM 8 GB DDR4 ampliable\r\nDisco Duro de 1TB SATA\r\nPantalla 24\" Full HD (1920x1080) IPS, marcos reducidos.\r\nWiFi, Buetooth, Cámara web 720p\r\nTeclado & Mouse alámbricos\r\nWindows 10 Home SP','651402.jpg',3099.00,'20',1,4,'Lenovo','30kg',1,'2026-08-17 02:26:23'),(4,'96785756756756','LAPTOP HP ULTRA','ESTA ES UNA LAPTOP HP ULTRA','211473.jpg',2400.00,'90',2,2,'hP','100',1,'2026-09-15 03:35:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'20568005491','TIENDAS DE COMPUTO EIRL','',1,'2026-08-15 19:14:16'),(2,'20511697353','EQUIPOS Y ACCESORIOS DE COMPUTO S.A.C','',1,'2026-08-15 19:15:17'),(4,'10399333426','MANRIQUE SA','',1,'2026-08-19 08:58:35');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
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
