-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 20, 2022 at 06:41 PM
-- Server version: 5.7.24
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `facturacion`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (`n_cantidad` INT, `n_precio` DECIMAL(10,2), `codigo` INT)  BEGIN
    	DECLARE nueva_existencia int;
        DECLARE nuevo_total  decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual int;
        DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia int;
        DECLARE actual_precio decimal(10,2);
                
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto = codigo;
        SET nueva_existencia = actual_existencia + n_cantidad;
        SET nuevo_total = (actual_existencia * actual_precio) + (n_cantidad * n_precio);
        SET nuevo_precio = nuevo_total / nueva_existencia;
        
        UPDATE producto SET existencia = nueva_existencia, precio = nuevo_precio WHERE codproducto = codigo;
        
        SELECT nueva_existencia,nuevo_precio;
        
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (`codigo` INT, `cantidad` INT, `token_user` VARCHAR(50))  BEGIN
    	DECLARE precio_actual decimal(10,2);
        SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
        
        INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta) VALUES(token_user,codigo,cantidad,precio_actual);
        
        SELECT tmp.correlativo,tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token_user;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (`no_factura` INT)  BEGIN
    	DECLARE existe_factura INT;
        DECLARE registros INT;
        DECLARE a INT;
        
        DECLARE cod_producto INT;
        DECLARE cant_producto INT;
        DECLARE existencia_actual INT;
        DECLARE nueva_existencia INT;
        
        
        SET  existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura and estatus = 1);
        
        IF existe_factura > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod int);
                
                SET a = 1;
                
                SET registros = (SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
                
                IF registros > 0 THEN 
                
                	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detallefactura WHERE nofactura = no_factura;
                
                	WHILE a <= registros DO
                    	SELECT cod_prod, cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                        SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                        SET nueva_existencia = existencia_actual + cant_producto;
                        UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                        
                        SET a=a+1;
                	END WHILE;
                    UPDATE factura SET estatus = 2 WHERE nofactura = no_factura;
                    DROP TABLE tbl_tmp;
                    SELECT * from factura WHERE nofactura = no_factura;
                
                END IF;
        
        ELSE
        	SELECT 0 factura;
        END IF;    
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dataDashboard` ()  BEGIN
    	DECLARE usuarios int;
        DECLARE clientes int;
        DECLARE productos int;
        DECLARE ventas int;
        
        SELECT COUNT(*) INTO usuarios FROM usuario WHERE estatus != 10;
        SELECT COUNT(*) INTO clientes FROM cliente WHERE estatus != 10;
        SELECT COUNT(*) INTO productos FROM producto WHERE estatus != 10;
        SELECT COUNT(*) INTO ventas FROM factura WHERE fecha > CURRENT_DATE AND estatus != 10;
        
        SELECT usuarios,clientes,productos,ventas;
     END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))  BEGIN
    	DELETE FROM detalle_temp WHERE correlativo = id_detalle;
        
        SELECT tmp.correlativo,tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (IN `cod_usuario` INT, IN `cod_cliente` INT, IN `token` VARCHAR(50))  BEGIN
    	DECLARE factura INT;
        DECLARE registros INT;
        DECLARE total DECIMAL(10,2);
        DECLARE nueva_existencia INT;
        DECLARE existencia_actual INT;
        DECLARE tmp_cod_producto INT;
        DECLARE tmp_cant_producto INT;
        DECLARE a INT;
        SET a = 1;     
             
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser (
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
            cod_prod BIGINT,
            cant_prod int);
        SET  registros = (SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
        
        IF registros > 0 THEN
        	INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE token_user = token;
            INSERT INTO factura(usuario,codcliente) VALUES (cod_usuario, cod_cliente);
            SET factura = LAST_INSERT_ID();
            INSERT INTO detallefactura(nofactura,codproducto,cantidad,precio_venta) SELECT (factura) as nofactura,codproducto,cantidad,precio_venta FROM detalle_temp
            WHERE token_user = token;
            
            WHILE a <= registros DO	
            	SELECT cod_prod,cant_prod INTO tmp_cod_producto,tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
                SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
                
                SET nueva_existencia = existencia_actual - tmp_cant_producto;
                UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
                
                SET a=a+1;
            END WHILE;    
            
            
            SET total = (SELECT SUM(cantidad * precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE factura SET totalfactura = total WHERE nofactura = factura;
            
            DELETE FROM detalle_temp WHERE token_user = token;
            TRUNCATE TABLE tbl_tmp_tokenuser;
            SELECT * FROM factura WHERE nofactura = factura;
            
        ELSE
        	SELECT 0;
        
        END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `idcategoria` int(11) NOT NULL,
  `categoria` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`idcategoria`, `categoria`) VALUES
(1, 'Jugos'),
(2, 'Carnes'),
(3, 'Panes'),
(4, 'Bebidas');

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `nit` int(11) DEFAULT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` text,
  `direccion` text,
  `dateadd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nit`, `nombre`, `telefono`, `direccion`, `dateadd`, `usuario_id`, `estatus`) VALUES
