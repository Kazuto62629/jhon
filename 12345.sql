/*
SQLyog Ultimate v8.55 
MySQL - 5.5.5-10.4.32-MariaDB : Database - dbfinalproj
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`dbfinalproj` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci */;

USE `dbfinalproj`;

/*Table structure for table `banners` */

DROP TABLE IF EXISTS `banners`;

CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `description` tinytext NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `update_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `display_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `banners` */

LOCK TABLES `banners` WRITE;

insert  into `banners`(`id`,`title`,`image_path`,`description`,`is_active`,`update_at`,`created_at`,`display_order`) values (8,'Welcome to the page','./images/asael-pena-n-HtQS7IgU4-unsplash.jpg','Continuing to inspire and nurture the human spirit–one person, one cup and one neighborhood at a time.',1,'2025-05-27 14:10:31','2025-05-27 14:10:31',NULL);

UNLOCK TABLES;

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `categories` */

LOCK TABLES `categories` WRITE;

insert  into `categories`(`id`,`name`,`parent_id`) values (17,'asddsa',7),(18,'Coffee',NULL),(19,'Hot',1),(20,'Iced',1),(21,'Tea',NULL),(22,'Pastries',NULL);

UNLOCK TABLES;

/*Table structure for table `menu_items` */

DROP TABLE IF EXISTS `menu_items`;

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_featured` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `menu_items` */

LOCK TABLES `menu_items` WRITE;

insert  into `menu_items`(`id`,`name`,`description`,`price`,`category`,`image_path`,`is_active`,`created_at`,`is_featured`) values (6,'Caramel Mac','Caramel','10.00','Iced','./images/00-734747-0194273078-rgS16NfW.jpg',1,'2025-05-27 18:19:22',NULL),(8,'Caramel Mac','caramel mach','345.00','ICed','./images/00-734747-0194273078-rgS16NfW.jpg',1,'2025-05-28 18:56:41',NULL),(9,'wqrfgvv','23456gfsdx','2345.00','Iced','./images/00-734747-0194273078-rgS16NfW.jpg',1,'2025-05-28 18:59:43',NULL),(10,'erfscz','12312','1231231.00','cvbnm,','./images/00-734747-0194273078-rgS16NfW.jpg',1,'2025-05-28 19:51:35',NULL);

UNLOCK TABLES;

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `order_items` */

LOCK TABLES `order_items` WRITE;

UNLOCK TABLES;

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','accepted','completed','cancelled') NOT NULL DEFAULT 'pending',
  `pickup_or_delivery` enum('pickup','delivery') NOT NULL DEFAULT 'pickup',
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `orders` */

LOCK TABLES `orders` WRITE;

UNLOCK TABLES;

/*Table structure for table `pages` */

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `pages` */

LOCK TABLES `pages` WRITE;

UNLOCK TABLES;

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `subcategory_id` int(20) NOT NULL,
  `images` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `products` */

LOCK TABLES `products` WRITE;

insert  into `products`(`id`,`name`,`description`,`price`,`category_id`,`stock`,`subcategory_id`,`images`) values (2,'Iced coffee','Signature Starbucks blend','4.50',1,100,2,'./images/th (1).jpg'),(3,'Caramel Mac','Espresso with vanilla and caramel','5.25',1,75,2,'./images/00-734747-0194273078-rgS16NfW.jpg'),(4,'Cafe Lattes','Chilled coffee beverage 111111','4.00',1,50,3,'./images/th.jpg'),(6,'Caramel Macchiato','fxccxzxcxz','23456.00',NULL,3456,0,'./images/00-734747-0194273078-rgS16NfW.jpg'),(8,'kopiko','dsvbvdbsvz2345','23456.00',NULL,23456,0,'./images/00-734747-0194273078-rgS16NfW.jpg');

UNLOCK TABLES;

/*Table structure for table `sales` */

DROP TABLE IF EXISTS `sales`;

CREATE TABLE `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `sales` */

LOCK TABLES `sales` WRITE;

UNLOCK TABLES;

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

/*Data for the table `users` */

LOCK TABLES `users` WRITE;

insert  into `users`(`id`,`username`,`password`,`role`,`created_at`) values (1,'admin','$2y$10$1MAeZzFwFSNnCPxjZXih5e4bLU/cyG1gUWyFGvo.rwyWPz0YMgjSG','admin','2025-05-26 10:28:00'),(2,'aljhon','$2y$10$KCjEjQ3p6RH9uzjyhgKsYuZe.yGoBsGBEB6TIXX5nbZr.1cdcIOqK','user','2025-05-26 10:29:01'),(3,'admin123','$2y$10$SRMdOTRyId6LCLcGKrw/.Odpr6vDQp13rxoR5Xq9b0CuWleAc3MUK','admin','2025-05-26 18:03:54'),(4,'admin123123','$2y$10$C0/y00UKAFdV4UW5efWLyeT/hgBHIK6DuQrQzHem9JbZQwVy2G6u.','admin','2025-05-27 12:50:48');

UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
