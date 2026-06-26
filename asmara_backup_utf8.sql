/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table` (`table_name`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-23 16:36:06'),(2,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 10:06:54'),(3,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 10:42:55'),(4,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.125.1 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36','2026-06-24 10:52:23'),(5,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:27:25'),(6,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:28:06'),(7,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:43:54'),(8,1,'updated','menu_items',11,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:53:45'),(9,1,'updated','menu_items',11,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:54:01'),(10,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 11:58:22'),(11,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:35'),(12,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:35'),(13,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:36'),(14,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:36'),(15,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:38'),(16,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:16:38'),(17,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:30:48'),(18,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:30:58'),(19,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 12:30:58'),(20,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:08:21'),(21,1,'updated','menu_items',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:32:04'),(22,1,'updated','menu_items',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:32:06'),(23,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:39:54'),(24,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:41:20'),(25,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:41:27'),(26,1,'updated','menu_items',3,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 13:58:26'),(27,1,'login','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 14:26:26'),(28,1,'logout','admin_users',1,NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-24 14:26:56'),(29,1,'login','admin_users',1,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:03:28'),(30,1,'login','admin_users',1,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:05:12'),(31,1,'updated','menu_items',3,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:07:00'),(32,1,'deleted','menu_items',3,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:07:03'),(33,1,'deleted','menu_items',3,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:14:49'),(34,1,'created','events',0,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:23:10'),(35,1,'updated','menu_items',1,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:28:11'),(36,1,'login','admin_users',1,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 12:37:00'),(37,1,'login','admin_users',1,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36','2026-06-26 13:08:18');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$JcUKgPYahHjwxlZt.pFTKO3Aq5NdEb8g/c76PhS56vJ4WUlv/nnn.','admin@asmara.co.ke','admin','2026-06-26 13:08:18','2026-06-23 16:14:16','2026-06-26 13:08:18');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `guest_count` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `special_requests` text DEFAULT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `confirmation_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `confirmation_code` (`confirmation_code`),
  KEY `idx_date` (`booking_date`),
  KEY `idx_status` (`status`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_email` (`email`),
  KEY `idx_confirmation_code` (`confirmation_code`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,'Lewis Ndungu','lewisndungu2005@gmail.com','0114971070','2026-06-27','15:15:00',2,2,'i need to eat','pending','BBFCD261','2026-06-26 12:14:37','2026-06-26 12:14:37');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `opening_hours` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 50,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(10,8) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `long_description` text DEFAULT NULL,
  `seo_keywords` text DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Westlands','General Mathenge Drive, Westlands','0721948020','info@asmara.co.ke','10:00 AM - 11:00 PM',50,NULL,NULL,'A casual and friendly dining atmosphere','The modern indoor and outdoor setting perfectly complements the afro-contemporary Eritrean and Continental cuisine offered here.','Located in the vibrant heart of Westlands, our flagship Asmara Restaurant brings the rich traditions of Eritrean and Ethiopian dining to Nairobi. We specialize in authentic African culinary experiences, featuring our famous, freshly-made injera paired with spicy Zigni, tender Kitfo, and rich vegetarian shiro.\n\nOur Westlands branch offers a seamless blend of traditional African culture and modern contemporary aesthetics. With an airy garden lounge and elegant indoor seating, it is the premier choice for business lunches, romantic dinners, and family gatherings in Nairobi.','Eritrean food Westlands, Ethiopian restaurant Nairobi, Asmara Westlands, authentic African cuisine, best injera Nairobi, Zigni, Kitfo, outdoor dining Westlands','images/optimized/Lavington-5.jpg','2026-06-23 16:14:16','2026-06-23 16:14:16'),(2,'Karen','Ngong\' Rd-No. 321, Karen','0724124555','admin.karen@asmara.co.ke','10:00 AM - 11:00 PM',45,NULL,NULL,'Leafy, serene, and spacious','In the leafy Karen suburb, this restaurant sits on a large and picturesque location with delicious quality food and excellent service.','Escape the city rush at Asmara Restaurant in Karen. Nestled on Ngong Road, our Karen branch is a true sanctuary of Eritrean culture and gastronomy. Diners can enjoy authentic Horn of Africa hospitality surrounded by manicured gardens.\n\nWhether you are craving our signature platters, Tibs, or a relaxing weekend brunch with continental favorites, our serene environment elevates every meal. It is the perfect venue for events, parties, and anyone looking for the best African restaurant in Karen.','Asmara Karen, Eritrean restaurant Karen, African food Ngong Road, garden restaurant Nairobi, best Ethiopian food Karen, family dining, cultural African restaurant','images/optimized/Lavington-10.jpg','2026-06-23 16:14:16','2026-06-23 16:14:16'),(3,'Lavington','Othaya Road, Lavington','0700458429','sales@asmara.co.ke','10:00 AM - 11:00 PM',40,NULL,NULL,'Cosy and modern','Ideally situated in Lavington, this branch offers the ultimate dining experience of Eritrean and Continental dishes for breakfast, lunch, and dinner.','Asmara Lavington on Othaya Road is the neighborhood\'s top destination for premium Eritrean, Ethiopian, and Continental dining. We pride ourselves on preserving authentic African cooking methods???from our traditional clay-pot stews to our hand-poured injera.\n\nThe cozy, afro-contemporary interior is designed for comfort, making it an excellent spot for breakfast meetings, casual lunches, and intimate dinners. Experience the true taste of Asmara with our diverse menu that caters to both meat lovers and vegans alike.','Asmara Lavington, Eritrean food Lavington, African restaurant Othaya Road, vegan Ethiopian food Nairobi, Asmara breakfast, best dining Lavington','images/optimized/Lavington-20.jpg','2026-06-23 16:14:16','2026-06-23 16:14:16'),(4,'Pangani','Pangani, Juja Road','0713610707','admin.pangani@asmara.co.ke','10:00 AM - 11:00 PM',35,NULL,NULL,'Authentically Eritrean','A favourite of the Eritrean community and locals, described by The Nairobian as a little piece of Eritrea.','Welcome to Asmara Pangani???the cultural heart of our brand. Situated on Juja Road, this branch is affectionately known as a \"little piece of Eritrea.\" It is deeply rooted in tradition and remains a favorite among the local Eritrean and Ethiopian communities in Nairobi.\n\nIf you want the most authentic, unfiltered Horn of Africa dining experience, this is the place. From communal dining over massive injera platters to the rich aroma of traditional coffee ceremonies, Asmara Pangani celebrates the true essence of African heritage and culinary excellence.','Asmara Pangani, authentic Eritrean restaurant Nairobi, Juja road restaurants, traditional Ethiopian food, Eritrean culture Nairobi, injera platters, African coffee ceremony','images/optimized/Lavington-30.jpg','2026-06-23 16:14:16','2026-06-23 16:14:16');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contact_inquiries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') DEFAULT 'new',
  `admin_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_email` (`email`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `contact_inquiries` WRITE;
/*!40000 ALTER TABLE `contact_inquiries` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_inquiries` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `available_branches` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_branch` (`branch_id`),
  KEY `idx_available` (`is_available`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,'Sambusas','Crispy pastries filled with spiced meat or vegetables','appetizers',300.00,'../backend/uploads/menu/menu_6a3e705b8616e.jpg',NULL,1,'2026-06-23 16:14:16','2026-06-26 12:28:11',NULL),(2,'Shiro','Creamy chickpea dip with traditional spices','appetizers',450.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(4,'Injera & Wat','Traditional Eritrean meal with meat sauce','mains',800.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(5,'Tibs','Sauteed meat with vegetables and spices','mains',950.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(6,'Kitfo','Ethiopian minced raw meat with spiced butter','mains',1200.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(7,'Fasting Greens','Seasonal vegetables in traditional preparation','mains',600.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(8,'Honey Wine','Traditional fermented honey beverage','drinks',400.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(9,'Tamarind Juice','Fresh tamarind juice, tangy and refreshing','drinks',350.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(10,'Basboosa','Sweet coconut semolina cake','desserts',400.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-23 16:14:16',NULL),(11,'Tiramisu','Italian-inspired layered dessert','desserts',550.00,NULL,NULL,1,'2026-06-23 16:14:16','2026-06-24 11:54:01',NULL);
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
INSERT INTO `newsletter_subscribers` VALUES (1,'lewisndungu2005@gmail.com','2026-06-26 13:08:06',1);
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