(1, 0, 'CF ', '923123457', '', '2022-11-20 09:14:29', 5, 1),
(2, 2, 'Maria', '923637384', 'Nuevo Imperial ', '2022-11-21 06:56:01', 1, 1),
(3, 1234, 'Lizbeth', '923637384', 'Nuevo Imperial', '2022-11-21 06:59:18', 1, 1),
(4, 0, 'Adrian Romero', '964321286', 'En tu corazón', '2022-11-21 07:03:23', 1, 1),
(5, 7634, 'Elizabeth', '964321286', 'Lima', '2022-11-21 07:04:44', 1, 1),
(6, 0, 'Carol Cabrera', '964321286', 'Lima', '2022-11-21 07:06:45', 1, 1),
(7, 0, 'Carol Cabrera', '964321286', 'Lima', '2022-11-21 15:01:16', 1, 0),
(8, 12345, 'Juieta Estrada', '912737', 'Los Olivos', '2022-11-30 12:43:08', 1, 1),
(9, 123456, 'Julio Pineda', '913232', 'Los Olivos', '2022-11-30 12:48:18', 1, 1),
(10, 0, 'Lizbeth Godoy', '923637384', 'Nuevo Imperial', '2022-12-06 21:04:03', 1, 1),
(11, 12345678, 'Elizabeth', '964321286', 'En tu corazón', '2022-12-07 16:37:27', 1, 1),
(12, 1234543, 'sdfred', '8866', 'fghj', '2022-12-07 17:02:03', 1, 1),
(13, 12312, 'Lizbeth', '923637384', '', '2022-12-07 18:20:48', 1, 1),
(14, 1234212, 'Lizbeth', '21323', '', '2022-12-07 18:21:01', 1, 1),
(15, 34545, 'maria rios', '5443', '', '2022-12-07 18:22:25', 1, 1),
(16, 342332, 'Lizbeth', '923637384', '', '2022-12-07 18:25:02', 1, 1),
(17, 231232, 'csdcds', '785443', '', '2022-12-07 18:28:20', 1, 1),
(18, 124589, 'Elizabeth', '964321286', '', '2022-12-07 20:53:09', 1, 1),
(19, 566985, 'yh', '964321286', '', '2022-12-07 20:53:47', 1, 1),
(20, 586, 'Mery Aguado Chumpitaz', '964321286', '', '2022-12-07 20:58:30', 1, 1),
(21, 462, '', '964321286', '', '2022-12-07 21:00:44', 1, 1),
(22, 256269, '', '964321286', '', '2022-12-07 21:01:41', 1, 1),
(23, 565269, '', '964321286', '', '2022-12-07 21:02:03', 1, 1),
(24, 956, 'Elizabeth', '964321286', '', '2022-12-07 21:07:00', 1, 1),
(25, 7634569, 'tresa', '59', '', '2022-12-07 21:09:25', 1, 1),
(26, 1234567865, 'lohbgf', NULL, NULL, '2022-12-07 21:13:30', 1, 1),
(27, 14555, 'tresa', '789', '', '2022-12-07 21:21:30', 1, 1),
(28, 89785, 'Lizbeth Guadalupe', NULL, '', '2022-12-07 21:29:48', 1, 1),
(29, 7895, 'Maria Jimenez', NULL, '', '2022-12-07 21:31:04', 1, 1),
(30, 7895211, 'Elizabeth', '', '', '2022-12-07 21:32:14', 1, 1),
(31, 895214, 'Carol Cabrera', '', '', '2022-12-07 21:47:15', 1, 1),
(32, 7895478, 'Maria Jimenez', '', '', '2022-12-07 21:47:51', 1, 1),
(33, 789547, 'Adrian Romero', '', '', '2022-12-07 21:48:46', 1, 1),
(34, 763456978, 'Fresia', '', '', '2022-12-07 21:53:12', 1, 1),
(35, 12341237, 'maria rios', '', '', '2022-12-14 10:04:09', 1, 1),
(36, 123412, 'lucia', '', '', '2022-12-14 10:04:39', 1, 1),
(37, 123, 'deceed', '', '', '2022-12-14 12:11:04', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `iva` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `configuracion`
--

INSERT INTO `configuracion` (`id`, `nit`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`) VALUES
(1, '12345678', 'Mil Sabores SA', '', 912345678, 'info@gmail.com', 'San Bartolo', '18.00');

-- --------------------------------------------------------

--
-- Table structure for table `detallefactura`
--

CREATE TABLE `detallefactura` (
  `correlativo` bigint(11) NOT NULL,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(1, 1, 2, 1, '5.00'),
(2, 1, 5, 2, '2.00'),
(3, 1, 4, 1, '2.00'),
(4, 1, 3, 1, '2.00'),
(8, 2, 2, 1, '5.00'),
(9, 2, 5, 2, '2.00'),
(10, 2, 4, 1, '2.00'),
(11, 2, 3, 1, '2.00'),
(15, 3, 1, 1, '2.00'),
(16, 3, 4, 1, '2.00'),
(18, 4, 4, 1, '2.00'),
(19, 4, 3, 1, '2.00'),
(20, 5, 1, 1, '2.00'),
(21, 6, 3, 1, '2.00'),
(22, 7, 1, 3, '2.00'),
(23, 8, 2, 1, '5.00'),
(24, 8, 3, 1, '2.00'),
(26, 9, 2, 1, '5.00'),
(27, 10, 4, 3, '2.00'),
(28, 11, 4, 2, '2.00'),
(29, 11, 3, 1, '2.00'),
(31, 12, 3, 4, '2.00'),
(32, 12, 5, 1, '2.00'),
(33, 13, 2, 1, '5.00'),
(34, 13, 2, 1, '5.00'),
(35, 13, 2, 1, '5.00'),
(36, 14, 7, 1, '2.00'),
(37, 15, 7, 1, '2.00'),
(38, 15, 4, 1, '2.00'),
(40, 16, 7, 1, '2.00'),
(41, 16, 1, 1, '2.00'),
(42, 17, 4, 1, '2.00'),
(43, 18, 2, 1, '5.00'),
(44, 19, 2, 1, '5.00'),
(45, 20, 8, 1, '6.00'),
(46, 20, 8, 1, '6.00'),
(47, 21, 8, 1, '6.00'),
(48, 21, 8, 1, '6.00'),
(49, 22, 8, 1, '6.00'),
(50, 22, 7, 1, '2.00'),
(51, 22, 13, 1, '10.00'),
(52, 22, 1, 1, '2.00'),
(53, 23, 2, 4, '5.00'),
(54, 24, 16, 2, '2.50'),
(55, 24, 16, 1, '2.50'),
(56, 24, 16, 2, '2.50'),
(57, 24, 15, 2, '4.00'),
(58, 24, 16, 2, '2.50');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(1, 1, '2022-11-22 20:39:07', 30, '2.00', 1),
(2, 2, '2022-11-22 20:42:41', 10, '5.00', 1),
(3, 3, '2022-11-22 23:52:33', 10, '2.00', 1),
(4, 4, '2022-11-22 23:57:39', 10, '2.00', 1),
(5, 5, '2022-11-22 23:58:13', 10, '2.00', 1),
(6, 6, '2022-11-23 00:09:15', 10, '2.00', 1),
(7, 7, '2022-11-23 16:49:51', 10, '2.00', 1),
(8, 1, '2022-11-27 20:14:44', 20, '2.00', 1),
(9, 1, '2022-11-27 20:17:57', 20, '2.00', 1),
(10, 1, '2022-11-27 20:18:59', 20, '2.00', 1),
(11, 1, '2022-11-27 20:20:13', 20, '2.00', 1),
(12, 1, '2022-11-27 20:34:52', 20, '2.00', 1),
(13, 1, '2022-11-27 20:36:00', 10, '2.00', 1),
(14, 1, '2022-11-27 20:37:10', 20, '2.00', 1),
(15, 1, '2022-11-27 20:37:35', 20, '2.00', 1),
(16, 1, '2022-11-27 20:38:01', 20, '2.00', 1),
(17, 1, '2022-11-27 20:38:12', 10, '2.00', 1),
(18, 1, '2022-11-27 20:39:05', 10, '2.00', 1),
(19, 1, '2022-11-27 20:45:33', 10, '2.00', 1),
(20, 1, '2022-11-27 20:46:34', 10, '2.00', 1),
(21, 1, '2022-11-27 20:47:21', 10, '2.00', 1),
(22, 1, '2022-11-27 20:55:26', 10, '2.00', 1),
(23, 1, '2022-11-27 21:08:21', 10, '2.00', 1),
(24, 1, '2022-11-27 21:17:43', 10, '2.00', 1),
(25, 2, '2022-11-27 21:18:21', 10, '2.00', 1),
(26, 1, '2022-11-27 21:18:50', 10, '2.00', 1),
(27, 2, '2022-11-27 21:19:19', 20, '5.00', 1),
(28, 8, '2022-12-06 00:34:40', 10, '5.00', 1),
(29, 9, '2022-12-06 01:26:31', 10, '4.00', 1),
(30, 10, '2022-12-06 01:26:42', 10, '4.00', 1),
(31, 11, '2022-12-06 01:26:43', 10, '4.00', 1),
(32, 12, '2022-12-06 01:34:11', 10, '5.00', 1),
(33, 13, '2022-12-06 01:36:03', 10, '10.00', 1),
(34, 14, '2022-12-06 01:36:21', 10, '2.00', 1),
(35, 15, '2022-12-06 01:44:41', 10, '4.00', 1),
(36, 16, '2022-12-14 12:10:32', 10, '2.50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `factura`
--

CREATE TABLE `factura` (
  `nofactura` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estatus`) VALUES
(1, '2022-12-01 15:59:26', 1, 2, NULL, 2),
(2, '2022-12-01 16:00:29', 1, 2, '13.00', 1),
(3, '2022-12-01 16:00:55', 1, 1, '4.00', 1),
(4, '2022-12-01 16:05:49', 1, 1, '4.00', 1),
(5, '2022-12-02 10:13:03', 1, 1, '2.00', 1),
(6, '2022-12-02 10:14:30', 1, 1, '2.00', 1),
(7, '2022-12-02 10:17:12', 1, 2, '6.00', 1),
(8, '2022-12-02 10:18:13', 1, 3, '7.00', 1),
(9, '2022-12-02 10:20:39', 1, 1, '5.00', 1),
(10, '2022-12-02 10:22:21', 1, 1, '6.00', 1),
(11, '2022-12-02 10:43:29', 1, 3, '6.00', 1),
(12, '2022-12-02 10:44:42', 1, 3, '10.00', 1),
(13, '2022-12-02 15:18:40', 1, 3, '15.00', 1),
(14, '2022-12-02 15:46:52', 1, 1, '2.00', 2),
(15, '2022-12-02 15:52:14', 1, 3, '4.00', 2),
(16, '2022-12-02 17:14:05', 1, 3, '4.00', 2),
(17, '2022-12-04 20:52:16', 1, 1, '2.00', 1),
(18, '2022-12-06 21:03:12', 1, 1, '5.00', 1),
(19, '2022-12-06 21:04:10', 1, 10, '5.00', 1),
(20, '2022-12-07 16:29:49', 1, 1, '12.00', 1),
(21, '2022-12-07 16:59:48', 1, 1, '12.00', 1),
(22, '2022-12-07 21:53:22', 1, 34, '20.00', 1),
(23, '2022-12-14 10:08:24', 1, 36, '20.00', 1),
(24, '2022-12-14 12:27:33', 1, 11, '25.50', 1);

-- --------------------------------------------------------

--
-- Table structure for table `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` int(11) DEFAULT NULL,
  `foto` text,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT '1',
  `categoria` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `precio`, `existencia`, `foto`, `date_add`, `usuario_id`, `estatus`, `categoria`) VALUES
(1, 'Pan con Queso', '2.00', 224, 'img_producto.png', '2022-11-22 20:39:07', 1, 1, 3),
(2, 'Pan con Pollo', '5.00', 29, 'img_producto.png', '2022-11-22 20:42:41', 1, 1, 3),
(3, 'Pan con Jamón, Queso y Orégano', '2.00', 2, 'img_421357cbe1b89a14492ec085bcc7f43djpg', '2022-11-22 23:52:33', 1, 1, 1),
(4, 'Pan con Cabanossi y Jamón', '2.00', 2, 'img_ae88bcd2611e1da54a248440ebb3bdaa.jpg', '2022-11-22 23:57:39', 1, 1, 1),
(5, 'Pan con Cabanossi y Queso', '2.00', 9, 'img_producto.png', '2022-11-22 23:58:13', 1, 1, 1),
(6, 'pan con JQO', '2.00', 10, 'img_8ac265fa2696d943636553015257dad5.jpg', '2022-11-23 00:09:15', 1, 0, 1),
(7, 'Pan con Cabanossi y queso', '2.00', 9, 'img_producto.png', '2022-11-23 16:49:51', 1, 1, 1),
(8, 'Minipizza', '6.00', 5, 'img_producto.png', '2022-12-06 00:34:40', 1, 1, 3),
(9, 'Fugazza Mixta', '4.00', 10, 'img_producto.png', '2022-12-06 01:26:31', 1, 1, 3),
(10, 'Fugazza Mixta', '4.00', 10, 'img_producto.png', '2022-12-06 01:26:42', 1, 1, 3),
(11, 'Fugazza Mixta', '4.00', 10, 'img_producto.png', '2022-12-06 01:26:43', 1, 1, 3),
(12, 'Minipizza', '5.00', 10, 'img_producto.png', '2022-12-06 01:34:11', 1, 1, 3),
(13, 'Pan con Queso', '10.00', 9, 'img_producto.png', '2022-12-06 01:36:03', 1, 1, 3),
(14, 'Pan con Queso', '2.00', 10, 'img_producto.png', '2022-12-06 01:36:21', 1, 1, 1),
(15, 'Fugazza Simple', '4.00', 8, 'img_producto.png', '2022-12-06 01:44:41', 1, 1, 3),
(16, 'frugos', '2.50', 3, 'img_producto.png', '2022-12-14 12:10:32', 1, 1, 4);

--
-- Triggers `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    	INSERT INTO entradas(codproducto, cantidad, precio, usuario_id)
        VALUES(new.codproducto,new.existencia,new.precio,new.usuario_id);
    END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'Administrador'),
(2, 'Supervisor'),
(3, 'Vendedor');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `usuario` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
  `clave` varchar(100) CHARACTER SET utf8 NOT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estatus`) VALUES
(1, 'LizGodoy', 'lizbetgodoyp@gmail.com', 'admin', '202cb962ac59075b964b07152d234b70', 1, 1),
(2, 'Matias Herrera ', 'matias@gmail.com', 'matias', '202cb962ac59075b964b07152d234b70', 3, 1),
(3, 'Arturo', 'arturo@gmail.com', 'arturito1990', '202cb962ac59075b964b07152d234b70', 3, 1),
(4, 'Rocío', 'rocio@gmail.com', 'rocio', 'e10adc3949ba59abbe56e057f20f883e', 3, 1),
(5, 'Carol Cabrera', 'carol@gmail.com', 'carol', 'e10adc3949ba59abbe56e057f20f883e', 3, 1),
(6, 'Elizabeth', 'eli@gmail.com', 'eli', '202cb962ac59075b964b07152d234b70', 2, 1),
(7, 'Adrian Romero', 'rome@gmail.com', 'romeito', '202cb962ac59075b964b07152d234b70', 3, 1),
(8, 'Maria Jimenez', 'maria@gmail.com', 'maria', '202cb962ac59075b964b07152d234b70', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `nofactura` (`nofactura`);

--
-- Indexes for table `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `nofactura` (`token_user`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indexes for table `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indexes for table `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`nofactura`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`);

--
-- Indexes for table `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria` (`categoria`);

--
-- Indexes for table `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `factura`
--
ALTER TABLE `factura`
  MODIFY `nofactura` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`);

--
-- Constraints for table `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_1` FOREIGN KEY (`nofactura`) REFERENCES `factura` (`nofactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`idcategoria`);

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
