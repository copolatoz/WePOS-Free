/*
SQLyog Ultimate v8.53 
MySQL - 5.6.24 : Database - wepos_cafe
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `acc_account_payable` */

DROP TABLE IF EXISTS `acc_account_payable`;

CREATE TABLE `acc_account_payable` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ap_no` varchar(30) NOT NULL,
  `ap_date` date DEFAULT NULL,
  `ap_name` varchar(100) DEFAULT NULL,
  `ap_address` varchar(255) DEFAULT NULL,
  `ap_phone` varchar(30) DEFAULT NULL,
  `tanggal_tempo` date DEFAULT NULL,
  `autoposting_id` int(11) DEFAULT NULL,
  `po_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT '0',
  `jurnal_id` int(11) DEFAULT NULL,
  `posting_no` varchar(30) DEFAULT NULL,
  `no_ref` varchar(30) DEFAULT NULL,
  `acc_bank_id` int(11) DEFAULT NULL,
  `ap_tipe` enum('operational','purchasing') DEFAULT 'operational',
  `ap_used` tinyint(1) DEFAULT '0',
  `ap_status` enum('pengakuan','jurnal','posting','kontrabon','pembayaran') DEFAULT 'pengakuan',
  `total_tagihan` double DEFAULT '0',
  `ap_notes` varchar(255) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `payment_type` tinyint(1) DEFAULT '1',
  `cash_name` varchar(50) DEFAULT NULL,
  `transfer_bank` varchar(50) DEFAULT NULL,
  `transfer_bank_no` varchar(30) DEFAULT NULL,
  `transfer_bank_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ap_no` (`ap_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*Table structure for table `acc_autoposting` */

DROP TABLE IF EXISTS `acc_autoposting`;

CREATE TABLE `acc_autoposting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `autoposting_name` varchar(100) NOT NULL,
  `autoposting_tipe` enum('purchasing','sales','other','pelunasan_account_payable','account_payable','account_receivable','pembayaran_account_receivable') DEFAULT 'other',
  `rek_id_debet` int(11) DEFAULT NULL,
  `rek_id_kredit` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

/*Data for the table `acc_autoposting` */

insert  into `acc_autoposting`(`id`,`autoposting_name`,`autoposting_tipe`,`rek_id_debet`,`rek_id_kredit`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Hutang Pembelian Bahan Baku ke Supplier','account_payable',24,74,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:39',1,0),(2,'Hutang Pembelian Alat/barang Supplier','account_payable',31,74,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(3,'Pelunasan Hutang Supplier via Kas Besar','pelunasan_account_payable',74,9,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(4,'Pelunasan Hutang Supplier via Bank Mandiri','pelunasan_account_payable',74,12,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(5,'Pelunasan Hutang Supplier via Bank BCA','pelunasan_account_payable',74,13,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(6,'Piutang Penjualan (Sales/Cashier)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(7,'Piutang Penjualan (Sales Order/Reservasi)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(8,'Piutang Penjualan (Marketplace/Online)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(9,'Pembayaran Piutang via Kas Besar','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(10,'Pembayaran Piutang via Bank BCA','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(11,'Pembayaran Piutang via Bank Mandiri','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0);

/*Table structure for table `apps_clients` */

DROP TABLE IF EXISTS `apps_clients`;

CREATE TABLE `apps_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_code` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `client_name` varchar(100) CHARACTER SET latin1 NOT NULL,
  `client_address` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `province_id` int(11) DEFAULT NULL,
  `client_postcode` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `client_phone` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `client_fax` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `client_email` varbinary(100) DEFAULT NULL,
  `client_logo` varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  `client_website` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `client_notes` varchar(400) CHARACTER SET latin1 DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rs_kode` (`client_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_clients` */

insert  into `apps_clients`(`id`,`client_code`,`client_name`,`client_address`,`city_id`,`province_id`,`client_postcode`,`country_id`,`client_phone`,`client_fax`,`client_email`,`client_logo`,`client_website`,`client_notes`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'COPY SERIAL NO DI WEBSITE WEPOS.ID','WePOS Cafe','Jl. Kebon Sirih Dalam',NULL,0,NULL,NULL,'081222549676',NULL,'contact@wepos.id','client_logo.png',NULL,NULL,'','2014-06-28 04:07:01','admin','2017-09-14 21:12:49',1,0);

/*Table structure for table `apps_clients_structure` */

DROP TABLE IF EXISTS `apps_clients_structure`;

CREATE TABLE `apps_clients_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_structure_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `client_structure_notes` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `client_structure_parent` bigint(11) DEFAULT '0',
  `client_structure_order` int(11) DEFAULT '0',
  `is_child` tinyint(1) DEFAULT '1',
  `role_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_unit_id` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

/*Data for the table `apps_clients_structure` */

insert  into `apps_clients_structure`(`id`,`client_structure_name`,`client_structure_notes`,`client_structure_parent`,`client_structure_order`,`is_child`,`role_id`,`client_id`,`client_unit_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Apps Administrator','Apps Super Admin',0,0,0,1,0,1,'1','0000-00-00 00:00:00','1','0000-00-00 00:00:00',1,0),(2,'Apps Admin','',1,0,1,2,1,2,'1','2014-08-11 14:55:11','admin','2017-09-08 00:32:12',1,0),(3,'Finance','',10,0,1,9,1,3,'1','2014-08-11 14:58:00','administrator','2014-09-13 11:17:37',1,0),(4,'Accounting','',10,0,1,10,1,3,'1','2014-08-11 14:59:00','administrator','2014-09-13 11:18:03',1,0),(5,'Supervisor Operational','',13,0,1,8,1,4,'1','2014-08-11 15:00:05','administrator','2014-12-15 10:51:19',1,0),(6,'Kitchen','',5,0,1,7,1,4,'1','2014-08-11 15:00:54','administrator','0000-00-00 00:00:00',1,0),(7,'Cashier','',5,0,1,6,1,4,'1','2014-08-11 15:12:00','administrator','0000-00-00 00:00:00',1,0),(8,'HRD','',13,0,1,11,1,1,'1','2014-08-11 15:12:19','administrator','2014-12-15 10:51:41',1,0),(9,'Service','',5,0,1,15,1,4,'administrator','2014-09-13 11:15:33','administrator','2014-12-10 15:46:18',1,0),(10,'Manager Finance Accounting','',13,0,1,14,1,3,'administrator','2014-09-13 11:17:25','administrator','2014-12-15 10:52:10',1,0),(11,'Purchasing','',10,0,1,12,1,3,'administrator','2014-09-13 11:23:27','administrator','2014-09-13 11:23:27',1,0),(12,'F & B','',13,0,1,13,1,1,'administrator','2014-09-13 13:00:12','administrator','2014-12-15 10:51:58',1,0),(13,'General Manager','',2,0,1,16,1,1,'administrator','2014-12-15 10:50:47','administrator','2014-12-15 10:57:18',1,0),(14,'GRO','',5,0,1,17,1,4,'administrator','2015-09-04 12:57:13','administrator','2015-09-04 12:57:13',1,0);

/*Table structure for table `apps_clients_unit` */

DROP TABLE IF EXISTS `apps_clients_unit`;

CREATE TABLE `apps_clients_unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_unit_name` varchar(255) COLLATE latin1_general_ci NOT NULL,
  `client_id` int(11) NOT NULL,
  `client_unit_code` char(10) COLLATE latin1_general_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_clients_unit` */

insert  into `apps_clients_unit`(`id`,`client_unit_name`,`client_id`,`client_unit_code`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Management',1,'MNG','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(2,'IT Dept.',1,'IT','administrator','2014-06-04 08:38:17','administrator','0000-00-00 00:00:00',1,0),(3,'Accounting',1,'ACC','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(4,'Operational',1,'OPR','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0);

/*Table structure for table `apps_modules` */

DROP TABLE IF EXISTS `apps_modules`;

CREATE TABLE `apps_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `module_author` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `module_version` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `module_description` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `module_folder` varchar(255) CHARACTER SET latin1 NOT NULL,
  `module_controller` varchar(255) CHARACTER SET latin1 NOT NULL,
  `module_is_menu` tinyint(1) DEFAULT '0',
  `module_breadcrumb` varchar(100) CHARACTER SET latin1 NOT NULL,
  `module_order` int(5) DEFAULT '0',
  `module_icon` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `module_shortcut_icon` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `module_glyph_icon` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `module_glyph_font` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `module_free` tinyint(1) DEFAULT '1',
  `running_background` tinyint(1) DEFAULT '0',
  `show_on_start_menu` tinyint(1) DEFAULT '1',
  `show_on_right_start_menu` tinyint(4) DEFAULT '0',
  `start_menu_path` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `start_menu_order` int(11) DEFAULT '0',
  `start_menu_icon` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `start_menu_glyph` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `show_on_context_menu` tinyint(1) DEFAULT '0',
  `context_menu_icon` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `context_menu_glyph` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `show_on_shorcut_desktop` tinyint(1) DEFAULT NULL,
  `desktop_shortcut_icon` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `desktop_shortcut_glyph` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `show_on_preference` tinyint(1) DEFAULT '0',
  `preference_icon` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `preference_glyph` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_controller` (`module_controller`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_modules` */

insert  into `apps_modules`(`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2017-05-03 07:47:08','administrator','2017-05-03 07:47:08',1,0),(2,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2014-08-10 08:52:10','administrator','0000-00-00 00:00:00',1,0),(3,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2014-08-10 08:52:11','administrator','0000-00-00 00:00:00',1,0),(4,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2014-08-10 08:52:15','administrator','0000-00-00 00:00:00',1,0),(5,'Module Manager','dev@wepos.id','v.1.1','','systems','ModuleManager',1,'1. Master Aplikasi>Module Manager',1,'icon-bricks','icon-bricks','','',1,0,1,1,'1. Master Aplikasi>Module Manager',1202,'icon-bricks','',0,'icon-bricks','',1,'icon-bricks','',1,'icon-bricks','','administrator','2014-08-10 08:52:13','administrator','0000-00-00 00:00:00',0,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2014-08-10 08:52:11','administrator','0000-00-00 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2014-08-10 08:52:17','administrator','0000-00-00 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2014-08-10 08:52:12','administrator','0000-00-00 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2014-06-24 07:43:19','administrator','2016-09-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2014-09-17 15:00:19','administrator','2014-09-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2014-08-17 01:40:20','administrator','0000-00-00 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2014-08-17 01:36:16','administrator','2016-05-20 15:06:35',1,0),(13,'Menu Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Menu Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Menu Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 17:26:07','administrator','0000-00-00 00:00:00',1,0),(14,'Master Menu & Package','dev@wepos.id','v.1.0','Master Menu & Package','master_pos','masterProduct',0,'2. Master POS>Master Menu',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Menu',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 17:24:38','administrator','0000-00-00 00:00:00',1,0),(15,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 03:24:56','administrator','2016-09-21 20:05:16',1,0),(16,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 03:25:13','administrator','2016-10-12 22:15:29',1,0),(17,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 03:25:04','administrator','2016-09-21 20:04:34',1,0),(18,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-12-05 00:36:29','administrator','2016-10-15 20:31:54',1,0),(19,'Item Sub Category','dev@wepos.id','v.1.0.0','Item Sub Category','master_pos','itemSubCategory',0,'2. Master POS>Item Sub Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Sub Category',2211,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-12-05 00:36:29','administrator','2016-10-15 20:31:54',1,0),(20,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemCafe',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-10-13 14:04:34','administrator','2016-10-13 14:04:34',1,0),(21,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount Menu','master_pos','discountPlanner',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 17:26:01','administrator','0000-00-00 00:00:00',1,0),(22,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 03:24:50','administrator','2016-09-21 20:06:25',1,0),(23,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 03:24:53','administrator','2016-09-21 20:05:03',1,0),(24,'Master Floor Plan','dev@wepos.id','v.1.0','','master_pos','masterFloorplan',0,'2. Master POS>Master Floor Plan',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Floor Plan',2307,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 17:26:51','administrator','0000-00-00 00:00:00',1,0),(25,'Master Table','dev@wepos.id','v.1.0.0','','master_pos','masterTable',0,'2. Master POS>Master Table',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Table',2308,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 17:26:54','administrator','0000-00-00 00:00:00',1,0),(26,'Table Inventory','dev@wepos.id','v.1.0.0','','master_pos','tableInventory',0,'2. Master POS>Table Inventory',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Table Inventory',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 17:26:59','administrator','0000-00-00 00:00:00',1,0),(27,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-03-27 19:23:32','administrator','2016-09-21 20:02:49',1,0),(28,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 06:43:42','administrator','2016-09-21 20:02:38',1,0),(29,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-11 22:53:04','administrator','2016-09-21 20:02:58',1,0),(30,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Reservation>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 17:28:12','administrator','0000-00-00 00:00:00',1,0),(31,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Reservation>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 17:28:17','administrator','0000-00-00 00:00:00',1,0),(32,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Reservation>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2014-09-20 07:59:55','administrator','2014-09-20 07:59:55',1,0),(33,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashier',0,'3. Cashier & Reservation>Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-10 03:28:03','administrator','2016-10-22 12:58:59',1,0),(34,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetup',0,'3. Cashier & Reservation>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-11 06:13:49','administrator','2016-10-22 12:59:09',1,0),(35,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 03:27:18','administrator','2014-10-15 15:07:08',1,0),(36,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 12:05:57','administrator','2016-10-22 13:04:22',1,0),(37,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 06:43:42','administrator','2016-10-24 13:22:20',1,0),(38,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-08-10 12:06:05','administrator','2016-10-24 13:22:51',1,0),(39,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 21:43:42','administrator','2016-02-03 21:43:42',1,0),(40,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 21:47:56','administrator','2016-02-03 21:51:27',1,0),(41,'Generate Harga Produk','dev@wepos.id','v.1.0.0','Generate Harga Produk','monitoring','generateProductPrice',0,'9. Sync, Backup, Generate>Generate Harga Produk',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Generate Harga Produk',9101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 21:43:42','administrator','2016-06-06 03:27:09',1,0),(42,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2016-02-03 21:43:42','administrator','2016-02-03 21:43:42',1,0),(43,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-08-11 01:28:24','administrator','2016-10-17 17:01:16',1,0),(44,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-09-24 16:30:29','administrator','2014-09-24 16:38:02',1,0),(45,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6123,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2014-09-19 09:45:34','administrator','2014-09-24 16:26:54',1,0),(46,'Sales By Menu','dev@wepos.id','v.1.0.0','Sales By Menu','billing','reportSalesByMenu',0,'6. Reports>Sales (Menu)>Sales By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Menu)>Sales By Menu',6201,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-09-09 05:51:55','administrator','2016-10-17 17:47:33',1,0),(47,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-09-24 16:46:57','administrator','2014-09-24 17:21:51',1,0),(48,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-09-24 16:58:17','administrator','2014-09-24 17:23:59',1,0),(49,'Sales Profit By Menu','dev@wepos.id','v.1.0.0','Sales Profit By Menu','billing','reportSalesProfitByMenu',0,'6. Reports>Sales (Profit)>Sales Profit By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Menu',6405,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-09-24 16:53:21','administrator','2016-10-17 19:38:07',1,0),(50,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-11-16 21:28:58','administrator','2014-12-09 19:08:45',1,0),(51,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6503,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-11-17 13:23:40','administrator','2014-12-09 19:08:25',1,0),(52,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6601,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2014-11-17 13:31:50','administrator','2014-12-09 19:00:32',1,0),(53,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6604,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-12-09 15:57:19','administrator','2014-12-09 19:01:16',1,0),(54,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6742,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2014-12-11 23:44:12','administrator','2016-10-18 00:45:36',1,0);

/*Table structure for table `apps_modules_method` */

DROP TABLE IF EXISTS `apps_modules_method`;

CREATE TABLE `apps_modules_method` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `method_function` varchar(100) CHARACTER SET latin1 NOT NULL,
  `module_id` int(11) NOT NULL,
  `method_description` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_modules_method` */

/*Table structure for table `apps_modules_preload` */

DROP TABLE IF EXISTS `apps_modules_preload`;

CREATE TABLE `apps_modules_preload` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `preload_filename` varchar(100) CHARACTER SET latin1 NOT NULL,
  `preload_folderpath` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `module_id` int(100) NOT NULL,
  `preload_description` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_modules_preload` */

/*Table structure for table `apps_options` */

DROP TABLE IF EXISTS `apps_options`;

CREATE TABLE `apps_options` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `option_var` varchar(100) NOT NULL,
  `option_value` text NOT NULL,
  `option_description` varchar(255) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `createdby` varchar(50) NOT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=latin1;

/*Data for the table `apps_options` */

insert  into `apps_options`(`id`,`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) values (1,'timezone_default','Asia/Jakarta','Timezone Asia/Jakarta','2014-09-08 23:12:43','administrator',NULL,'administrator',1,0),(2,'report_place_default','Bandung',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(3,'input_chinese_text','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(4,'payment_id_cash','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(5,'payment_id_debit','2',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(6,'payment_id_credit','3',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(7,'warehouse_primary','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(8,'auto_logout_time','3600000',NULL,'2016-01-16 12:12:12','administrator',NULL,NULL,1,0),(9,'stock_rekap_start_date','01-01-2016',NULL,'2016-02-12 18:00:00','administrator',NULL,NULL,1,0),(10,'stock_rekap_start_date','01-01-2016',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(11,'account_payable_non_accounting','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(12,'use_login_pin','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(13,'auto_add_supplier_ap','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(14,'receiving_select_warehouse','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(15,'pembulatan_dinamis','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(16,'wepos_tipe','cafe','retail/cafe/foodcourt','2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(17,'retail_warehouse','0',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(18,'auto_item_code','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(19,'item_code_separator','.',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(20,'item_code_format','{Cat}.{SubCat}.{ItemNo}',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(21,'item_no_length','4',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(22,'so_count_stock','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(23,'ds_count_stock','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(24,'ds_auto_terima','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(25,'auto_add_supplier_item_when_purchasing','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(26,'purchasing_request_order','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(27,'use_approval_po','0',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(28,'default_discount_payment','0',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(29,'management_systems','0',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(30,'ipserver_management_systems','',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(31,'big_size_width','1024',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(32,'big_size_height','768',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(33,'thumb_size_width','375',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(34,'thumb_size_height','250',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(35,'tiny_size_width','160',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(36,'tiny_size_height','120',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(37,'big_size_real','1',NULL,'2016-01-13 20:00:00','administrator',NULL,NULL,1,0),(38,'include_tax','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(39,'include_service','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(40,'role_id_kasir','1,2,3',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(41,'takeaway_no_tax','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(42,'takeaway_no_service','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(43,'use_pembulatan','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(44,'cashier_pembulatan_keatas','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(45,'cashier_max_pembulatan','100','MAX PEMBULATAN','2014-08-05 11:41:36','',NULL,NULL,1,0),(46,'default_tax_percentage','10','DEF TAX','2014-08-17 22:46:13','administrator','2014-08-10 03:44:35','administrator',1,0),(47,'default_service_percentage','0','DEF SERVICE','2014-08-17 22:46:36','administrator','2014-08-10 03:44:35','administrator',1,0),(48,'table_available_after_paid','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(49,'hide_compliment_order','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(50,'hide_takeaway_order_apps','1',NULL,'2016-01-06 14:50:09','administrator',NULL,NULL,1,0),(51,'hide_compliment_order_apps','1',NULL,'2016-01-06 14:50:09','administrator',NULL,NULL,1,0),(52,'spv_access_active','open_close_cashier,cancel_billing,cancel_order,retur_order,unmerge_billing,change_ppn,change_service,change_dp,set_compliment_item,clear_compliment_item,approval_po',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(53,'use_order_counter','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(54,'supervisor_pin_mode','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(55,'order_menu_after_booked_on_tablet','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(56,'order_menu_after_reserved_on_tablet','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(57,'autohold_create_billing','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(58,'diskon_sebelum_pajak_service','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(59,'no_midnight','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(60,'billing_log','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(61,'cashierReceipt_layout','[align=1][size=1]WePOS Cafe\n[size=0]JL. Kebon Sirih Dalam No.26, Bandung\nPHONE: 081222549676\n[set_tab1]\n[size=1]NO:{billing_no}[tab]{user}\n[size=0][align=0]-----------------------------------------------\n{order_data}\n[align=0]-----------------------------------------------\n[set_tab2]\n[tab]SUB TOTAL[tab]{subtotal}\n{hide_empty}[tab]PAJAK[tab]{tax_total}\n{hide_empty}[tab]DISC[tab]{potongan}\n{hide_empty}[tab]COMPLIMENT[tab]{compliment}\n{hide_empty}[tab]DP[tab]{dp_total}\n{hide_empty}[tab]PEMBULATAN[tab]{rounded}\n[size=1][tab]GRAND TOTAL[tab]{grand_total}\n[size=0][tab]TUNAI[tab]{cash}\n[tab]KEMBALI[tab]{return}\n[tab]{payment_type}\n','cashier print receipt layout','2014-09-08 04:20:46','','2014-09-20 17:58:56',NULL,1,0),(62,'kitchenReceipt_layout','PRINT OUT KITCHEN\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}\n','kitchen print layout - order done','2014-09-06 09:47:01','','2014-12-31 12:47:27',NULL,1,0),(63,'cashierReceipt_layout_footer','[align=1][size=1]\n[size=0][align=0]-----------------------------------------------\n[align=1]{date_time}\n\nPoint of Sales Solutions\nIG: @wepos.id\n\n\n\n','','2014-09-08 02:38:49','','2014-09-26 17:00:55',NULL,1,0),(64,'barReceipt_layout','PRINT OUT BAR\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}\n','bar receipt layout','2014-09-06 09:49:11','administrator','2014-12-31 12:47:27','administrator',1,0),(65,'qcReceipt_layout','PRINT OUT CHECKER\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data_kitchen}\n[size=0][set_tab1]{order_data_bar}','QC receipt layout','2014-09-08 02:51:16','administrator','2014-12-31 12:47:27','administrator',1,0),(66,'cashierReceipt_invoice_layout','[align=1][size=1]WePOS Cafe\n[size=0]JL. Kebon Sirih Dalam No.26, Bandung\nPHONE: 081222549676\n[set_tab1]\n[size=1]NO:{billing_no}[tab]{user}\n[size=0][align=0]-----------------------------------------------\n{order_data}\n[align=0]-----------------------------------------------\n[set_tab2]\n[tab]SUB TOTAL[tab]{subtotal}\n{hide_empty}[tab]PAJAK[tab]{tax_total}\n{hide_empty}[tab]DISC[tab]{potongan}\n{hide_empty}[tab]COMPLIMENT[tab]{compliment}\n{hide_empty}[tab]DP[tab]{dp_total}\n{hide_empty}[tab]PEMBULATAN[tab]{rounded}\n[size=1][tab]GRAND TOTAL[tab]{grand_total}','cashier print invoice layout','2014-09-08 04:26:35','administrator','2014-09-20 17:58:56','administrator',1,0),(67,'otherReceipt_layout','PRINT OUT OTHER\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}',NULL,'2016-01-06 09:16:23','administrator',NULL,NULL,1,0),(68,'cashierReceipt_bagihasil_layout','[align=1][size=1]WePOS Cafe\n[size=0]JL. Kebon Sirih Dalam No.26, Bandung\n{supplier_name}\n\n[set_tab1]\n[size=1]{tanggal_shift} {jam_shift}[tab]\n[size=0][align=0]-----------------------------------------------\n{sales_data}\n[align=0]-----------------------------------------------\n[set_tab1]\n[size=0]TOTAL ITEM[tab]{total_qty}\nTOTAL SALES[tab]{total_sales}\nTOTAL TOKO[tab]{total_toko}\nTOTAL SUPPLIER[tab]{total_supplier}\n\n',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(69,'cashierReceipt_settlement_layout','[align=1][size=1]WePOS Cafe\n[size=0]JL. Kebon Sirih Dalam No.26, Bandung\n\nSETTLEMENT\n[set_tab1]\n[align=0][size=0]{tanggal_shift} {jam_shift}[tab]\n[size=0][align=0]----------------------------------------\n[set_tab3]\n{summary_data}\n[align=0]----------------------------------------\n[set_tab3]\n[align=0]{payment_data}\n\n',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(70,'cashierReceipt_openclose_layout','[align=1][size=1]WePOS Cafe\n[size=0]JL. Kebon Sirih Dalam No.26, Bandung\n\n[set_tab1]\n[align=0][size=0]{tipe_openclose}: {shift_on}[tab]\n[align=0][size=0]{tanggal_shift} {jam_shift}[tab]\n[size=0][align=0]----------------------------------------[set_tab3]\n{uang_kertas_data}\n{uang_koin_data}{summary_data}\n[align=0]----------------------------------------[set_tab3]\n[align=0]{payment_data}approved: {spv_user}\n\n\n',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(71,'print_chinese_text','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(72,'print_order_peritem_kitchen','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(73,'print_order_peritem_bar','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(74,'print_order_peritem_other','0',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(75,'printMonitoring_qc','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(76,'printMonitoring_kitchen','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(77,'printMonitoring_bar','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(78,'printMonitoring_other','1',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(79,'printMonitoringTime_qc','2000',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(80,'printMonitoringTime_kitchen','2000',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(81,'printMonitoringTime_bar','2000',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(82,'printMonitoringTime_other','2000',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(83,'cleanPrintMonitoring','06:00',NULL,'2015-12-23 19:00:00','administrator',NULL,NULL,1,0),(84,'show_multiple_print_qc','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(85,'multiple_print_qc','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(86,'multiple_print_qc','2',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(87,'multiple_print_qc','3',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(88,'print_qc_then_order','0',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(89,'show_multiple_print_billing','0',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(90,'multiple_print_billing','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(91,'multiple_print_billing','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(92,'print_qc_order_when_payment','1',NULL,'2016-08-11 20:00:00','administrator',NULL,NULL,1,0),(93,'do_print_cashierReceipt_default','1',NULL,'2017-06-28 19:57:55','administrator','2017-06-28 19:57:55','administrator',1,0),(94,'printer_tipe_cashierReceipt_default','EPSON',NULL,'2017-06-28 19:57:55','administrator','2017-06-28 19:57:55','administrator',1,0),(95,'printer_pin_cashierReceipt_default','40 CHAR',NULL,'2017-06-28 19:57:55','administrator','2017-06-28 19:57:55','administrator',1,0),(96,'printer_ip_cashierReceipt_default','127.0.0.1\\printer_name',NULL,'2017-06-28 19:57:55','administrator','2017-06-28 19:57:55','administrator',1,0),(97,'printer_id_cashierReceipt_default','1',NULL,'2017-06-28 19:57:55','administrator','2017-06-28 19:57:55','administrator',1,0),(98,'do_print_qcReceipt_default','1',NULL,'2017-07-05 00:11:02','administrator','2017-07-05 00:11:02','administrator',1,0),(99,'printer_tipe_qcReceipt_default','EPSON',NULL,'2017-07-05 00:11:02','administrator','2017-07-05 00:11:02','administrator',1,0),(100,'printer_pin_qcReceipt_default','40 CHAR',NULL,'2017-07-05 00:11:02','administrator','2017-07-05 00:11:02','administrator',1,0),(101,'printer_ip_qcReceipt_default','127.0.0.1\\printer_name',NULL,'2017-07-05 00:11:02','administrator','2017-07-05 00:11:02','administrator',1,0),(102,'printer_id_qcReceipt_default','1',NULL,'2017-07-05 00:11:02','administrator','2017-07-05 00:11:02','administrator',1,0),(103,'do_print_kitchenReceipt_default','1',NULL,'2017-07-05 00:09:35','administrator','2017-07-05 00:09:35','administrator',1,0),(104,'printer_tipe_kitchenReceipt_default','EPSON',NULL,'2017-07-05 00:09:35','administrator','2017-07-05 00:09:35','administrator',1,0),(105,'printer_pin_kitchenReceipt_default','40 CHAR',NULL,'2017-07-05 00:09:35','administrator','2017-07-05 00:09:35','administrator',1,0),(106,'printer_ip_kitchenReceipt_default','127.0.0.1\\printer_name',NULL,'2017-07-05 00:09:35','administrator','2017-07-05 00:09:35','administrator',1,0),(107,'printer_id_kitchenReceipt_default','1',NULL,'2017-07-05 00:09:35','administrator','2017-07-05 00:09:35','administrator',1,0),(108,'do_print_barReceipt_default','1',NULL,'2017-06-28 19:30:31','administrator','2017-06-28 19:30:31','administrator',1,0),(109,'printer_tipe_barReceipt_default','EPSON',NULL,'2017-06-28 19:30:31','administrator','2017-06-28 19:30:31','administrator',1,0),(110,'printer_pin_barReceipt_default','42 CHAR',NULL,'2017-06-28 19:30:31','administrator','2017-06-28 19:30:31','administrator',1,0),(111,'printer_ip_barReceipt_default','127.0.0.1\\printer_name',NULL,'2017-06-28 19:30:31','administrator','2017-06-28 19:30:31','administrator',1,0),(112,'printer_id_barReceipt_default','1',NULL,'2017-06-28 19:30:31','administrator','2017-06-28 19:30:31','administrator',1,0),(113,'do_print_otherReceipt_default','1',NULL,'2017-07-05 00:09:24','administrator','2017-07-05 00:09:24','administrator',1,0),(114,'printer_tipe_otherReceipt_default','EPSON',NULL,'2017-07-05 00:09:24','administrator','2017-07-05 00:09:24','administrator',1,0),(115,'printer_pin_otherReceipt_default','40 CHAR',NULL,'2017-07-05 00:09:24','administrator','2017-07-05 00:09:24','administrator',1,0),(116,'printer_ip_otherReceipt_default','127.0.0.1\\printer_name',NULL,'2017-07-05 00:09:24','administrator','2017-07-05 00:09:24','administrator',1,0),(117,'printer_id_otherReceipt_default','1',NULL,'2017-07-05 00:09:24','administrator','2017-07-05 00:09:24','administrator',1,0),(118,'closing_sales_start_date','01-01-2016',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(119,'closing_purchasing_start_date','01-01-2016',NULL,'2016-02-09 00:00:00','',NULL,NULL,1,0),(120,'closing_inventory_start_date','01-01-2016',NULL,'2016-11-02 12:00:00','',NULL,NULL,1,0),(121,'closing_accounting_start_date','01-01-2016',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(122,'autoclosing_generate_sales','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(123,'autoclosing_generate_purchasing','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(124,'autoclosing_generate_inventory','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(125,'autoclosing_generate_stock','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(126,'autoclosing_generate_accounting','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(127,'autoclosing_closing_sales','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(128,'autoclosing_closing_purchasing','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(129,'autoclosing_closing_inventory','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(130,'autoclosing_closing_accounting','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(131,'autoclosing_auto_cancel_billing','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(132,'autoclosing_auto_cancel_receiving','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(133,'autoclosing_auto_cancel_distribution','1',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(134,'autoclosing_skip_open_jurnal','0',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(135,'autoclosing_generate_timer','360000',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(136,'autoclosing_closing_time','03:00',NULL,'2016-03-01 12:00:43','',NULL,NULL,1,0),(137,'autoclosing_auto_cancel_production','1',NULL,'2016-03-08 12:43:06','',NULL,NULL,1,0),(138,'salary_end_month','0',NULL,'2016-12-31 09:02:01','administrator',NULL,NULL,1,0),(139,'salary_end_date','22',NULL,'2016-12-31 09:02:01','administrator',NULL,NULL,1,0),(140,'salary_start_month','1',NULL,'2016-12-31 09:02:01','administrator',NULL,NULL,1,0),(141,'salary_start_date','23',NULL,'2016-12-31 09:02:01','administrator',NULL,NULL,1,0),(142,'bln_aktif_sebelumnya','12',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(143,'thn_aktif_sebelumnya','2015',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(144,'bln_aktif_saat_ini','05',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(145,'thn_aktif_saat_ini','2016',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(146,'bln_aktif_akan_datang','06',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(147,'thn_aktif_akan_datang','2016',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(148,'bln_periode_saldo_awal','12',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(149,'thn_periode_saldo_awal','2015',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(150,'bulan_berjalan','04',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(151,'tahun_berjalan','2016',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(152,'tutup_bulan_lap','1',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(153,'tutup_periode_saldo_awal','1',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(154,'bulan_baru','1',NULL,'2016-02-11 11:01:24','',NULL,NULL,1,0),(155,'closing_saldo_awal','',NULL,'2016-02-18 14:52:24','',NULL,NULL,1,0),(156,'update_closing_saldo_awal','2016-03-11 11:24:24',NULL,'2016-02-18 14:52:24','',NULL,NULL,1,0),(157,'updated_by_closing_saldo_awal','administrator',NULL,'2016-02-18 14:52:24','',NULL,NULL,1,0),(158,'spv_closing_saldo_awal','administrator',NULL,'2016-02-18 14:52:24','',NULL,NULL,1,0),(159,'laporan_cashflow_level','2',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(160,'laporan_labarugi_level','2',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(161,'laporan_neraca_level','2',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(162,'kel_LR_biaya_atas_pendapatan','505',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(163,'kel_CF_kas_dan_setara_kas','106',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(164,'kd_tipe_jurnal_ap','3',NULL,'2016-04-09 17:00:00','administrator',NULL,NULL,1,0),(165,'kd_tipe_jurnal_pelunasan_ap','4',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(166,'kel_LR_hpp','508',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(167,'account_receivable_non_accounting','1',NULL,'2016-02-06 20:00:00','administrator',NULL,NULL,1,0),(168,'persediaan_barang','average','average, fifo','2016-08-11 20:00:00','administrator',NULL,NULL,1,0);

/*Table structure for table `apps_roles` */

DROP TABLE IF EXISTS `apps_roles`;

CREATE TABLE `apps_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(25) CHARACTER SET latin1 NOT NULL,
  `role_description` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `role_window_mode` enum('full','lite') COLLATE latin1_general_ci DEFAULT 'full',
  `client_id` int(11) NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_roles` */

insert  into `apps_roles`(`id`,`role_name`,`role_description`,`role_window_mode`,`client_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Super Admin','Super Admin Roles','full',1,'1','2014-08-11 21:20:00','admin','2017-09-14 21:23:51',1,0),(2,'Admin','Admin App','full',1,'administrator','2016-05-22 14:21:07','admin','2017-09-14 21:24:01',1,0),(3,'Cashier','Cashier Roles','full',1,'administrator','2014-08-11 09:29:02','admin','2016-09-09 14:51:18',1,0),(4,'BackOffice','Unit BackOffice','full',1,'1','2014-06-23 00:08:20','administrator','2014-06-23 02:08:20',1,0),(5,'Supervisor','Supervisor Roles','full',1,'administrator','2014-08-11 21:20:21','admin','2016-09-09 15:02:33',1,0),(6,'Purchasing','Purchasing','full',1,'administrator','2014-09-13 11:21:50','admin','2016-09-09 14:50:10',1,0),(7,'Service','Service Mode','full',1,'administrator','2014-12-10 15:40:51','administrator','2016-05-22 14:17:51',1,0);

/*Table structure for table `apps_roles_module` */

DROP TABLE IF EXISTS `apps_roles_module`;

CREATE TABLE `apps_roles_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `start_menu_path` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `module_order` int(11) DEFAULT '0',
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_idi_group_rule_list` (`module_id`),
  KEY `FK_idi_group_rule_list2` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_roles_module` */

insert  into `apps_roles_module`(`id`,`role_id`,`module_id`,`start_menu_path`,`module_order`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,40,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(2,1,39,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(3,1,45,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(4,1,46,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(5,1,49,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(6,1,47,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(7,1,48,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(8,1,43,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(9,1,44,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(10,1,33,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(11,1,34,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(12,1,31,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(13,1,32,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(14,1,30,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(15,1,37,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(16,1,54,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(17,1,36,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(18,1,52,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(19,1,53,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(20,1,38,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(21,1,21,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(22,1,18,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(23,1,19,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(24,1,23,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(25,1,24,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(26,1,20,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(27,1,14,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(28,1,17,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(29,1,25,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(30,1,16,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(31,1,15,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(32,1,13,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(33,1,28,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(34,1,22,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(35,1,29,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(36,1,26,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(37,1,27,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(38,1,42,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(39,1,41,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(40,1,35,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(41,1,50,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(42,1,51,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(43,1,1,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(44,1,2,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(45,1,3,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(46,1,6,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(47,1,8,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(48,1,11,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(49,1,12,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(50,1,9,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(51,1,10,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(52,1,4,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(53,1,7,NULL,0,'admin','2017-09-14 21:23:51','admin','2017-09-14 21:23:51',1,0),(54,2,40,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(55,2,39,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(56,2,45,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(57,2,46,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(58,2,49,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(59,2,47,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(60,2,48,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(61,2,43,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(62,2,44,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(63,2,33,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(64,2,34,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(65,2,31,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(66,2,32,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(67,2,30,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(68,2,37,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(69,2,54,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(70,2,36,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(71,2,52,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(72,2,53,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(73,2,38,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(74,2,21,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(75,2,18,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(76,2,19,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(77,2,23,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(78,2,24,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(79,2,20,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(80,2,14,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(81,2,17,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(82,2,25,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(83,2,16,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(84,2,15,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(85,2,13,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(86,2,28,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(87,2,22,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(88,2,29,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(89,2,26,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(90,2,27,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(91,2,42,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(92,2,41,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(93,2,35,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(94,2,50,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(95,2,51,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(96,2,1,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(97,2,2,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(98,2,3,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(99,2,6,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(100,2,8,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(101,2,11,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(102,2,12,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(103,2,9,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(104,2,10,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(105,2,4,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0),(106,2,7,NULL,0,'admin','2017-09-14 21:24:01','admin','2017-09-14 21:24:01',1,0);

/*Table structure for table `apps_roles_widget` */

DROP TABLE IF EXISTS `apps_roles_widget`;

CREATE TABLE `apps_roles_widget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `widget_id` int(11) NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_idi_group_rule_list` (`widget_id`),
  KEY `FK_idi_group_rule_list2` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_roles_widget` */

/*Table structure for table `apps_supervisor` */

DROP TABLE IF EXISTS `apps_supervisor`;

CREATE TABLE `apps_supervisor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(25) NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_supervisor` */

insert  into `apps_supervisor`(`id`,`user_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,'administrator','2014-08-16 11:55:26','administrator','2014-08-10 10:44:35',1,0),(2,2,'admin','2016-05-25 19:48:20','admin','2016-05-25 19:48:20',1,0);

/*Table structure for table `apps_supervisor_access` */

DROP TABLE IF EXISTS `apps_supervisor_access`;

CREATE TABLE `apps_supervisor_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisor_id` int(25) NOT NULL,
  `supervisor_access` varchar(100) COLLATE latin1_general_ci NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_supervisor_access` */

insert  into `apps_supervisor_access`(`id`,`supervisor_id`,`supervisor_access`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,2,'cancel_billing','admin','2016-05-25 19:48:20','admin','2016-05-25 19:48:20',1,0),(2,2,'open_close_cashier','admin','2016-09-09 13:05:54','admin','2016-09-09 13:05:54',1,0),(3,3,'unmerge_billing','admin','2017-09-02 19:35:06','admin','2017-09-02 19:35:06',1,0),(4,3,'cancel_order','demo','2017-09-08 00:40:27','demo','2017-09-08 00:40:27',1,0),(5,3,'cancel_billing','demo','2017-09-08 00:40:36','demo','2017-09-08 00:40:36',1,0);

/*Table structure for table `apps_supervisor_log` */

DROP TABLE IF EXISTS `apps_supervisor_log`;

CREATE TABLE `apps_supervisor_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisor_id` int(25) NOT NULL,
  `supervisor_access_id` int(11) DEFAULT NULL,
  `supervisor_access` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `log_data` text COLLATE latin1_general_ci NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_supervisor_log` */

/*Table structure for table `apps_users` */

DROP TABLE IF EXISTS `apps_users`;

CREATE TABLE `apps_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_username` varchar(50) CHARACTER SET latin1 NOT NULL,
  `user_password` varchar(64) CHARACTER SET latin1 NOT NULL,
  `role_id` int(11) NOT NULL,
  `user_firstname` varchar(100) CHARACTER SET latin1 NOT NULL,
  `user_lastname` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `user_email` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `user_phone` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `user_mobile` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `user_address` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `client_id` int(11) NOT NULL DEFAULT '1',
  `client_structure_id` int(11) NOT NULL,
  `avatar` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `user_pin` char(8) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_username` (`user_username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users` */

insert  into `apps_users`(`id`,`user_username`,`user_password`,`role_id`,`user_firstname`,`user_lastname`,`user_email`,`user_phone`,`user_mobile`,`user_address`,`client_id`,`client_structure_id`,`avatar`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`user_pin`) values (1,'administrator','202cb962ac59075b964b07152d234b70',1,'Super','Admin','contact@aplikasi-pos.com','6281222549676','1231239990111','Bandung - West Java - Indonesia',1,1,'0','1','2014-06-23 05:05:55','administrator','2016-05-22 14:27:50',1,0,'9999'),(2,'admin','202cb962ac59075b964b07152d234b70',2,'Admin','WePOS','contact@wepos.id','','','',1,2,'0','administrator','2016-05-22 14:28:58','administrator','2016-06-13 09:42:34',1,0,'1234');

/*Table structure for table `apps_users_desktop` */

DROP TABLE IF EXISTS `apps_users_desktop`;

CREATE TABLE `apps_users_desktop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `dock` enum('top','bottom','left','right') CHARACTER SET latin1 NOT NULL DEFAULT 'bottom',
  `window_mode` enum('full','lite') COLLATE latin1_general_ci DEFAULT 'full',
  `wallpaper` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT 'default.jpg',
  `wallpaperStretch` tinyint(1) NOT NULL DEFAULT '0',
  `wallpaper_id` int(11) NOT NULL DEFAULT '1',
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users_desktop` */

insert  into `apps_users_desktop`(`id`,`user_id`,`dock`,`window_mode`,`wallpaper`,`wallpaperStretch`,`wallpaper_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,'bottom','full','default.jpg',1,1,'','2014-07-02 07:21:35',NULL,NULL,1,0),(2,2,'bottom','full','default.jpg',0,1,NULL,NULL,NULL,NULL,1,0);

/*Table structure for table `apps_users_quickstart` */

DROP TABLE IF EXISTS `apps_users_quickstart`;

CREATE TABLE `apps_users_quickstart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users_quickstart` */

/*Table structure for table `apps_users_shortcut` */

DROP TABLE IF EXISTS `apps_users_shortcut`;

CREATE TABLE `apps_users_shortcut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users_shortcut` */

/*Table structure for table `apps_widgets` */

DROP TABLE IF EXISTS `apps_widgets`;

CREATE TABLE `apps_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `widget_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `widget_author` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `widget_version` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `widget_description` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `widget_controller` varchar(255) CHARACTER SET latin1 NOT NULL,
  `widget_order` int(5) DEFAULT '0',
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_controller` (`widget_controller`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_widgets` */

insert  into `apps_widgets`(`id`,`widget_name`,`widget_author`,`widget_version`,`widget_description`,`widget_controller`,`widget_order`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'User Info','angga.nugraha@gmail.com','v1.0.0','Show User Information','userInfo',1001,'administrator','2014-06-23 02:24:58','administrator','0000-00-00 00:00:00',1,0),(2,'new widget','angga.nugraha@gmail.com','v.1.0','','NewWidget',1002,'administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0);

/*Table structure for table `pos_bank` */

DROP TABLE IF EXISTS `pos_bank`;

CREATE TABLE `pos_bank` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bank_code` varchar(10) DEFAULT NULL,
  `bank_name` varchar(255) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_code_idx` (`bank_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

/*Data for the table `pos_bank` */

insert  into `pos_bank`(`id`,`bank_code`,`bank_name`,`payment_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'B1','BCA',3,'administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(2,'B2','BCA',2,'administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(3,'B3','Bank Mandiri',3,'administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(4,'B4','Bank Mandiri',2,'administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0);

/*Table structure for table `pos_billing` */

DROP TABLE IF EXISTS `pos_billing`;

CREATE TABLE `pos_billing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_no` varchar(20) NOT NULL,
  `table_id` mediumint(9) DEFAULT NULL,
  `table_no` char(20) DEFAULT NULL,
  `billing_status` enum('paid','unpaid','hold','cancel') DEFAULT 'unpaid',
  `total_billing` double DEFAULT '0',
  `total_paid` double DEFAULT '0',
  `total_pembulatan` double DEFAULT '0',
  `billing_notes` char(100) DEFAULT NULL,
  `payment_id` tinyint(4) NOT NULL,
  `payment_date` datetime DEFAULT NULL,
  `bank_id` tinyint(4) DEFAULT NULL,
  `card_no` char(50) DEFAULT NULL,
  `include_tax` tinyint(1) DEFAULT '0',
  `tax_percentage` decimal(5,2) DEFAULT '0.00' COMMENT 'will added to total',
  `tax_total` double DEFAULT '0',
  `include_service` tinyint(1) DEFAULT '0',
  `service_percentage` decimal(5,2) DEFAULT '0.00',
  `service_total` double DEFAULT '0',
  `discount_id` mediumint(9) DEFAULT NULL,
  `discount_notes` char(100) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `discount_price` double DEFAULT '0',
  `discount_total` double DEFAULT '0',
  `is_compliment` tinyint(1) DEFAULT '0',
  `is_half_payment` tinyint(1) DEFAULT '0',
  `total_cash` double DEFAULT '0',
  `total_credit` double DEFAULT '0',
  `total_hpp` double DEFAULT '0',
  `total_guest` smallint(6) DEFAULT '1',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `merge_id` int(11) DEFAULT NULL,
  `merge_main_status` tinyint(1) DEFAULT '0',
  `split_from_id` int(11) DEFAULT NULL,
  `takeaway_no_tax` tinyint(1) DEFAULT '0',
  `takeaway_no_service` tinyint(1) DEFAULT '0',
  `total_dp` double DEFAULT '0',
  `grand_total` double DEFAULT '0',
  `total_return` double DEFAULT '0',
  `discount_perbilling` tinyint(1) DEFAULT '0',
  `voucher_no` char(100) DEFAULT NULL,
  `compliment_total` double DEFAULT '0',
  `compliment_total_tax_service` double DEFAULT '0',
  `cancel_notes` char(100) DEFAULT NULL,
  `sales_id` mediumint(9) DEFAULT NULL,
  `sales_percentage` decimal(5,2) DEFAULT '0.00',
  `sales_price` double DEFAULT '0',
  `sales_type` char(20) DEFAULT NULL,
  `lock_billing` tinyint(1) DEFAULT '0',
  `qc_notes` varchar(100) DEFAULT NULL,
  `storehouse_id` int(11) DEFAULT '0',
  `is_sistem_tawar` tinyint(1) DEFAULT '0',
  `customer_id` int(11) DEFAULT '0',
  `single_rate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_no` (`billing_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_billing` */

/*Table structure for table `pos_billing_additional_price` */

DROP TABLE IF EXISTS `pos_billing_additional_price`;

CREATE TABLE `pos_billing_additional_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `additional_price_id` int(11) NOT NULL,
  `total_price` double DEFAULT '0',
  `billing_id` int(11) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_billing_additional_price` */

/*Table structure for table `pos_billing_detail` */

DROP TABLE IF EXISTS `pos_billing_detail`;

CREATE TABLE `pos_billing_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` mediumint(9) NOT NULL,
  `order_qty` smallint(6) DEFAULT '0',
  `product_price` double DEFAULT '0',
  `product_price_hpp` double DEFAULT '0',
  `product_normal_price` double DEFAULT '0',
  `category_id` tinyint(4) DEFAULT NULL,
  `billing_id` int(11) NOT NULL,
  `order_status` enum('order','progress','done','cancel') DEFAULT 'order',
  `order_notes` char(100) DEFAULT NULL,
  `order_day_counter` int(11) DEFAULT NULL,
  `order_counter` smallint(6) DEFAULT '0',
  `retur_type` enum('none','payment','menu') DEFAULT 'none',
  `retur_qty` smallint(6) DEFAULT '0',
  `retur_reason` char(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `billing_id_before_merge` int(11) DEFAULT NULL,
  `cancel_order_notes` char(100) DEFAULT NULL,
  `order_qty_split` smallint(6) DEFAULT NULL,
  `product_price_real` double DEFAULT '0',
  `has_varian` tinyint(1) DEFAULT '0',
  `varian_id` mediumint(9) DEFAULT NULL,
  `product_varian_id` int(11) DEFAULT NULL,
  `print_qc` tinyint(1) DEFAULT '0',
  `print_order` tinyint(1) DEFAULT '0',
  `include_tax` tinyint(1) DEFAULT '1',
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `tax_total` double DEFAULT '0',
  `include_service` tinyint(1) DEFAULT '1',
  `service_percentage` decimal(5,2) DEFAULT '0.00',
  `service_total` double DEFAULT '0',
  `is_takeaway` tinyint(1) DEFAULT '0',
  `takeaway_no_tax` tinyint(1) DEFAULT '0',
  `takeaway_no_service` tinyint(1) DEFAULT '0',
  `is_compliment` tinyint(1) DEFAULT '0',
  `discount_id` mediumint(9) DEFAULT NULL,
  `discount_notes` char(100) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `discount_price` double DEFAULT '0',
  `discount_total` double DEFAULT '0',
  `is_promo` tinyint(1) DEFAULT '0',
  `promo_id` mediumint(9) DEFAULT NULL,
  `promo_tipe` tinyint(1) DEFAULT '0',
  `promo_desc` char(100) DEFAULT NULL,
  `promo_percentage` decimal(5,2) DEFAULT '0.00',
  `promo_price` double DEFAULT '0',
  `is_kerjasama` tinyint(1) DEFAULT '0',
  `supplier_id` int(11) DEFAULT '0',
  `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
  `total_bagi_hasil` double DEFAULT '0',
  `grandtotal_bagi_hasil` double DEFAULT '0',
  `is_buyget` tinyint(1) DEFAULT '0',
  `buyget_id` int(11) DEFAULT '0',
  `buyget_tipe` varchar(20) DEFAULT NULL,
  `buyget_percentage` decimal(5,2) DEFAULT '0.00',
  `buyget_total` double DEFAULT '0',
  `buyget_qty` smallint(6) DEFAULT '0',
  `buyget_desc` varchar(100) DEFAULT '',
  `buyget_item` int(11) DEFAULT '0',
  `free_item` tinyint(1) DEFAULT '0',
  `ref_order_id` int(11) DEFAULT '0',
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  `data_stok_kode_unik` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_billing_detail` */

/*Table structure for table `pos_billing_detail_split` */

DROP TABLE IF EXISTS `pos_billing_detail_split`;

CREATE TABLE `pos_billing_detail_split` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` mediumint(9) NOT NULL,
  `order_qty` smallint(6) DEFAULT '0',
  `product_price` double DEFAULT '0',
  `product_price_hpp` double DEFAULT '0',
  `product_normal_price` double DEFAULT '0',
  `category_id` tinyint(4) DEFAULT NULL,
  `billing_id` int(11) NOT NULL,
  `order_status` enum('order','progress','done','cancel') DEFAULT 'order',
  `order_notes` char(100) DEFAULT NULL,
  `order_day_counter` int(11) DEFAULT NULL,
  `order_counter` smallint(6) DEFAULT '0',
  `retur_type` enum('none','payment','menu') DEFAULT 'none',
  `retur_qty` smallint(6) DEFAULT '0',
  `retur_reason` char(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `billing_id_before_merge` int(11) DEFAULT NULL,
  `cancel_order_notes` char(100) DEFAULT NULL,
  `billing_detail_id` int(11) DEFAULT NULL,
  `order_qty_split` smallint(6) DEFAULT NULL,
  `product_price_real` double DEFAULT '0',
  `has_varian` tinyint(1) DEFAULT '0',
  `varian_id` mediumint(9) DEFAULT NULL,
  `product_varian_id` int(11) DEFAULT NULL,
  `print_qc` tinyint(1) DEFAULT '0',
  `print_order` tinyint(1) DEFAULT '0',
  `include_tax` tinyint(1) DEFAULT '1',
  `tax_percentage` decimal(5,2) DEFAULT '0.00',
  `tax_total` double DEFAULT '0',
  `include_service` tinyint(1) DEFAULT '1',
  `service_percentage` decimal(5,2) DEFAULT '0.00',
  `service_total` double DEFAULT '0',
  `is_takeaway` tinyint(1) DEFAULT '0',
  `takeaway_no_tax` tinyint(1) DEFAULT '0',
  `takeaway_no_service` tinyint(1) DEFAULT '0',
  `is_compliment` tinyint(1) DEFAULT '0',
  `discount_id` mediumint(9) DEFAULT NULL,
  `discount_notes` char(100) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `discount_price` double DEFAULT '0',
  `discount_total` double DEFAULT '0',
  `is_promo` tinyint(1) DEFAULT '0',
  `promo_id` mediumint(9) DEFAULT NULL,
  `promo_tipe` tinyint(1) DEFAULT '0',
  `promo_desc` char(100) DEFAULT NULL,
  `promo_percentage` decimal(5,2) DEFAULT '0.00',
  `promo_price` double DEFAULT '0',
  `is_kerjasama` tinyint(1) DEFAULT '0',
  `supplier_id` int(11) DEFAULT '0',
  `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
  `total_bagi_hasil` double DEFAULT '0',
  `grandtotal_bagi_hasil` double DEFAULT '0',
  `is_buyget` tinyint(1) DEFAULT '0',
  `buyget_id` int(11) DEFAULT '0',
  `buyget_tipe` varchar(20) DEFAULT NULL,
  `buyget_percentage` decimal(5,2) DEFAULT '0.00',
  `buyget_total` double DEFAULT '0',
  `buyget_qty` smallint(6) DEFAULT '0',
  `buyget_desc` varchar(100) DEFAULT '',
  `buyget_item` int(11) DEFAULT '0',
  `free_item` tinyint(1) DEFAULT '0',
  `ref_order_id` int(11) DEFAULT '0',
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  `data_stok_kode_unik` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_billing_detail_split` */

/*Table structure for table `pos_billing_log` */

DROP TABLE IF EXISTS `pos_billing_log`;

CREATE TABLE `pos_billing_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_id` int(25) NOT NULL,
  `trx_type` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `trx_info` varchar(255) COLLATE latin1_general_ci DEFAULT NULL,
  `log_data` text COLLATE latin1_general_ci NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_billing_log` */

/*Table structure for table `pos_closing` */

DROP TABLE IF EXISTS `pos_closing`;

CREATE TABLE `pos_closing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `bulan` char(2) DEFAULT NULL,
  `tahun` char(4) DEFAULT NULL,
  `tipe` enum('sales','purchasing','inventory','hrd','accounting') DEFAULT NULL,
  `closing_status` tinyint(1) DEFAULT '0',
  `generate_status` tinyint(1) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_closing` */

/*Table structure for table `pos_closing_log` */

DROP TABLE IF EXISTS `pos_closing_log`;

CREATE TABLE `pos_closing_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `tipe` varchar(100) DEFAULT NULL,
  `task` varchar(100) DEFAULT NULL,
  `task_status` varchar(15) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_closing_log` */

/*Table structure for table `pos_closing_purchasing` */

DROP TABLE IF EXISTS `pos_closing_purchasing`;

CREATE TABLE `pos_closing_purchasing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `po_total` smallint(6) DEFAULT '0',
  `po_total_supplier` smallint(6) DEFAULT '0',
  `po_total_item` smallint(6) DEFAULT '0',
  `po_status_done` smallint(6) DEFAULT '0',
  `po_status_progress` smallint(6) DEFAULT '0',
  `po_qty_item` float DEFAULT '0',
  `po_sub_total` double DEFAULT '0',
  `po_discount` double DEFAULT '0',
  `po_tax` double DEFAULT '0',
  `po_shipping` double DEFAULT '0',
  `po_grand_total` double DEFAULT '0',
  `po_qty_cash` smallint(6) DEFAULT '0',
  `po_total_cash` double DEFAULT '0',
  `po_qty_credit` smallint(6) DEFAULT '0',
  `po_total_credit` double DEFAULT '0',
  `receiving_total` smallint(6) DEFAULT '0',
  `receiving_total_po` smallint(6) DEFAULT '0',
  `receiving_total_supplier` smallint(6) DEFAULT '0',
  `receiving_total_item` smallint(6) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `po_total_ro` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_closing_purchasing` */

/*Table structure for table `pos_closing_sales` */

DROP TABLE IF EXISTS `pos_closing_sales`;

CREATE TABLE `pos_closing_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date DEFAULT NULL,
  `qty_billing` smallint(6) DEFAULT '0',
  `total_guest` smallint(6) DEFAULT '0',
  `total_billing` double DEFAULT '0',
  `tax_total` double DEFAULT '0',
  `service_total` double DEFAULT '0',
  `discount_total` double DEFAULT '0',
  `total_dp` double DEFAULT '0',
  `grand_total` double DEFAULT '0',
  `sub_total` double DEFAULT '0',
  `total_pembulatan` double DEFAULT '0',
  `total_compliment` double DEFAULT '0',
  `total_hpp` double DEFAULT '0',
  `total_profit` double DEFAULT '0',
  `qty_halfpayment` smallint(6) DEFAULT '0',
  `total_payment_1` double DEFAULT '0',
  `qty_payment_1` smallint(6) DEFAULT '0',
  `total_payment_2` double DEFAULT '0',
  `qty_payment_2` smallint(6) DEFAULT '0',
  `total_payment_3` double DEFAULT '0',
  `qty_payment_3` smallint(6) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_closing_sales` */

/*Table structure for table `pos_customer` */

DROP TABLE IF EXISTS `pos_customer`;

CREATE TABLE `pos_customer` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(10) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_contact_person` varchar(40) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(100) DEFAULT NULL,
  `customer_fax` varchar(100) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_customer` */

/*Table structure for table `pos_customer_member` */

DROP TABLE IF EXISTS `pos_customer_member`;

CREATE TABLE `pos_customer_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_customer_member` */

/*Table structure for table `pos_discount` */

DROP TABLE IF EXISTS `pos_discount`;

CREATE TABLE `pos_discount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_name` varchar(100) NOT NULL,
  `discount_percentage` decimal(5,2) DEFAULT '0.00',
  `discount_price` double DEFAULT '0',
  `min_total_billing` double DEFAULT '0' COMMENT 'optional condition using discount',
  `discount_date_type` enum('limited_date','unlimited_date') DEFAULT 'limited_date',
  `discount_product` tinyint(1) DEFAULT '0' COMMENT '0 = all product, 1 = dicount per-product',
  `discount_desc` varchar(100) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_discount_billing` tinyint(1) DEFAULT '0',
  `discount_max_price` double DEFAULT '0',
  `discount_type` tinyint(1) DEFAULT '0',
  `is_promo` tinyint(1) DEFAULT '0',
  `discount_allow_day` tinyint(2) DEFAULT '0',
  `use_discount_time` tinyint(1) DEFAULT '0',
  `discount_time_start` varchar(15) DEFAULT NULL,
  `discount_time_end` varchar(15) DEFAULT NULL,
  `is_buy_get` tinyint(1) DEFAULT '0',
  `is_sistem_tawar` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `pos_discount` */

insert  into `pos_discount`(`id`,`discount_name`,`discount_percentage`,`discount_price`,`min_total_billing`,`discount_date_type`,`discount_product`,`discount_desc`,`date_start`,`date_end`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_discount_billing`,`discount_max_price`,`discount_type`,`is_promo`,`discount_allow_day`,`use_discount_time`,`discount_time_start`,`discount_time_end`,`is_buy_get`,`is_sistem_tawar`) values (1,'Discount 10%','10.00',0,0,'unlimited_date',0,'','0000-00-00 00:00:00','0000-00-00 00:00:00','administrator','2016-05-07 11:10:20','administrator','2016-05-16 14:24:53',1,1,0,0,0,1,0,1,'12:00 AM','12:00 AM',0,0);

/*Table structure for table `pos_discount_buyget` */

DROP TABLE IF EXISTS `pos_discount_buyget`;

CREATE TABLE `pos_discount_buyget` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_id` int(11) NOT NULL,
  `buyget_tipe` enum('item','percentage') DEFAULT 'item',
  `buy_item` int(11) DEFAULT '0',
  `buy_qty` smallint(6) DEFAULT NULL,
  `get_item` int(11) NOT NULL,
  `get_qty` smallint(6) DEFAULT NULL,
  `get_percentage` decimal(5,2) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`,`get_item`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_discount_buyget` */

/*Table structure for table `pos_discount_product` */

DROP TABLE IF EXISTS `pos_discount_product`;

CREATE TABLE `pos_discount_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_discount_product` */

/*Table structure for table `pos_discount_voucher` */

DROP TABLE IF EXISTS `pos_discount_voucher`;

CREATE TABLE `pos_discount_voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `discount_id` int(11) NOT NULL,
  `voucher_no` char(50) NOT NULL,
  `voucher_status` tinyint(1) DEFAULT '0',
  `date_used` date DEFAULT NULL,
  `ref_billing_no` char(20) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_discount_voucher` */

/*Table structure for table `pos_divisi` */

DROP TABLE IF EXISTS `pos_divisi`;

CREATE TABLE `pos_divisi` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `divisi_name` varchar(200) NOT NULL,
  `divisi_desc` varchar(255) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Data for the table `pos_divisi` */

insert  into `pos_divisi`(`id`,`divisi_name`,`divisi_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Accounting','Accounting Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:01:24',1,0),(2,'Kitchen','Kitchen Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:01:42',1,0),(3,'Manager','Manager Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:00:56',1,0),(4,'Supervisor','Supervisor Department','administrator','2014-12-11 16:02:08','administrator','2014-12-11 16:02:08',1,0),(5,'Bar','Bar Department','administrator','2014-12-11 16:02:32','administrator','2014-12-11 16:02:32',1,0),(6,'Kasir','Kasir Department','administrator','2014-12-11 16:02:47','administrator','2014-12-11 16:02:47',1,0),(7,'Service','Service Department','administrator','2014-12-11 16:03:06','administrator','2014-12-11 16:03:06',1,0),(8,'Steward','Steward Department','administrator','2014-12-11 16:03:34','administrator','2014-12-11 16:03:34',1,0),(9,'Security','Security Department','administrator','2014-12-11 16:03:51','administrator','2014-12-11 16:03:51',1,0),(10,'Housekeeping','Housekeeping Department','administrator','2014-12-11 16:04:15','administrator','2014-12-11 16:04:15',1,0),(11,'Admin','Admin Department','administrator','2014-12-11 16:04:32','administrator','2014-12-11 16:04:32',1,0),(12,'Marketing','Marketing Department','administrator','2014-12-11 16:04:47','administrator','2014-12-11 16:04:47',1,0),(13,'Purcashing','Purcashing Department','administrator','2014-12-17 17:58:36','administrator','2014-12-17 17:58:36',1,0);

/*Table structure for table `pos_floorplan` */

DROP TABLE IF EXISTS `pos_floorplan`;

CREATE TABLE `pos_floorplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `floorplan_name` varchar(100) NOT NULL,
  `floorplan_desc` varchar(100) DEFAULT NULL,
  `floorplan_image` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `pos_floorplan` */

insert  into `pos_floorplan`(`id`,`floorplan_name`,`floorplan_desc`,`floorplan_image`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'PUBLIC','','','administrator','2016-03-29 22:48:20','administrator','2016-03-29 22:48:20',1,0),(2,'PRIVATE','','','administrator','2016-03-29 22:48:28','administrator','2016-03-29 22:48:28',1,0);

/*Table structure for table `pos_item_category` */

DROP TABLE IF EXISTS `pos_item_category`;

CREATE TABLE `pos_item_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_category_name` varchar(100) NOT NULL,
  `item_category_code` char(2) DEFAULT NULL,
  `item_category_desc` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_category_code` (`item_category_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `pos_item_category` */

insert  into `pos_item_category`(`id`,`item_category_name`,`item_category_code`,`item_category_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'sample cat item','A0','','administrator','2016-05-17 16:42:10','administrator','2017-09-01 15:30:30',1,0);

/*Table structure for table `pos_item_kode_unik` */

DROP TABLE IF EXISTS `pos_item_kode_unik`;

CREATE TABLE `pos_item_kode_unik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `kode_unik` varchar(255) NOT NULL,
  `ref_in` varchar(50) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `ref_out` varchar(50) DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `storehouse_id` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_item_kode_unik` */

/*Table structure for table `pos_item_subcategory` */

DROP TABLE IF EXISTS `pos_item_subcategory`;

CREATE TABLE `pos_item_subcategory` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `item_subcategory_name` varchar(100) NOT NULL,
  `item_subcategory_code` char(5) DEFAULT NULL,
  `item_subcategory_desc` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

/*Data for the table `pos_item_subcategory` */

insert  into `pos_item_subcategory`(`id`,`item_subcategory_name`,`item_subcategory_code`,`item_subcategory_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (19,'SUB CAT 1','SB1','','administrator','2017-09-01 16:56:03','administrator','2017-09-01 16:56:03',1,0);

/*Table structure for table `pos_items` */

DROP TABLE IF EXISTS `pos_items`;

CREATE TABLE `pos_items` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_code` varchar(100) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('main','support') DEFAULT 'main',
  `item_manufacturer` varchar(255) DEFAULT NULL,
  `item_desc` varchar(255) DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `item_price` double DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `item_hpp` double DEFAULT '0',
  `last_in` double DEFAULT '0',
  `old_last_in` double DEFAULT '0',
  `min_stock` int(11) DEFAULT '0',
  `total_qty_stok` float DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `sales_price` double DEFAULT '0',
  `use_for_sales` tinyint(1) DEFAULT '0',
  `id_ref_product` int(11) DEFAULT '0',
  `sales_use_tax` tinyint(1) DEFAULT '0',
  `sales_use_service` tinyint(1) DEFAULT '0',
  `is_kerjasama` tinyint(1) DEFAULT '0',
  `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
  `total_bagi_hasil` double DEFAULT '0',
  `subcategory_id` smallint(6) DEFAULT '0',
  `item_no` smallint(6) DEFAULT '0',
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_items` */

/*Table structure for table `pos_open_close_shift` */

DROP TABLE IF EXISTS `pos_open_close_shift`;

CREATE TABLE `pos_open_close_shift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kasir_user` varchar(50) NOT NULL,
  `spv_user` varchar(50) DEFAULT NULL,
  `tipe_shift` enum('open','close') DEFAULT 'open',
  `tanggal_shift` date NOT NULL,
  `jam_shift` varchar(5) DEFAULT '00:00',
  `user_shift` tinyint(1) DEFAULT '1',
  `uang_kertas_100000` smallint(6) DEFAULT '0',
  `uang_kertas_50000` smallint(6) DEFAULT '0',
  `uang_kertas_20000` smallint(6) DEFAULT '0',
  `uang_kertas_10000` smallint(6) DEFAULT '0',
  `uang_kertas_5000` smallint(6) DEFAULT '0',
  `uang_kertas_2000` smallint(6) DEFAULT '0',
  `uang_kertas_1000` smallint(6) NOT NULL DEFAULT '0',
  `uang_koin_1000` smallint(6) NOT NULL DEFAULT '0',
  `uang_koin_500` smallint(6) DEFAULT '0',
  `uang_koin_200` smallint(6) DEFAULT '0',
  `uang_koin_100` smallint(6) DEFAULT '0',
  `jumlah_uang_kertas` double DEFAULT '0',
  `jumlah_uang_koin` double DEFAULT '0',
  `is_validate` tinyint(1) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `pos_open_close_shift` */

insert  into `pos_open_close_shift`(`id`,`kasir_user`,`spv_user`,`tipe_shift`,`tanggal_shift`,`jam_shift`,`user_shift`,`uang_kertas_100000`,`uang_kertas_50000`,`uang_kertas_20000`,`uang_kertas_10000`,`uang_kertas_5000`,`uang_kertas_2000`,`uang_kertas_1000`,`uang_koin_1000`,`uang_koin_500`,`uang_koin_200`,`uang_koin_100`,`jumlah_uang_kertas`,`jumlah_uang_koin`,`is_validate`,`createdby`,`created`,`updatedby`,`updated`,`is_deleted`) values (1,'admin','admin','open','2016-09-09','13:04',1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'admin','2016-09-09 13:09:22','admin','2016-09-09 14:01:04',0),(2,'administrator','admin','open','2017-09-01','19:09',1,2,2,4,4,4,5,5,5,4,10,10,455000,10000,0,'administrator','2017-09-01 19:11:52','administrator','2017-09-01 19:11:52',0);

/*Table structure for table `pos_payment_type` */

DROP TABLE IF EXISTS `pos_payment_type`;

CREATE TABLE `pos_payment_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_type_name` varchar(100) NOT NULL,
  `payment_type_desc` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `pos_payment_type` */

insert  into `pos_payment_type`(`id`,`payment_type_name`,`payment_type_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Cash','Paid by Cash','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(2,'Debit Card','Paid by Debit Card','administrator','2014-06-28 03:32:50','administrator','0000-00-00 00:00:00',1,0),(3,'Credit Card','Paid by Credit Card','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0);

/*Table structure for table `pos_po` */

DROP TABLE IF EXISTS `pos_po`;

CREATE TABLE `pos_po` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(20) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_invoice` varchar(100) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_total_qty` float DEFAULT '0',
  `po_sub_total` double DEFAULT NULL,
  `po_discount` double DEFAULT NULL,
  `po_tax` double DEFAULT NULL,
  `po_shipping` double DEFAULT NULL,
  `po_total_price` double DEFAULT '0',
  `po_payment` enum('cash','credit') NOT NULL DEFAULT 'cash',
  `po_status` enum('progress','done','cancel') NOT NULL DEFAULT 'progress',
  `po_memo` tinytext,
  `ro_id` int(11) NOT NULL,
  `po_project` varchar(100) DEFAULT NULL,
  `po_ship_to` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `supplier_from_ro` tinyint(1) DEFAULT '1',
  `approval_status` enum('progress','done') DEFAULT NULL,
  `use_approval` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number_idx` (`po_number`),
  KEY `fk_po_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_po` */

/*Table structure for table `pos_po_detail` */

DROP TABLE IF EXISTS `pos_po_detail`;

CREATE TABLE `pos_po_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `po_detail_purchase` double DEFAULT NULL,
  `po_detail_qty` float DEFAULT NULL,
  `po_receive_qty` float DEFAULT '0',
  `unit_id` int(11) DEFAULT NULL,
  `po_detail_total` double DEFAULT NULL,
  `po_detail_status` enum('request','take','cancel') NOT NULL DEFAULT 'take',
  `ro_detail_id` bigint(20) DEFAULT NULL,
  `supplier_item_id` int(11) DEFAULT NULL,
  `po_detail_potongan` double DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_po_detail_po` (`po_id`),
  KEY `fk_po_detail_barang` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_po_detail` */

/*Table structure for table `pos_print_monitoring` */

DROP TABLE IF EXISTS `pos_print_monitoring`;

CREATE TABLE `pos_print_monitoring` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `tipe` varchar(10) NOT NULL,
  `peritem` tinyint(1) DEFAULT '0',
  `receiptTxt` text NOT NULL,
  `printer` varchar(100) DEFAULT NULL,
  `billing_no` varchar(20) DEFAULT NULL,
  `table_no` varchar(20) DEFAULT NULL,
  `user` varchar(50) DEFAULT NULL,
  `print_date` date DEFAULT NULL,
  `print_datetime` timestamp NULL DEFAULT NULL,
  `status_print` tinyint(1) DEFAULT '0',
  `tipe_printer` varchar(20) DEFAULT NULL,
  `tipe_pin` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_print_monitoring` */

/*Table structure for table `pos_printer` */

DROP TABLE IF EXISTS `pos_printer`;

CREATE TABLE `pos_printer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `printer_ip` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `printer_name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `printer_tipe` varchar(30) COLLATE latin1_general_ci DEFAULT NULL,
  `printer_pin` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `is_print_anywhere` tinyint(1) DEFAULT '0',
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_printer` */

insert  into `pos_printer`(`id`,`printer_ip`,`printer_name`,`printer_tipe`,`printer_pin`,`is_print_anywhere`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'PC-NAME\\PRINTER-SHARE-NAME','PRINTER NAME','EPSON','46 CHAR',0,'administrator','2017-09-01 18:59:11','administrator','2017-09-01 18:59:11',1,0);

/*Table structure for table `pos_product` */

DROP TABLE IF EXISTS `pos_product`;

CREATE TABLE `pos_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(200) NOT NULL,
  `product_desc` varchar(255) DEFAULT NULL,
  `product_price` double DEFAULT '0',
  `product_hpp` double DEFAULT '0',
  `product_image` varchar(100) DEFAULT NULL,
  `product_type` enum('item','package') DEFAULT 'item',
  `product_group` enum('food','beverage','other') DEFAULT 'food',
  `category_id` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `product_chinese_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `price_include_tax` tinyint(1) DEFAULT '0',
  `price_include_service` tinyint(1) DEFAULT '0',
  `discount_manual` tinyint(1) DEFAULT '1',
  `has_varian` smallint(6) DEFAULT '0',
  `normal_price` double DEFAULT '0',
  `use_tax` tinyint(1) DEFAULT '1',
  `use_service` tinyint(1) DEFAULT '1',
  `from_item` tinyint(1) DEFAULT '0',
  `id_ref_item` int(11) DEFAULT '0',
  `is_kerjasama` tinyint(1) DEFAULT '0',
  `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
  `total_bagi_hasil` double DEFAULT '0',
  `supplier_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `pos_product` */

insert  into `pos_product`(`id`,`product_name`,`product_desc`,`product_price`,`product_hpp`,`product_image`,`product_type`,`product_group`,`category_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`product_chinese_name`,`price_include_tax`,`price_include_service`,`discount_manual`,`has_varian`,`normal_price`,`use_tax`,`use_service`,`from_item`,`id_ref_item`,`is_kerjasama`,`persentase_bagi_hasil`,`total_bagi_hasil`,`supplier_id`) values (3,'Nasi Goreng Special',NULL,19800,7313,NULL,'item','food',1,'administrator','2016-03-24 09:11:25','administrator','2016-03-30 22:29:24',1,0,'0',0,0,1,0,18000,1,1,0,0,0,'0.00',0,0);

/*Table structure for table `pos_product_category` */

DROP TABLE IF EXISTS `pos_product_category`;

CREATE TABLE `pos_product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_category_name` varchar(100) NOT NULL,
  `product_category_desc` varchar(100) DEFAULT NULL,
  `parent_id` int(11) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

/*Data for the table `pos_product_category` */

insert  into `pos_product_category`(`id`,`product_category_name`,`product_category_desc`,`parent_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Rice & Noodle','',0,'administrator','2016-03-24 09:03:40','administrator','2016-03-24 09:03:40',1,0);

/*Table structure for table `pos_product_gramasi` */

DROP TABLE IF EXISTS `pos_product_gramasi`;

CREATE TABLE `pos_product_gramasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_price` double DEFAULT '0',
  `item_qty` float DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_product_gramasi` */

/*Table structure for table `pos_product_package` */

DROP TABLE IF EXISTS `pos_product_package`;

CREATE TABLE `pos_product_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_price` double DEFAULT NULL,
  `product_hpp` double DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_product_package` */

/*Table structure for table `pos_product_varian` */

DROP TABLE IF EXISTS `pos_product_varian`;

CREATE TABLE `pos_product_varian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `varian_id` int(11) DEFAULT NULL,
  `product_price` double DEFAULT NULL,
  `product_hpp` double DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `normal_price` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_product_varian` */

/*Table structure for table `pos_receive_detail` */

DROP TABLE IF EXISTS `pos_receive_detail`;

CREATE TABLE `pos_receive_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `receive_id` int(11) NOT NULL,
  `receive_det_date` date DEFAULT NULL,
  `receive_det_qty` float DEFAULT NULL,
  `receive_det_purchase` double DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `po_detail_qty` float DEFAULT NULL,
  `po_detail_id` int(11) DEFAULT NULL,
  `current_stock` float DEFAULT '0',
  `supplier_item_id` int(11) DEFAULT NULL,
  `storehouse_id` int(11) DEFAULT '0',
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  `data_stok_kode_unik` text,
  PRIMARY KEY (`id`),
  KEY `fk_receive_receive_detail` (`receive_id`),
  KEY `fk_barang_receive_detail` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_receive_detail` */

/*Table structure for table `pos_receiving` */

DROP TABLE IF EXISTS `pos_receiving`;

CREATE TABLE `pos_receiving` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receive_number` varchar(20) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `receive_date` date DEFAULT NULL,
  `receive_memo` tinytext,
  `total_qty` float DEFAULT '0',
  `total_price` double DEFAULT '0',
  `receive_status` enum('progress','done','cancel') NOT NULL DEFAULT 'progress',
  `po_id` int(11) NOT NULL,
  `receive_project` varchar(100) DEFAULT NULL,
  `receive_ship_to` varchar(100) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `storehouse_id` int(11) DEFAULT '0',
  `no_surat_jalan` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receiv_number_idx` (`receive_number`),
  KEY `fk_receiving_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_receiving` */

/*Table structure for table `pos_ro` */

DROP TABLE IF EXISTS `pos_ro`;

CREATE TABLE `pos_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ro_number` varchar(20) NOT NULL,
  `ro_date` date DEFAULT NULL,
  `ro_memo` tinytext,
  `ro_total_qty` float DEFAULT '0',
  `ro_status` enum('request','validated','take','cancel') NOT NULL DEFAULT 'request',
  `divisi_id` int(11) DEFAULT '0',
  `total_item` tinyint(4) DEFAULT '0',
  `total_validated` tinyint(4) DEFAULT '0',
  `total_request` tinyint(4) DEFAULT '0',
  `ro_from` varchar(100) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `take_reff_id` int(11) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ro_number_idx` (`ro_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_ro` */

/*Table structure for table `pos_ro_detail` */

DROP TABLE IF EXISTS `pos_ro_detail`;

CREATE TABLE `pos_ro_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ro_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `ro_detail_qty` float NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `ro_detail_status` enum('request','validated','take','cancel') NOT NULL DEFAULT 'request',
  `take_reff_detail_id` bigint(20) DEFAULT '0',
  `supplier_id` int(11) DEFAULT '0',
  `take_reff_id` int(11) DEFAULT '0',
  `item_price` double DEFAULT '0',
  `item_hpp` double DEFAULT '0',
  `supplier_item_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ro_detail_ro` (`ro_id`),
  KEY `fk_ro_detail_barang` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_ro_detail` */

/*Table structure for table `pos_sales` */

DROP TABLE IF EXISTS `pos_sales`;

CREATE TABLE `pos_sales` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `sales_name` char(100) NOT NULL,
  `sales_percentage` decimal(5,2) DEFAULT '0.00',
  `sales_price` double DEFAULT '0',
  `sales_contract_type` enum('unlimited_date','limited_date') DEFAULT 'unlimited_date',
  `sales_company` char(50) DEFAULT NULL,
  `sales_phone` char(20) DEFAULT NULL,
  `sales_address` char(100) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `createdby` char(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` char(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  `sales_type` enum('before_tax','after_tax') DEFAULT 'after_tax',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_sales` */

/*Table structure for table `pos_stock` */

DROP TABLE IF EXISTS `pos_stock`;

CREATE TABLE `pos_stock` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) NOT NULL,
  `trx_date` date NOT NULL,
  `trx_type` enum('in','out','sto') DEFAULT 'in',
  `trx_qty` float NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `trx_nominal` double NOT NULL DEFAULT '0',
  `trx_note` varchar(255) DEFAULT NULL,
  `trx_ref_data` varchar(100) NOT NULL,
  `trx_ref_det_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `storehouse_id` int(11) DEFAULT NULL,
  `is_sto` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock` */

/*Table structure for table `pos_stock_opname` */

DROP TABLE IF EXISTS `pos_stock_opname`;

CREATE TABLE `pos_stock_opname` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sto_number` varchar(255) NOT NULL,
  `sto_date` date NOT NULL,
  `sto_memo` varchar(255) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `storehouse_id` int(11) DEFAULT NULL,
  `sto_status` enum('progress','done','cancel') DEFAULT 'progress',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock_opname` */

/*Table structure for table `pos_stock_opname_detail` */

DROP TABLE IF EXISTS `pos_stock_opname_detail`;

CREATE TABLE `pos_stock_opname_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sto_id` int(11) DEFAULT NULL,
  `jumlah_awal` float DEFAULT NULL,
  `jumlah_fisik` float DEFAULT NULL,
  `selisih` float DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `total_hpp_fifo` double DEFAULT NULL,
  `current_hpp_avg` double DEFAULT '0',
  `total_hpp_avg` double DEFAULT '0',
  `stod_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_in` double DEFAULT '0',
  `total_last_in` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock_opname_detail` */

/*Table structure for table `pos_stock_opname_detail_upload` */

DROP TABLE IF EXISTS `pos_stock_opname_detail_upload`;

CREATE TABLE `pos_stock_opname_detail_upload` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sto_id` int(11) DEFAULT NULL,
  `jumlah_awal` float DEFAULT NULL,
  `jumlah_fisik` float DEFAULT NULL,
  `selisih` float DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `total_hpp_fifo` double DEFAULT NULL,
  `current_hpp_avg` double DEFAULT NULL,
  `total_hpp_avg` double DEFAULT NULL,
  `stod_status` tinyint(1) NOT NULL DEFAULT '0',
  `last_in` double DEFAULT '0',
  `total_last_in` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock_opname_detail_upload` */

/*Table structure for table `pos_stock_rekap` */

DROP TABLE IF EXISTS `pos_stock_rekap`;

CREATE TABLE `pos_stock_rekap` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `item_id` bigint(20) NOT NULL,
  `total_stock` float DEFAULT NULL,
  `total_stock_in` float DEFAULT NULL,
  `total_stock_out` float DEFAULT NULL,
  `trx_date` date NOT NULL,
  `storehouse_id` int(11) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `total_stock_kemarin` float DEFAULT NULL,
  `item_hpp` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock_rekap` */

/*Table structure for table `pos_stock_unit` */

DROP TABLE IF EXISTS `pos_stock_unit`;

CREATE TABLE `pos_stock_unit` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) DEFAULT NULL,
  `item_id` bigint(20) NOT NULL,
  `total_stock` int(11) DEFAULT NULL,
  `total_stock_in` int(11) DEFAULT NULL,
  `total_stock_out` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `pos_stock_unit` */

/*Table structure for table `pos_storehouse` */

DROP TABLE IF EXISTS `pos_storehouse`;

CREATE TABLE `pos_storehouse` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `storehouse_code` varchar(10) NOT NULL,
  `storehouse_name` varchar(200) NOT NULL,
  `storehouse_desc` varchar(255) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `gudang_code_idx` (`storehouse_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

/*Data for the table `pos_storehouse` */

insert  into `pos_storehouse`(`id`,`storehouse_code`,`storehouse_name`,`storehouse_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_primary`) values (1,'G1','Gudang 1','Gudang Gedung 1','administrator','0000-00-00 00:00:00','administrator','2017-09-01 15:31:09',1,0,1),(2,'G2','Gudang 2','Gudang Dapur','administrator','0000-00-00 00:00:00','siska','2014-11-01 11:05:12',1,0,0);

/*Table structure for table `pos_storehouse_users` */

DROP TABLE IF EXISTS `pos_storehouse_users`;

CREATE TABLE `pos_storehouse_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storehouse_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_retail_warehouse` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `pos_storehouse_users` */

insert  into `pos_storehouse_users`(`id`,`storehouse_id`,`user_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_retail_warehouse`) values (1,2,1,'administrator','2016-03-27 19:39:00','administrator','2016-03-27 19:40:09',1,0,0),(2,1,1,'administrator','2016-03-27 19:40:25','administrator','2017-09-01 19:05:40',1,0,1),(3,1,57,'administrator','2017-09-01 19:05:13','administrator','2017-09-01 19:05:47',1,0,1);

/*Table structure for table `pos_supplier` */

DROP TABLE IF EXISTS `pos_supplier`;

CREATE TABLE `pos_supplier` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_code` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `supplier_name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `supplier_contact_person` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `supplier_address` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `supplier_phone` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `supplier_fax` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `supplier_email` varchar(100) COLLATE latin1_general_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_supplier` */

insert  into `pos_supplier`(`id`,`supplier_code`,`supplier_name`,`supplier_contact_person`,`supplier_address`,`supplier_phone`,`supplier_fax`,`supplier_email`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'','Supplier Sample','','','123','123','','administrator','2016-05-17 16:41:26','administrator','2016-05-17 16:41:26',1,0);

/*Table structure for table `pos_supplier_item` */

DROP TABLE IF EXISTS `pos_supplier_item`;

CREATE TABLE `pos_supplier_item` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `item_price` double NOT NULL,
  `item_hpp` double NOT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_supplier_item` */

/*Table structure for table `pos_table` */

DROP TABLE IF EXISTS `pos_table`;

CREATE TABLE `pos_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `table_no` varchar(10) NOT NULL,
  `table_desc` varchar(100) DEFAULT NULL,
  `floorplan_id` int(11) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

/*Data for the table `pos_table` */

insert  into `pos_table`(`id`,`table_name`,`table_no`,`table_desc`,`floorplan_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Take Away 1','T/A 1','Take Away 1',1,'admin','2016-09-09 14:30:16','admin','2016-09-09 14:30:16',1,0),(2,'VIP 1','VIP 1','VIP',2,'admin','2016-09-09 14:30:33','admin','2016-09-09 14:30:33',1,0),(3,'S1','S1','Smoking',1,'admin','2016-09-09 14:30:58','admin','2016-09-09 14:30:58',1,0),(4,'NS1','NS1','Non Smoking',1,'admin','2016-09-09 14:31:14','admin','2016-09-09 14:31:14',1,0);

/*Table structure for table `pos_table_inventory` */

DROP TABLE IF EXISTS `pos_table_inventory`;

CREATE TABLE `pos_table_inventory` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `table_id` int(11) DEFAULT NULL,
  `billing_no` varchar(15) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `status` enum('available','booked','reserved','not available') DEFAULT 'available',
  `created` datetime DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1949 DEFAULT CHARSET=latin1;

/*Data for the table `pos_table_inventory` */

insert  into `pos_table_inventory`(`id`,`table_id`,`billing_no`,`tanggal`,`status`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) values (1,1,NULL,'2017-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(2,2,NULL,'2017-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(3,3,NULL,'2017-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(4,4,NULL,'2017-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(5,1,NULL,'2017-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(6,2,NULL,'2017-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(7,3,NULL,'2017-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(8,4,NULL,'2017-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(9,1,NULL,'2017-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(10,2,NULL,'2017-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(11,3,NULL,'2017-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(12,4,NULL,'2017-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(13,1,NULL,'2017-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(14,2,NULL,'2017-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(15,3,NULL,'2017-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(16,4,NULL,'2017-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(17,1,NULL,'2017-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(18,2,NULL,'2017-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(19,3,NULL,'2017-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(20,4,NULL,'2017-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(21,1,NULL,'2017-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(22,2,NULL,'2017-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(23,3,NULL,'2017-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(24,4,NULL,'2017-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(25,1,NULL,'2017-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(26,2,NULL,'2017-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(27,3,NULL,'2017-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(28,4,NULL,'2017-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(29,1,NULL,'2017-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(30,2,NULL,'2017-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(31,3,NULL,'2017-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(32,4,NULL,'2017-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(33,1,NULL,'2017-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(34,2,NULL,'2017-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(35,3,NULL,'2017-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(36,4,NULL,'2017-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(37,1,NULL,'2017-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(38,2,NULL,'2017-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(39,3,NULL,'2017-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(40,4,NULL,'2017-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(41,1,NULL,'2017-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(42,2,NULL,'2017-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(43,3,NULL,'2017-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(44,4,NULL,'2017-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(45,1,NULL,'2017-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(46,2,NULL,'2017-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(47,3,NULL,'2017-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(48,4,NULL,'2017-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(49,1,NULL,'2017-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(50,2,NULL,'2017-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(51,3,NULL,'2017-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(52,4,NULL,'2017-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(53,1,NULL,'2017-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(54,2,NULL,'2017-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(55,3,NULL,'2017-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(56,4,NULL,'2017-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(57,1,NULL,'2017-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(58,2,NULL,'2017-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(59,3,NULL,'2017-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(60,4,NULL,'2017-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(61,1,NULL,'2017-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(62,2,NULL,'2017-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(63,3,NULL,'2017-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(64,4,NULL,'2017-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(65,1,NULL,'2017-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(66,2,NULL,'2017-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(67,3,NULL,'2017-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(68,4,NULL,'2017-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(69,1,NULL,'2017-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(70,2,NULL,'2017-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(71,3,NULL,'2017-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(72,4,NULL,'2017-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(73,1,NULL,'2017-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(74,2,NULL,'2017-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(75,3,NULL,'2017-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(76,4,NULL,'2017-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(77,1,NULL,'2017-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(78,2,NULL,'2017-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(79,3,NULL,'2017-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(80,4,NULL,'2017-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(81,1,NULL,'2017-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(82,2,NULL,'2017-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(83,3,NULL,'2017-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(84,4,NULL,'2017-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(85,1,NULL,'2017-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(86,2,NULL,'2017-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(87,3,NULL,'2017-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(88,4,NULL,'2017-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(89,1,NULL,'2017-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(90,2,NULL,'2017-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(91,3,NULL,'2017-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(92,4,NULL,'2017-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(93,1,NULL,'2017-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(94,2,NULL,'2017-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(95,3,NULL,'2017-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(96,4,NULL,'2017-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(97,1,NULL,'2017-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(98,2,NULL,'2017-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(99,3,NULL,'2017-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(100,4,NULL,'2017-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(101,1,NULL,'2017-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(102,2,NULL,'2017-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(103,3,NULL,'2017-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(104,4,NULL,'2017-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(105,1,NULL,'2017-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(106,2,NULL,'2017-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(107,3,NULL,'2017-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(108,4,NULL,'2017-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(109,1,NULL,'2017-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(110,2,NULL,'2017-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(111,3,NULL,'2017-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(112,4,NULL,'2017-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(113,1,NULL,'2017-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(114,2,NULL,'2017-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(115,3,NULL,'2017-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(116,4,NULL,'2017-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(117,1,NULL,'2017-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(118,2,NULL,'2017-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(119,3,NULL,'2017-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(120,4,NULL,'2017-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(121,1,NULL,'2017-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(122,2,NULL,'2017-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(123,3,NULL,'2017-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(124,4,NULL,'2017-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(125,1,NULL,'2017-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(126,2,NULL,'2017-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(127,3,NULL,'2017-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(128,4,NULL,'2017-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(129,1,NULL,'2017-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(130,2,NULL,'2017-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(131,3,NULL,'2017-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(132,4,NULL,'2017-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(133,1,NULL,'2017-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(134,2,NULL,'2017-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(135,3,NULL,'2017-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(136,4,NULL,'2017-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(137,1,NULL,'2017-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(138,2,NULL,'2017-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(139,3,NULL,'2017-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(140,4,NULL,'2017-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(141,1,NULL,'2017-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(142,2,NULL,'2017-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(143,3,NULL,'2017-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(144,4,NULL,'2017-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(145,1,NULL,'2017-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(146,2,NULL,'2017-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(147,3,NULL,'2017-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(148,4,NULL,'2017-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(149,1,NULL,'2017-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(150,2,NULL,'2017-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(151,3,NULL,'2017-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(152,4,NULL,'2017-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(153,1,NULL,'2017-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(154,2,NULL,'2017-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(155,3,NULL,'2017-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(156,4,NULL,'2017-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(157,1,NULL,'2017-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(158,2,NULL,'2017-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(159,3,NULL,'2017-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(160,4,NULL,'2017-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(161,1,NULL,'2017-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(162,2,NULL,'2017-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(163,3,NULL,'2017-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(164,4,NULL,'2017-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(165,1,NULL,'2017-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(166,2,NULL,'2017-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(167,3,NULL,'2017-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(168,4,NULL,'2017-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(169,1,NULL,'2017-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(170,2,NULL,'2017-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(171,3,NULL,'2017-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(172,4,NULL,'2017-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(173,1,NULL,'2017-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(174,2,NULL,'2017-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(175,3,NULL,'2017-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(176,4,NULL,'2017-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(177,1,NULL,'2017-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(178,2,NULL,'2017-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(179,3,NULL,'2017-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(180,4,NULL,'2017-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(181,1,NULL,'2017-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(182,2,NULL,'2017-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(183,3,NULL,'2017-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(184,4,NULL,'2017-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(185,1,NULL,'2017-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(186,2,NULL,'2017-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(187,3,NULL,'2017-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(188,4,NULL,'2017-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(189,1,NULL,'2017-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(190,2,NULL,'2017-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(191,3,NULL,'2017-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(192,4,NULL,'2017-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(193,1,NULL,'2017-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(194,2,NULL,'2017-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(195,3,NULL,'2017-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(196,4,NULL,'2017-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(197,1,NULL,'2017-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(198,2,NULL,'2017-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(199,3,NULL,'2017-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(200,4,NULL,'2017-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(201,1,NULL,'2017-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(202,2,NULL,'2017-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(203,3,NULL,'2017-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(204,4,NULL,'2017-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(205,1,NULL,'2017-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(206,2,NULL,'2017-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(207,3,NULL,'2017-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(208,4,NULL,'2017-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(209,1,NULL,'2017-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(210,2,NULL,'2017-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(211,3,NULL,'2017-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(212,4,NULL,'2017-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(213,1,NULL,'2017-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(214,2,NULL,'2017-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(215,3,NULL,'2017-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(216,4,NULL,'2017-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(217,1,NULL,'2017-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(218,2,NULL,'2017-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(219,3,NULL,'2017-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(220,4,NULL,'2017-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(221,1,NULL,'2017-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(222,2,NULL,'2017-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(223,3,NULL,'2017-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(224,4,NULL,'2017-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(225,1,NULL,'2017-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(226,2,NULL,'2017-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(227,3,NULL,'2017-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(228,4,NULL,'2017-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(229,1,NULL,'2017-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(230,2,NULL,'2017-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(231,3,NULL,'2017-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(232,4,NULL,'2017-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(233,1,NULL,'2017-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(234,2,NULL,'2017-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(235,3,NULL,'2017-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(236,4,NULL,'2017-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(237,1,NULL,'2017-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(238,2,NULL,'2017-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(239,3,NULL,'2017-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(240,4,NULL,'2017-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(241,1,NULL,'2017-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(242,2,NULL,'2017-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(243,3,NULL,'2017-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(244,4,NULL,'2017-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(245,1,NULL,'2017-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(246,2,NULL,'2017-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(247,3,NULL,'2017-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(248,4,NULL,'2017-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(249,1,NULL,'2017-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(250,2,NULL,'2017-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(251,3,NULL,'2017-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(252,4,NULL,'2017-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(253,1,NULL,'2017-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(254,2,NULL,'2017-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(255,3,NULL,'2017-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(256,4,NULL,'2017-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(257,1,NULL,'2017-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(258,2,NULL,'2017-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(259,3,NULL,'2017-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(260,4,NULL,'2017-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(261,1,NULL,'2017-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(262,2,NULL,'2017-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(263,3,NULL,'2017-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(264,4,NULL,'2017-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(265,1,NULL,'2017-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(266,2,NULL,'2017-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(267,3,NULL,'2017-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(268,4,NULL,'2017-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(269,1,NULL,'2017-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(270,2,NULL,'2017-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(271,3,NULL,'2017-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(272,4,NULL,'2017-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(273,1,NULL,'2017-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(274,2,NULL,'2017-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(275,3,NULL,'2017-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(276,4,NULL,'2017-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(277,1,NULL,'2017-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(278,2,NULL,'2017-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(279,3,NULL,'2017-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(280,4,NULL,'2017-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(281,1,NULL,'2017-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(282,2,NULL,'2017-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(283,3,NULL,'2017-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(284,4,NULL,'2017-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(285,1,NULL,'2017-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(286,2,NULL,'2017-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(287,3,NULL,'2017-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(288,4,NULL,'2017-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(289,1,NULL,'2017-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(290,2,NULL,'2017-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(291,3,NULL,'2017-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(292,4,NULL,'2017-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(293,1,NULL,'2017-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(294,2,NULL,'2017-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(295,3,NULL,'2017-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(296,4,NULL,'2017-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(297,1,NULL,'2017-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(298,2,NULL,'2017-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(299,3,NULL,'2017-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(300,4,NULL,'2017-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(301,1,NULL,'2017-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(302,2,NULL,'2017-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(303,3,NULL,'2017-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(304,4,NULL,'2017-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(305,1,NULL,'2017-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(306,2,NULL,'2017-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(307,3,NULL,'2017-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(308,4,NULL,'2017-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(309,1,NULL,'2017-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(310,2,NULL,'2017-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(311,3,NULL,'2017-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(312,4,NULL,'2017-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(313,1,NULL,'2017-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(314,2,NULL,'2017-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(315,3,NULL,'2017-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(316,4,NULL,'2017-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(317,1,NULL,'2017-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(318,2,NULL,'2017-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(319,3,NULL,'2017-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(320,4,NULL,'2017-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(321,1,NULL,'2017-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(322,2,NULL,'2017-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(323,3,NULL,'2017-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(324,4,NULL,'2017-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(325,1,NULL,'2017-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(326,2,NULL,'2017-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(327,3,NULL,'2017-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(328,4,NULL,'2017-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(329,1,NULL,'2017-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(330,2,NULL,'2017-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(331,3,NULL,'2017-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(332,4,NULL,'2017-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(333,1,NULL,'2017-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(334,2,NULL,'2017-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(335,3,NULL,'2017-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(336,4,NULL,'2017-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(337,1,NULL,'2017-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(338,2,NULL,'2017-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(339,3,NULL,'2017-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(340,4,NULL,'2017-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(341,1,NULL,'2017-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(342,2,NULL,'2017-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(343,3,NULL,'2017-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(344,4,NULL,'2017-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(345,1,NULL,'2017-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(346,2,NULL,'2017-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(347,3,NULL,'2017-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(348,4,NULL,'2017-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(349,1,NULL,'2017-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(350,2,NULL,'2017-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(351,3,NULL,'2017-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(352,4,NULL,'2017-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(353,1,NULL,'2017-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(354,2,NULL,'2017-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(355,3,NULL,'2017-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(356,4,NULL,'2017-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(357,1,NULL,'2017-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(358,2,NULL,'2017-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(359,3,NULL,'2017-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(360,4,NULL,'2017-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(361,1,NULL,'2017-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(362,2,NULL,'2017-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(363,3,NULL,'2017-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(364,4,NULL,'2017-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(365,1,NULL,'2017-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(366,2,NULL,'2017-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(367,3,NULL,'2017-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(368,4,NULL,'2017-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(369,1,NULL,'2017-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(370,2,NULL,'2017-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(371,3,NULL,'2017-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(372,4,NULL,'2017-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(373,1,NULL,'2017-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(374,2,NULL,'2017-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(375,3,NULL,'2017-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(376,4,NULL,'2017-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(377,1,NULL,'2017-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(378,2,NULL,'2017-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(379,3,NULL,'2017-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(380,4,NULL,'2017-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(381,1,NULL,'2017-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(382,2,NULL,'2017-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(383,3,NULL,'2017-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(384,4,NULL,'2017-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(385,1,NULL,'2017-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(386,2,NULL,'2017-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(387,3,NULL,'2017-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(388,4,NULL,'2017-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(389,1,NULL,'2017-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(390,2,NULL,'2017-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(391,3,NULL,'2017-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(392,4,NULL,'2017-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(393,1,NULL,'2017-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(394,2,NULL,'2017-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(395,3,NULL,'2017-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(396,4,NULL,'2017-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(397,1,NULL,'2017-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(398,2,NULL,'2017-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(399,3,NULL,'2017-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(400,4,NULL,'2017-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(401,1,NULL,'2017-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(402,2,NULL,'2017-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(403,3,NULL,'2017-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(404,4,NULL,'2017-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(405,1,NULL,'2017-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(406,2,NULL,'2017-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(407,3,NULL,'2017-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(408,4,NULL,'2017-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(409,1,NULL,'2017-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(410,2,NULL,'2017-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(411,3,NULL,'2017-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(412,4,NULL,'2017-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(413,1,NULL,'2017-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(414,2,NULL,'2017-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(415,3,NULL,'2017-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(416,4,NULL,'2017-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(417,1,NULL,'2017-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(418,2,NULL,'2017-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(419,3,NULL,'2017-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(420,4,NULL,'2017-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(421,1,NULL,'2017-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(422,2,NULL,'2017-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(423,3,NULL,'2017-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(424,4,NULL,'2017-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(425,1,NULL,'2017-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(426,2,NULL,'2017-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(427,3,NULL,'2017-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(428,4,NULL,'2017-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(429,1,NULL,'2017-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(430,2,NULL,'2017-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(431,3,NULL,'2017-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(432,4,NULL,'2017-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(433,1,NULL,'2017-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(434,2,NULL,'2017-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(435,3,NULL,'2017-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(436,4,NULL,'2017-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(437,1,NULL,'2017-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(438,2,NULL,'2017-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(439,3,NULL,'2017-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(440,4,NULL,'2017-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(441,1,NULL,'2017-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(442,2,NULL,'2017-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(443,3,NULL,'2017-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(444,4,NULL,'2017-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(445,1,NULL,'2017-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(446,2,NULL,'2017-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(447,3,NULL,'2017-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(448,4,NULL,'2017-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(449,1,NULL,'2017-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(450,2,NULL,'2017-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(451,3,NULL,'2017-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(452,4,NULL,'2017-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(453,1,NULL,'2017-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(454,2,NULL,'2017-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(455,3,NULL,'2017-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(456,4,NULL,'2017-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(457,1,NULL,'2017-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(458,2,NULL,'2017-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(459,3,NULL,'2017-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(460,4,NULL,'2017-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(461,1,NULL,'2017-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(462,2,NULL,'2017-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(463,3,NULL,'2017-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(464,4,NULL,'2017-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(465,1,NULL,'2017-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(466,2,NULL,'2017-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(467,3,NULL,'2017-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(468,4,NULL,'2017-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(469,1,NULL,'2017-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(470,2,NULL,'2017-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(471,3,NULL,'2017-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(472,4,NULL,'2017-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(473,1,NULL,'2017-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(474,2,NULL,'2017-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(475,3,NULL,'2017-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(476,4,NULL,'2017-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(477,1,NULL,'2017-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(478,2,NULL,'2017-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(479,3,NULL,'2017-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(480,4,NULL,'2017-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(481,1,NULL,'2017-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(482,2,NULL,'2017-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(483,3,NULL,'2017-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(484,4,NULL,'2017-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(485,1,NULL,'2017-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(486,2,NULL,'2017-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(487,3,NULL,'2017-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(488,4,NULL,'2017-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(489,1,NULL,'2018-01-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(490,2,NULL,'2018-01-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(491,3,NULL,'2018-01-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(492,4,NULL,'2018-01-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(493,1,NULL,'2018-01-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(494,2,NULL,'2018-01-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(495,3,NULL,'2018-01-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(496,4,NULL,'2018-01-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(497,1,NULL,'2018-01-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(498,2,NULL,'2018-01-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(499,3,NULL,'2018-01-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(500,4,NULL,'2018-01-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(501,1,NULL,'2018-01-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(502,2,NULL,'2018-01-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(503,3,NULL,'2018-01-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(504,4,NULL,'2018-01-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(505,1,NULL,'2018-01-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(506,2,NULL,'2018-01-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(507,3,NULL,'2018-01-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(508,4,NULL,'2018-01-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(509,1,NULL,'2018-01-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(510,2,NULL,'2018-01-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(511,3,NULL,'2018-01-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(512,4,NULL,'2018-01-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(513,1,NULL,'2018-01-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(514,2,NULL,'2018-01-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(515,3,NULL,'2018-01-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(516,4,NULL,'2018-01-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(517,1,NULL,'2018-01-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(518,2,NULL,'2018-01-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(519,3,NULL,'2018-01-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(520,4,NULL,'2018-01-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(521,1,NULL,'2018-01-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(522,2,NULL,'2018-01-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(523,3,NULL,'2018-01-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(524,4,NULL,'2018-01-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(525,1,NULL,'2018-01-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(526,2,NULL,'2018-01-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(527,3,NULL,'2018-01-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(528,4,NULL,'2018-01-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(529,1,NULL,'2018-01-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(530,2,NULL,'2018-01-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(531,3,NULL,'2018-01-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(532,4,NULL,'2018-01-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(533,1,NULL,'2018-01-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(534,2,NULL,'2018-01-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(535,3,NULL,'2018-01-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(536,4,NULL,'2018-01-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(537,1,NULL,'2018-01-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(538,2,NULL,'2018-01-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(539,3,NULL,'2018-01-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(540,4,NULL,'2018-01-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(541,1,NULL,'2018-01-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(542,2,NULL,'2018-01-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(543,3,NULL,'2018-01-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(544,4,NULL,'2018-01-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(545,1,NULL,'2018-01-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(546,2,NULL,'2018-01-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(547,3,NULL,'2018-01-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(548,4,NULL,'2018-01-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(549,1,NULL,'2018-01-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(550,2,NULL,'2018-01-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(551,3,NULL,'2018-01-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(552,4,NULL,'2018-01-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(553,1,NULL,'2018-01-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(554,2,NULL,'2018-01-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(555,3,NULL,'2018-01-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(556,4,NULL,'2018-01-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(557,1,NULL,'2018-01-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(558,2,NULL,'2018-01-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(559,3,NULL,'2018-01-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(560,4,NULL,'2018-01-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(561,1,NULL,'2018-01-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(562,2,NULL,'2018-01-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(563,3,NULL,'2018-01-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(564,4,NULL,'2018-01-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(565,1,NULL,'2018-01-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(566,2,NULL,'2018-01-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(567,3,NULL,'2018-01-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(568,4,NULL,'2018-01-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(569,1,NULL,'2018-01-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(570,2,NULL,'2018-01-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(571,3,NULL,'2018-01-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(572,4,NULL,'2018-01-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(573,1,NULL,'2018-01-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(574,2,NULL,'2018-01-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(575,3,NULL,'2018-01-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(576,4,NULL,'2018-01-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(577,1,NULL,'2018-01-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(578,2,NULL,'2018-01-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(579,3,NULL,'2018-01-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(580,4,NULL,'2018-01-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(581,1,NULL,'2018-01-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(582,2,NULL,'2018-01-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(583,3,NULL,'2018-01-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(584,4,NULL,'2018-01-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(585,1,NULL,'2018-01-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(586,2,NULL,'2018-01-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(587,3,NULL,'2018-01-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(588,4,NULL,'2018-01-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(589,1,NULL,'2018-01-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(590,2,NULL,'2018-01-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(591,3,NULL,'2018-01-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(592,4,NULL,'2018-01-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(593,1,NULL,'2018-01-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(594,2,NULL,'2018-01-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(595,3,NULL,'2018-01-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(596,4,NULL,'2018-01-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(597,1,NULL,'2018-01-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(598,2,NULL,'2018-01-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(599,3,NULL,'2018-01-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(600,4,NULL,'2018-01-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(601,1,NULL,'2018-01-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(602,2,NULL,'2018-01-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(603,3,NULL,'2018-01-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(604,4,NULL,'2018-01-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(605,1,NULL,'2018-01-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(606,2,NULL,'2018-01-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(607,3,NULL,'2018-01-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(608,4,NULL,'2018-01-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(609,1,NULL,'2018-01-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(610,2,NULL,'2018-01-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(611,3,NULL,'2018-01-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(612,4,NULL,'2018-01-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(613,1,NULL,'2018-02-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(614,2,NULL,'2018-02-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(615,3,NULL,'2018-02-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(616,4,NULL,'2018-02-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(617,1,NULL,'2018-02-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(618,2,NULL,'2018-02-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(619,3,NULL,'2018-02-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(620,4,NULL,'2018-02-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(621,1,NULL,'2018-02-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(622,2,NULL,'2018-02-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(623,3,NULL,'2018-02-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(624,4,NULL,'2018-02-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(625,1,NULL,'2018-02-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(626,2,NULL,'2018-02-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(627,3,NULL,'2018-02-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(628,4,NULL,'2018-02-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(629,1,NULL,'2018-02-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(630,2,NULL,'2018-02-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(631,3,NULL,'2018-02-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(632,4,NULL,'2018-02-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(633,1,NULL,'2018-02-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(634,2,NULL,'2018-02-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(635,3,NULL,'2018-02-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(636,4,NULL,'2018-02-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(637,1,NULL,'2018-02-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(638,2,NULL,'2018-02-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(639,3,NULL,'2018-02-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(640,4,NULL,'2018-02-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(641,1,NULL,'2018-02-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(642,2,NULL,'2018-02-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(643,3,NULL,'2018-02-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(644,4,NULL,'2018-02-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(645,1,NULL,'2018-02-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(646,2,NULL,'2018-02-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(647,3,NULL,'2018-02-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(648,4,NULL,'2018-02-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(649,1,NULL,'2018-02-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(650,2,NULL,'2018-02-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(651,3,NULL,'2018-02-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(652,4,NULL,'2018-02-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(653,1,NULL,'2018-02-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(654,2,NULL,'2018-02-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(655,3,NULL,'2018-02-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(656,4,NULL,'2018-02-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(657,1,NULL,'2018-02-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(658,2,NULL,'2018-02-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(659,3,NULL,'2018-02-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(660,4,NULL,'2018-02-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(661,1,NULL,'2018-02-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(662,2,NULL,'2018-02-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(663,3,NULL,'2018-02-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(664,4,NULL,'2018-02-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(665,1,NULL,'2018-02-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(666,2,NULL,'2018-02-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(667,3,NULL,'2018-02-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(668,4,NULL,'2018-02-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(669,1,NULL,'2018-02-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(670,2,NULL,'2018-02-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(671,3,NULL,'2018-02-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(672,4,NULL,'2018-02-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(673,1,NULL,'2018-02-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(674,2,NULL,'2018-02-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(675,3,NULL,'2018-02-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(676,4,NULL,'2018-02-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(677,1,NULL,'2018-02-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(678,2,NULL,'2018-02-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(679,3,NULL,'2018-02-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(680,4,NULL,'2018-02-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(681,1,NULL,'2018-02-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(682,2,NULL,'2018-02-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(683,3,NULL,'2018-02-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(684,4,NULL,'2018-02-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(685,1,NULL,'2018-02-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(686,2,NULL,'2018-02-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(687,3,NULL,'2018-02-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(688,4,NULL,'2018-02-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(689,1,NULL,'2018-02-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(690,2,NULL,'2018-02-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(691,3,NULL,'2018-02-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(692,4,NULL,'2018-02-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(693,1,NULL,'2018-02-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(694,2,NULL,'2018-02-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(695,3,NULL,'2018-02-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(696,4,NULL,'2018-02-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(697,1,NULL,'2018-02-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(698,2,NULL,'2018-02-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(699,3,NULL,'2018-02-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(700,4,NULL,'2018-02-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(701,1,NULL,'2018-02-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(702,2,NULL,'2018-02-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(703,3,NULL,'2018-02-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(704,4,NULL,'2018-02-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(705,1,NULL,'2018-02-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(706,2,NULL,'2018-02-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(707,3,NULL,'2018-02-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(708,4,NULL,'2018-02-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(709,1,NULL,'2018-02-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(710,2,NULL,'2018-02-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(711,3,NULL,'2018-02-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(712,4,NULL,'2018-02-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(713,1,NULL,'2018-02-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(714,2,NULL,'2018-02-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(715,3,NULL,'2018-02-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(716,4,NULL,'2018-02-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(717,1,NULL,'2018-02-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(718,2,NULL,'2018-02-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(719,3,NULL,'2018-02-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(720,4,NULL,'2018-02-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(721,1,NULL,'2018-02-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(722,2,NULL,'2018-02-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(723,3,NULL,'2018-02-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(724,4,NULL,'2018-02-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(725,1,NULL,'2018-03-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(726,2,NULL,'2018-03-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(727,3,NULL,'2018-03-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(728,4,NULL,'2018-03-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(729,1,NULL,'2018-03-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(730,2,NULL,'2018-03-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(731,3,NULL,'2018-03-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(732,4,NULL,'2018-03-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(733,1,NULL,'2018-03-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(734,2,NULL,'2018-03-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(735,3,NULL,'2018-03-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(736,4,NULL,'2018-03-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(737,1,NULL,'2018-03-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(738,2,NULL,'2018-03-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(739,3,NULL,'2018-03-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(740,4,NULL,'2018-03-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(741,1,NULL,'2018-03-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(742,2,NULL,'2018-03-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(743,3,NULL,'2018-03-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(744,4,NULL,'2018-03-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(745,1,NULL,'2018-03-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(746,2,NULL,'2018-03-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(747,3,NULL,'2018-03-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(748,4,NULL,'2018-03-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(749,1,NULL,'2018-03-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(750,2,NULL,'2018-03-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(751,3,NULL,'2018-03-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(752,4,NULL,'2018-03-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(753,1,NULL,'2018-03-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(754,2,NULL,'2018-03-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(755,3,NULL,'2018-03-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(756,4,NULL,'2018-03-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(757,1,NULL,'2018-03-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(758,2,NULL,'2018-03-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(759,3,NULL,'2018-03-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(760,4,NULL,'2018-03-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(761,1,NULL,'2018-03-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(762,2,NULL,'2018-03-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(763,3,NULL,'2018-03-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(764,4,NULL,'2018-03-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(765,1,NULL,'2018-03-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(766,2,NULL,'2018-03-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(767,3,NULL,'2018-03-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(768,4,NULL,'2018-03-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(769,1,NULL,'2018-03-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(770,2,NULL,'2018-03-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(771,3,NULL,'2018-03-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(772,4,NULL,'2018-03-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(773,1,NULL,'2018-03-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(774,2,NULL,'2018-03-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(775,3,NULL,'2018-03-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(776,4,NULL,'2018-03-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(777,1,NULL,'2018-03-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(778,2,NULL,'2018-03-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(779,3,NULL,'2018-03-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(780,4,NULL,'2018-03-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(781,1,NULL,'2018-03-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(782,2,NULL,'2018-03-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(783,3,NULL,'2018-03-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(784,4,NULL,'2018-03-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(785,1,NULL,'2018-03-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(786,2,NULL,'2018-03-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(787,3,NULL,'2018-03-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(788,4,NULL,'2018-03-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(789,1,NULL,'2018-03-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(790,2,NULL,'2018-03-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(791,3,NULL,'2018-03-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(792,4,NULL,'2018-03-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(793,1,NULL,'2018-03-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(794,2,NULL,'2018-03-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(795,3,NULL,'2018-03-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(796,4,NULL,'2018-03-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(797,1,NULL,'2018-03-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(798,2,NULL,'2018-03-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(799,3,NULL,'2018-03-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(800,4,NULL,'2018-03-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(801,1,NULL,'2018-03-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(802,2,NULL,'2018-03-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(803,3,NULL,'2018-03-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(804,4,NULL,'2018-03-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(805,1,NULL,'2018-03-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(806,2,NULL,'2018-03-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(807,3,NULL,'2018-03-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(808,4,NULL,'2018-03-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(809,1,NULL,'2018-03-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(810,2,NULL,'2018-03-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(811,3,NULL,'2018-03-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(812,4,NULL,'2018-03-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(813,1,NULL,'2018-03-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(814,2,NULL,'2018-03-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(815,3,NULL,'2018-03-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(816,4,NULL,'2018-03-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(817,1,NULL,'2018-03-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(818,2,NULL,'2018-03-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(819,3,NULL,'2018-03-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(820,4,NULL,'2018-03-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(821,1,NULL,'2018-03-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(822,2,NULL,'2018-03-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(823,3,NULL,'2018-03-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(824,4,NULL,'2018-03-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(825,1,NULL,'2018-03-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(826,2,NULL,'2018-03-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(827,3,NULL,'2018-03-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(828,4,NULL,'2018-03-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(829,1,NULL,'2018-03-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(830,2,NULL,'2018-03-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(831,3,NULL,'2018-03-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(832,4,NULL,'2018-03-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(833,1,NULL,'2018-03-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(834,2,NULL,'2018-03-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(835,3,NULL,'2018-03-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(836,4,NULL,'2018-03-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(837,1,NULL,'2018-03-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(838,2,NULL,'2018-03-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(839,3,NULL,'2018-03-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(840,4,NULL,'2018-03-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(841,1,NULL,'2018-03-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(842,2,NULL,'2018-03-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(843,3,NULL,'2018-03-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(844,4,NULL,'2018-03-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(845,1,NULL,'2018-03-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(846,2,NULL,'2018-03-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(847,3,NULL,'2018-03-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(848,4,NULL,'2018-03-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(849,1,NULL,'2018-04-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(850,2,NULL,'2018-04-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(851,3,NULL,'2018-04-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(852,4,NULL,'2018-04-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(853,1,NULL,'2018-04-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(854,2,NULL,'2018-04-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(855,3,NULL,'2018-04-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(856,4,NULL,'2018-04-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(857,1,NULL,'2018-04-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(858,2,NULL,'2018-04-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(859,3,NULL,'2018-04-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(860,4,NULL,'2018-04-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(861,1,NULL,'2018-04-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(862,2,NULL,'2018-04-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(863,3,NULL,'2018-04-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(864,4,NULL,'2018-04-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(865,1,NULL,'2018-04-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(866,2,NULL,'2018-04-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(867,3,NULL,'2018-04-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(868,4,NULL,'2018-04-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(869,1,NULL,'2018-04-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(870,2,NULL,'2018-04-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(871,3,NULL,'2018-04-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(872,4,NULL,'2018-04-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(873,1,NULL,'2018-04-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(874,2,NULL,'2018-04-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(875,3,NULL,'2018-04-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(876,4,NULL,'2018-04-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(877,1,NULL,'2018-04-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(878,2,NULL,'2018-04-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(879,3,NULL,'2018-04-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(880,4,NULL,'2018-04-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(881,1,NULL,'2018-04-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(882,2,NULL,'2018-04-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(883,3,NULL,'2018-04-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(884,4,NULL,'2018-04-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(885,1,NULL,'2018-04-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(886,2,NULL,'2018-04-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(887,3,NULL,'2018-04-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(888,4,NULL,'2018-04-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(889,1,NULL,'2018-04-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(890,2,NULL,'2018-04-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(891,3,NULL,'2018-04-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(892,4,NULL,'2018-04-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(893,1,NULL,'2018-04-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(894,2,NULL,'2018-04-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(895,3,NULL,'2018-04-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(896,4,NULL,'2018-04-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(897,1,NULL,'2018-04-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(898,2,NULL,'2018-04-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(899,3,NULL,'2018-04-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(900,4,NULL,'2018-04-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(901,1,NULL,'2018-04-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(902,2,NULL,'2018-04-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(903,3,NULL,'2018-04-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(904,4,NULL,'2018-04-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(905,1,NULL,'2018-04-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(906,2,NULL,'2018-04-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(907,3,NULL,'2018-04-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(908,4,NULL,'2018-04-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(909,1,NULL,'2018-04-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(910,2,NULL,'2018-04-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(911,3,NULL,'2018-04-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(912,4,NULL,'2018-04-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(913,1,NULL,'2018-04-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(914,2,NULL,'2018-04-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(915,3,NULL,'2018-04-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(916,4,NULL,'2018-04-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(917,1,NULL,'2018-04-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(918,2,NULL,'2018-04-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(919,3,NULL,'2018-04-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(920,4,NULL,'2018-04-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(921,1,NULL,'2018-04-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(922,2,NULL,'2018-04-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(923,3,NULL,'2018-04-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(924,4,NULL,'2018-04-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(925,1,NULL,'2018-04-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(926,2,NULL,'2018-04-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(927,3,NULL,'2018-04-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(928,4,NULL,'2018-04-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(929,1,NULL,'2018-04-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(930,2,NULL,'2018-04-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(931,3,NULL,'2018-04-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(932,4,NULL,'2018-04-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(933,1,NULL,'2018-04-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(934,2,NULL,'2018-04-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(935,3,NULL,'2018-04-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(936,4,NULL,'2018-04-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(937,1,NULL,'2018-04-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(938,2,NULL,'2018-04-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(939,3,NULL,'2018-04-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(940,4,NULL,'2018-04-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(941,1,NULL,'2018-04-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(942,2,NULL,'2018-04-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(943,3,NULL,'2018-04-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(944,4,NULL,'2018-04-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(945,1,NULL,'2018-04-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(946,2,NULL,'2018-04-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(947,3,NULL,'2018-04-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(948,4,NULL,'2018-04-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(949,1,NULL,'2018-04-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(950,2,NULL,'2018-04-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(951,3,NULL,'2018-04-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(952,4,NULL,'2018-04-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(953,1,NULL,'2018-04-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(954,2,NULL,'2018-04-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(955,3,NULL,'2018-04-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(956,4,NULL,'2018-04-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(957,1,NULL,'2018-04-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(958,2,NULL,'2018-04-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(959,3,NULL,'2018-04-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(960,4,NULL,'2018-04-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(961,1,NULL,'2018-04-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(962,2,NULL,'2018-04-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(963,3,NULL,'2018-04-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(964,4,NULL,'2018-04-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(965,1,NULL,'2018-04-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(966,2,NULL,'2018-04-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(967,3,NULL,'2018-04-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(968,4,NULL,'2018-04-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(969,1,NULL,'2018-05-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(970,2,NULL,'2018-05-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(971,3,NULL,'2018-05-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(972,4,NULL,'2018-05-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(973,1,NULL,'2018-05-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(974,2,NULL,'2018-05-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(975,3,NULL,'2018-05-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(976,4,NULL,'2018-05-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(977,1,NULL,'2018-05-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(978,2,NULL,'2018-05-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(979,3,NULL,'2018-05-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(980,4,NULL,'2018-05-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(981,1,NULL,'2018-05-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(982,2,NULL,'2018-05-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(983,3,NULL,'2018-05-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(984,4,NULL,'2018-05-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(985,1,NULL,'2018-05-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(986,2,NULL,'2018-05-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(987,3,NULL,'2018-05-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(988,4,NULL,'2018-05-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(989,1,NULL,'2018-05-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(990,2,NULL,'2018-05-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(991,3,NULL,'2018-05-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(992,4,NULL,'2018-05-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(993,1,NULL,'2018-05-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(994,2,NULL,'2018-05-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(995,3,NULL,'2018-05-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(996,4,NULL,'2018-05-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(997,1,NULL,'2018-05-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(998,2,NULL,'2018-05-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(999,3,NULL,'2018-05-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1000,4,NULL,'2018-05-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1001,1,NULL,'2018-05-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1002,2,NULL,'2018-05-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1003,3,NULL,'2018-05-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1004,4,NULL,'2018-05-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1005,1,NULL,'2018-05-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1006,2,NULL,'2018-05-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1007,3,NULL,'2018-05-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1008,4,NULL,'2018-05-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1009,1,NULL,'2018-05-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1010,2,NULL,'2018-05-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1011,3,NULL,'2018-05-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1012,4,NULL,'2018-05-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1013,1,NULL,'2018-05-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1014,2,NULL,'2018-05-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1015,3,NULL,'2018-05-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1016,4,NULL,'2018-05-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1017,1,NULL,'2018-05-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1018,2,NULL,'2018-05-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1019,3,NULL,'2018-05-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1020,4,NULL,'2018-05-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1021,1,NULL,'2018-05-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1022,2,NULL,'2018-05-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1023,3,NULL,'2018-05-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1024,4,NULL,'2018-05-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1025,1,NULL,'2018-05-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1026,2,NULL,'2018-05-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1027,3,NULL,'2018-05-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1028,4,NULL,'2018-05-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1029,1,NULL,'2018-05-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1030,2,NULL,'2018-05-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1031,3,NULL,'2018-05-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1032,4,NULL,'2018-05-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1033,1,NULL,'2018-05-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1034,2,NULL,'2018-05-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1035,3,NULL,'2018-05-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1036,4,NULL,'2018-05-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1037,1,NULL,'2018-05-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1038,2,NULL,'2018-05-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1039,3,NULL,'2018-05-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1040,4,NULL,'2018-05-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1041,1,NULL,'2018-05-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1042,2,NULL,'2018-05-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1043,3,NULL,'2018-05-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1044,4,NULL,'2018-05-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1045,1,NULL,'2018-05-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1046,2,NULL,'2018-05-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1047,3,NULL,'2018-05-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1048,4,NULL,'2018-05-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1049,1,NULL,'2018-05-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1050,2,NULL,'2018-05-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1051,3,NULL,'2018-05-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1052,4,NULL,'2018-05-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1053,1,NULL,'2018-05-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1054,2,NULL,'2018-05-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1055,3,NULL,'2018-05-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1056,4,NULL,'2018-05-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1057,1,NULL,'2018-05-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1058,2,NULL,'2018-05-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1059,3,NULL,'2018-05-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1060,4,NULL,'2018-05-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1061,1,NULL,'2018-05-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1062,2,NULL,'2018-05-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1063,3,NULL,'2018-05-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1064,4,NULL,'2018-05-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1065,1,NULL,'2018-05-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1066,2,NULL,'2018-05-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1067,3,NULL,'2018-05-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1068,4,NULL,'2018-05-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1069,1,NULL,'2018-05-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1070,2,NULL,'2018-05-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1071,3,NULL,'2018-05-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1072,4,NULL,'2018-05-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1073,1,NULL,'2018-05-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1074,2,NULL,'2018-05-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1075,3,NULL,'2018-05-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1076,4,NULL,'2018-05-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1077,1,NULL,'2018-05-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1078,2,NULL,'2018-05-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1079,3,NULL,'2018-05-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1080,4,NULL,'2018-05-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1081,1,NULL,'2018-05-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1082,2,NULL,'2018-05-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1083,3,NULL,'2018-05-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1084,4,NULL,'2018-05-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1085,1,NULL,'2018-05-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1086,2,NULL,'2018-05-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1087,3,NULL,'2018-05-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1088,4,NULL,'2018-05-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1089,1,NULL,'2018-05-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1090,2,NULL,'2018-05-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1091,3,NULL,'2018-05-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1092,4,NULL,'2018-05-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1093,1,NULL,'2018-06-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1094,2,NULL,'2018-06-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1095,3,NULL,'2018-06-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1096,4,NULL,'2018-06-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1097,1,NULL,'2018-06-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1098,2,NULL,'2018-06-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1099,3,NULL,'2018-06-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1100,4,NULL,'2018-06-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1101,1,NULL,'2018-06-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1102,2,NULL,'2018-06-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1103,3,NULL,'2018-06-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1104,4,NULL,'2018-06-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1105,1,NULL,'2018-06-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1106,2,NULL,'2018-06-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1107,3,NULL,'2018-06-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1108,4,NULL,'2018-06-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1109,1,NULL,'2018-06-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1110,2,NULL,'2018-06-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1111,3,NULL,'2018-06-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1112,4,NULL,'2018-06-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1113,1,NULL,'2018-06-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1114,2,NULL,'2018-06-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1115,3,NULL,'2018-06-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1116,4,NULL,'2018-06-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1117,1,NULL,'2018-06-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1118,2,NULL,'2018-06-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1119,3,NULL,'2018-06-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1120,4,NULL,'2018-06-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1121,1,NULL,'2018-06-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1122,2,NULL,'2018-06-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1123,3,NULL,'2018-06-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1124,4,NULL,'2018-06-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1125,1,NULL,'2018-06-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1126,2,NULL,'2018-06-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1127,3,NULL,'2018-06-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1128,4,NULL,'2018-06-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1129,1,NULL,'2018-06-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1130,2,NULL,'2018-06-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1131,3,NULL,'2018-06-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1132,4,NULL,'2018-06-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1133,1,NULL,'2018-06-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1134,2,NULL,'2018-06-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1135,3,NULL,'2018-06-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1136,4,NULL,'2018-06-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1137,1,NULL,'2018-06-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1138,2,NULL,'2018-06-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1139,3,NULL,'2018-06-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1140,4,NULL,'2018-06-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1141,1,NULL,'2018-06-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1142,2,NULL,'2018-06-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1143,3,NULL,'2018-06-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1144,4,NULL,'2018-06-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1145,1,NULL,'2018-06-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1146,2,NULL,'2018-06-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1147,3,NULL,'2018-06-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1148,4,NULL,'2018-06-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1149,1,NULL,'2018-06-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1150,2,NULL,'2018-06-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1151,3,NULL,'2018-06-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1152,4,NULL,'2018-06-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1153,1,NULL,'2018-06-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1154,2,NULL,'2018-06-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1155,3,NULL,'2018-06-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1156,4,NULL,'2018-06-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1157,1,NULL,'2018-06-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1158,2,NULL,'2018-06-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1159,3,NULL,'2018-06-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1160,4,NULL,'2018-06-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1161,1,NULL,'2018-06-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1162,2,NULL,'2018-06-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1163,3,NULL,'2018-06-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1164,4,NULL,'2018-06-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1165,1,NULL,'2018-06-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1166,2,NULL,'2018-06-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1167,3,NULL,'2018-06-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1168,4,NULL,'2018-06-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1169,1,NULL,'2018-06-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1170,2,NULL,'2018-06-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1171,3,NULL,'2018-06-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1172,4,NULL,'2018-06-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1173,1,NULL,'2018-06-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1174,2,NULL,'2018-06-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1175,3,NULL,'2018-06-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1176,4,NULL,'2018-06-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1177,1,NULL,'2018-06-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1178,2,NULL,'2018-06-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1179,3,NULL,'2018-06-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1180,4,NULL,'2018-06-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1181,1,NULL,'2018-06-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1182,2,NULL,'2018-06-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1183,3,NULL,'2018-06-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1184,4,NULL,'2018-06-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1185,1,NULL,'2018-06-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1186,2,NULL,'2018-06-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1187,3,NULL,'2018-06-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1188,4,NULL,'2018-06-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1189,1,NULL,'2018-06-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1190,2,NULL,'2018-06-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1191,3,NULL,'2018-06-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1192,4,NULL,'2018-06-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1193,1,NULL,'2018-06-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1194,2,NULL,'2018-06-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1195,3,NULL,'2018-06-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1196,4,NULL,'2018-06-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1197,1,NULL,'2018-06-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1198,2,NULL,'2018-06-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1199,3,NULL,'2018-06-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1200,4,NULL,'2018-06-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1201,1,NULL,'2018-06-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1202,2,NULL,'2018-06-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1203,3,NULL,'2018-06-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1204,4,NULL,'2018-06-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1205,1,NULL,'2018-06-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1206,2,NULL,'2018-06-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1207,3,NULL,'2018-06-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1208,4,NULL,'2018-06-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1209,1,NULL,'2018-06-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1210,2,NULL,'2018-06-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1211,3,NULL,'2018-06-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1212,4,NULL,'2018-06-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1213,1,NULL,'2018-07-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1214,2,NULL,'2018-07-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1215,3,NULL,'2018-07-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1216,4,NULL,'2018-07-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1217,1,NULL,'2018-07-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1218,2,NULL,'2018-07-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1219,3,NULL,'2018-07-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1220,4,NULL,'2018-07-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1221,1,NULL,'2018-07-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1222,2,NULL,'2018-07-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1223,3,NULL,'2018-07-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1224,4,NULL,'2018-07-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1225,1,NULL,'2018-07-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1226,2,NULL,'2018-07-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1227,3,NULL,'2018-07-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1228,4,NULL,'2018-07-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1229,1,NULL,'2018-07-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1230,2,NULL,'2018-07-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1231,3,NULL,'2018-07-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1232,4,NULL,'2018-07-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1233,1,NULL,'2018-07-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1234,2,NULL,'2018-07-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1235,3,NULL,'2018-07-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1236,4,NULL,'2018-07-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1237,1,NULL,'2018-07-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1238,2,NULL,'2018-07-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1239,3,NULL,'2018-07-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1240,4,NULL,'2018-07-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1241,1,NULL,'2018-07-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1242,2,NULL,'2018-07-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1243,3,NULL,'2018-07-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1244,4,NULL,'2018-07-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1245,1,NULL,'2018-07-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1246,2,NULL,'2018-07-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1247,3,NULL,'2018-07-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1248,4,NULL,'2018-07-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1249,1,NULL,'2018-07-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1250,2,NULL,'2018-07-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1251,3,NULL,'2018-07-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1252,4,NULL,'2018-07-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1253,1,NULL,'2018-07-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1254,2,NULL,'2018-07-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1255,3,NULL,'2018-07-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1256,4,NULL,'2018-07-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1257,1,NULL,'2018-07-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1258,2,NULL,'2018-07-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1259,3,NULL,'2018-07-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1260,4,NULL,'2018-07-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1261,1,NULL,'2018-07-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1262,2,NULL,'2018-07-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1263,3,NULL,'2018-07-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1264,4,NULL,'2018-07-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1265,1,NULL,'2018-07-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1266,2,NULL,'2018-07-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1267,3,NULL,'2018-07-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1268,4,NULL,'2018-07-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1269,1,NULL,'2018-07-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1270,2,NULL,'2018-07-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1271,3,NULL,'2018-07-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1272,4,NULL,'2018-07-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1273,1,NULL,'2018-07-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1274,2,NULL,'2018-07-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1275,3,NULL,'2018-07-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1276,4,NULL,'2018-07-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1277,1,NULL,'2018-07-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1278,2,NULL,'2018-07-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1279,3,NULL,'2018-07-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1280,4,NULL,'2018-07-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1281,1,NULL,'2018-07-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1282,2,NULL,'2018-07-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1283,3,NULL,'2018-07-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1284,4,NULL,'2018-07-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1285,1,NULL,'2018-07-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1286,2,NULL,'2018-07-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1287,3,NULL,'2018-07-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1288,4,NULL,'2018-07-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1289,1,NULL,'2018-07-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1290,2,NULL,'2018-07-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1291,3,NULL,'2018-07-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1292,4,NULL,'2018-07-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1293,1,NULL,'2018-07-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1294,2,NULL,'2018-07-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1295,3,NULL,'2018-07-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1296,4,NULL,'2018-07-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1297,1,NULL,'2018-07-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1298,2,NULL,'2018-07-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1299,3,NULL,'2018-07-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1300,4,NULL,'2018-07-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1301,1,NULL,'2018-07-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1302,2,NULL,'2018-07-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1303,3,NULL,'2018-07-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1304,4,NULL,'2018-07-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1305,1,NULL,'2018-07-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1306,2,NULL,'2018-07-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1307,3,NULL,'2018-07-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1308,4,NULL,'2018-07-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1309,1,NULL,'2018-07-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1310,2,NULL,'2018-07-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1311,3,NULL,'2018-07-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1312,4,NULL,'2018-07-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1313,1,NULL,'2018-07-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1314,2,NULL,'2018-07-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1315,3,NULL,'2018-07-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1316,4,NULL,'2018-07-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1317,1,NULL,'2018-07-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1318,2,NULL,'2018-07-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1319,3,NULL,'2018-07-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1320,4,NULL,'2018-07-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1321,1,NULL,'2018-07-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1322,2,NULL,'2018-07-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1323,3,NULL,'2018-07-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1324,4,NULL,'2018-07-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1325,1,NULL,'2018-07-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1326,2,NULL,'2018-07-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1327,3,NULL,'2018-07-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1328,4,NULL,'2018-07-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1329,1,NULL,'2018-07-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1330,2,NULL,'2018-07-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1331,3,NULL,'2018-07-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1332,4,NULL,'2018-07-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1333,1,NULL,'2018-07-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1334,2,NULL,'2018-07-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1335,3,NULL,'2018-07-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1336,4,NULL,'2018-07-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1337,1,NULL,'2018-08-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1338,2,NULL,'2018-08-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1339,3,NULL,'2018-08-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1340,4,NULL,'2018-08-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1341,1,NULL,'2018-08-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1342,2,NULL,'2018-08-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1343,3,NULL,'2018-08-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1344,4,NULL,'2018-08-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1345,1,NULL,'2018-08-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1346,2,NULL,'2018-08-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1347,3,NULL,'2018-08-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1348,4,NULL,'2018-08-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1349,1,NULL,'2018-08-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1350,2,NULL,'2018-08-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1351,3,NULL,'2018-08-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1352,4,NULL,'2018-08-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1353,1,NULL,'2018-08-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1354,2,NULL,'2018-08-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1355,3,NULL,'2018-08-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1356,4,NULL,'2018-08-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1357,1,NULL,'2018-08-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1358,2,NULL,'2018-08-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1359,3,NULL,'2018-08-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1360,4,NULL,'2018-08-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1361,1,NULL,'2018-08-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1362,2,NULL,'2018-08-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1363,3,NULL,'2018-08-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1364,4,NULL,'2018-08-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1365,1,NULL,'2018-08-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1366,2,NULL,'2018-08-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1367,3,NULL,'2018-08-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1368,4,NULL,'2018-08-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1369,1,NULL,'2018-08-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1370,2,NULL,'2018-08-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1371,3,NULL,'2018-08-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1372,4,NULL,'2018-08-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1373,1,NULL,'2018-08-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1374,2,NULL,'2018-08-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1375,3,NULL,'2018-08-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1376,4,NULL,'2018-08-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1377,1,NULL,'2018-08-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1378,2,NULL,'2018-08-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1379,3,NULL,'2018-08-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1380,4,NULL,'2018-08-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1381,1,NULL,'2018-08-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1382,2,NULL,'2018-08-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1383,3,NULL,'2018-08-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1384,4,NULL,'2018-08-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1385,1,NULL,'2018-08-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1386,2,NULL,'2018-08-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1387,3,NULL,'2018-08-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1388,4,NULL,'2018-08-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1389,1,NULL,'2018-08-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1390,2,NULL,'2018-08-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1391,3,NULL,'2018-08-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1392,4,NULL,'2018-08-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1393,1,NULL,'2018-08-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1394,2,NULL,'2018-08-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1395,3,NULL,'2018-08-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1396,4,NULL,'2018-08-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1397,1,NULL,'2018-08-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1398,2,NULL,'2018-08-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1399,3,NULL,'2018-08-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1400,4,NULL,'2018-08-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1401,1,NULL,'2018-08-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1402,2,NULL,'2018-08-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1403,3,NULL,'2018-08-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1404,4,NULL,'2018-08-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1405,1,NULL,'2018-08-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1406,2,NULL,'2018-08-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1407,3,NULL,'2018-08-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1408,4,NULL,'2018-08-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1409,1,NULL,'2018-08-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1410,2,NULL,'2018-08-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1411,3,NULL,'2018-08-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1412,4,NULL,'2018-08-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1413,1,NULL,'2018-08-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1414,2,NULL,'2018-08-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1415,3,NULL,'2018-08-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1416,4,NULL,'2018-08-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1417,1,NULL,'2018-08-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1418,2,NULL,'2018-08-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1419,3,NULL,'2018-08-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1420,4,NULL,'2018-08-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1421,1,NULL,'2018-08-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1422,2,NULL,'2018-08-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1423,3,NULL,'2018-08-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1424,4,NULL,'2018-08-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1425,1,NULL,'2018-08-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1426,2,NULL,'2018-08-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1427,3,NULL,'2018-08-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1428,4,NULL,'2018-08-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1429,1,NULL,'2018-08-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1430,2,NULL,'2018-08-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1431,3,NULL,'2018-08-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1432,4,NULL,'2018-08-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1433,1,NULL,'2018-08-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1434,2,NULL,'2018-08-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1435,3,NULL,'2018-08-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1436,4,NULL,'2018-08-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1437,1,NULL,'2018-08-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1438,2,NULL,'2018-08-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1439,3,NULL,'2018-08-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1440,4,NULL,'2018-08-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1441,1,NULL,'2018-08-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1442,2,NULL,'2018-08-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1443,3,NULL,'2018-08-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1444,4,NULL,'2018-08-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1445,1,NULL,'2018-08-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1446,2,NULL,'2018-08-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1447,3,NULL,'2018-08-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1448,4,NULL,'2018-08-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1449,1,NULL,'2018-08-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1450,2,NULL,'2018-08-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1451,3,NULL,'2018-08-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1452,4,NULL,'2018-08-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1453,1,NULL,'2018-08-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1454,2,NULL,'2018-08-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1455,3,NULL,'2018-08-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1456,4,NULL,'2018-08-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1457,1,NULL,'2018-08-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1458,2,NULL,'2018-08-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1459,3,NULL,'2018-08-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1460,4,NULL,'2018-08-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1461,1,NULL,'2018-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1462,2,NULL,'2018-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1463,3,NULL,'2018-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1464,4,NULL,'2018-09-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1465,1,NULL,'2018-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1466,2,NULL,'2018-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1467,3,NULL,'2018-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1468,4,NULL,'2018-09-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1469,1,NULL,'2018-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1470,2,NULL,'2018-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1471,3,NULL,'2018-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1472,4,NULL,'2018-09-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1473,1,NULL,'2018-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1474,2,NULL,'2018-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1475,3,NULL,'2018-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1476,4,NULL,'2018-09-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1477,1,NULL,'2018-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1478,2,NULL,'2018-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1479,3,NULL,'2018-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1480,4,NULL,'2018-09-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1481,1,NULL,'2018-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1482,2,NULL,'2018-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1483,3,NULL,'2018-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1484,4,NULL,'2018-09-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1485,1,NULL,'2018-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1486,2,NULL,'2018-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1487,3,NULL,'2018-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1488,4,NULL,'2018-09-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1489,1,NULL,'2018-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1490,2,NULL,'2018-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1491,3,NULL,'2018-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1492,4,NULL,'2018-09-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1493,1,NULL,'2018-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1494,2,NULL,'2018-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1495,3,NULL,'2018-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1496,4,NULL,'2018-09-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1497,1,NULL,'2018-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1498,2,NULL,'2018-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1499,3,NULL,'2018-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1500,4,NULL,'2018-09-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1501,1,NULL,'2018-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1502,2,NULL,'2018-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1503,3,NULL,'2018-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1504,4,NULL,'2018-09-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1505,1,NULL,'2018-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1506,2,NULL,'2018-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1507,3,NULL,'2018-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1508,4,NULL,'2018-09-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1509,1,NULL,'2018-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1510,2,NULL,'2018-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1511,3,NULL,'2018-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1512,4,NULL,'2018-09-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1513,1,NULL,'2018-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1514,2,NULL,'2018-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1515,3,NULL,'2018-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1516,4,NULL,'2018-09-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1517,1,NULL,'2018-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1518,2,NULL,'2018-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1519,3,NULL,'2018-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1520,4,NULL,'2018-09-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1521,1,NULL,'2018-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1522,2,NULL,'2018-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1523,3,NULL,'2018-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1524,4,NULL,'2018-09-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1525,1,NULL,'2018-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1526,2,NULL,'2018-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1527,3,NULL,'2018-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1528,4,NULL,'2018-09-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1529,1,NULL,'2018-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1530,2,NULL,'2018-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1531,3,NULL,'2018-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1532,4,NULL,'2018-09-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1533,1,NULL,'2018-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1534,2,NULL,'2018-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1535,3,NULL,'2018-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1536,4,NULL,'2018-09-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1537,1,NULL,'2018-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1538,2,NULL,'2018-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1539,3,NULL,'2018-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1540,4,NULL,'2018-09-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1541,1,NULL,'2018-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1542,2,NULL,'2018-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1543,3,NULL,'2018-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1544,4,NULL,'2018-09-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1545,1,NULL,'2018-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1546,2,NULL,'2018-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1547,3,NULL,'2018-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1548,4,NULL,'2018-09-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1549,1,NULL,'2018-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1550,2,NULL,'2018-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1551,3,NULL,'2018-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1552,4,NULL,'2018-09-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1553,1,NULL,'2018-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1554,2,NULL,'2018-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1555,3,NULL,'2018-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1556,4,NULL,'2018-09-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1557,1,NULL,'2018-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1558,2,NULL,'2018-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1559,3,NULL,'2018-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1560,4,NULL,'2018-09-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1561,1,NULL,'2018-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1562,2,NULL,'2018-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1563,3,NULL,'2018-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1564,4,NULL,'2018-09-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1565,1,NULL,'2018-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1566,2,NULL,'2018-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1567,3,NULL,'2018-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1568,4,NULL,'2018-09-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1569,1,NULL,'2018-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1570,2,NULL,'2018-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1571,3,NULL,'2018-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1572,4,NULL,'2018-09-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1573,1,NULL,'2018-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1574,2,NULL,'2018-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1575,3,NULL,'2018-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1576,4,NULL,'2018-09-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1577,1,NULL,'2018-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1578,2,NULL,'2018-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1579,3,NULL,'2018-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1580,4,NULL,'2018-09-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1581,1,NULL,'2018-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1582,2,NULL,'2018-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1583,3,NULL,'2018-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1584,4,NULL,'2018-10-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1585,1,NULL,'2018-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1586,2,NULL,'2018-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1587,3,NULL,'2018-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1588,4,NULL,'2018-10-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1589,1,NULL,'2018-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1590,2,NULL,'2018-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1591,3,NULL,'2018-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1592,4,NULL,'2018-10-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1593,1,NULL,'2018-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1594,2,NULL,'2018-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1595,3,NULL,'2018-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1596,4,NULL,'2018-10-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1597,1,NULL,'2018-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1598,2,NULL,'2018-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1599,3,NULL,'2018-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1600,4,NULL,'2018-10-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1601,1,NULL,'2018-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1602,2,NULL,'2018-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1603,3,NULL,'2018-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1604,4,NULL,'2018-10-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1605,1,NULL,'2018-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1606,2,NULL,'2018-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1607,3,NULL,'2018-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1608,4,NULL,'2018-10-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1609,1,NULL,'2018-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1610,2,NULL,'2018-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1611,3,NULL,'2018-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1612,4,NULL,'2018-10-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1613,1,NULL,'2018-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1614,2,NULL,'2018-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1615,3,NULL,'2018-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1616,4,NULL,'2018-10-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1617,1,NULL,'2018-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1618,2,NULL,'2018-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1619,3,NULL,'2018-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1620,4,NULL,'2018-10-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1621,1,NULL,'2018-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1622,2,NULL,'2018-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1623,3,NULL,'2018-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1624,4,NULL,'2018-10-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1625,1,NULL,'2018-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1626,2,NULL,'2018-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1627,3,NULL,'2018-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1628,4,NULL,'2018-10-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1629,1,NULL,'2018-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1630,2,NULL,'2018-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1631,3,NULL,'2018-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1632,4,NULL,'2018-10-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1633,1,NULL,'2018-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1634,2,NULL,'2018-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1635,3,NULL,'2018-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1636,4,NULL,'2018-10-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1637,1,NULL,'2018-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1638,2,NULL,'2018-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1639,3,NULL,'2018-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1640,4,NULL,'2018-10-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1641,1,NULL,'2018-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1642,2,NULL,'2018-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1643,3,NULL,'2018-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1644,4,NULL,'2018-10-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1645,1,NULL,'2018-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1646,2,NULL,'2018-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1647,3,NULL,'2018-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1648,4,NULL,'2018-10-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1649,1,NULL,'2018-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1650,2,NULL,'2018-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1651,3,NULL,'2018-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1652,4,NULL,'2018-10-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1653,1,NULL,'2018-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1654,2,NULL,'2018-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1655,3,NULL,'2018-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1656,4,NULL,'2018-10-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1657,1,NULL,'2018-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1658,2,NULL,'2018-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1659,3,NULL,'2018-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1660,4,NULL,'2018-10-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1661,1,NULL,'2018-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1662,2,NULL,'2018-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1663,3,NULL,'2018-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1664,4,NULL,'2018-10-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1665,1,NULL,'2018-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1666,2,NULL,'2018-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1667,3,NULL,'2018-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1668,4,NULL,'2018-10-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1669,1,NULL,'2018-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1670,2,NULL,'2018-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1671,3,NULL,'2018-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1672,4,NULL,'2018-10-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1673,1,NULL,'2018-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1674,2,NULL,'2018-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1675,3,NULL,'2018-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1676,4,NULL,'2018-10-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1677,1,NULL,'2018-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1678,2,NULL,'2018-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1679,3,NULL,'2018-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1680,4,NULL,'2018-10-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1681,1,NULL,'2018-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1682,2,NULL,'2018-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1683,3,NULL,'2018-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1684,4,NULL,'2018-10-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1685,1,NULL,'2018-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1686,2,NULL,'2018-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1687,3,NULL,'2018-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1688,4,NULL,'2018-10-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1689,1,NULL,'2018-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1690,2,NULL,'2018-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1691,3,NULL,'2018-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1692,4,NULL,'2018-10-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1693,1,NULL,'2018-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1694,2,NULL,'2018-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1695,3,NULL,'2018-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1696,4,NULL,'2018-10-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1697,1,NULL,'2018-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1698,2,NULL,'2018-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1699,3,NULL,'2018-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1700,4,NULL,'2018-10-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1701,1,NULL,'2018-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1702,2,NULL,'2018-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1703,3,NULL,'2018-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1704,4,NULL,'2018-10-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1705,1,NULL,'2018-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1706,2,NULL,'2018-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1707,3,NULL,'2018-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1708,4,NULL,'2018-11-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1709,1,NULL,'2018-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1710,2,NULL,'2018-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1711,3,NULL,'2018-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1712,4,NULL,'2018-11-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1713,1,NULL,'2018-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1714,2,NULL,'2018-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1715,3,NULL,'2018-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1716,4,NULL,'2018-11-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1717,1,NULL,'2018-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1718,2,NULL,'2018-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1719,3,NULL,'2018-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1720,4,NULL,'2018-11-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1721,1,NULL,'2018-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1722,2,NULL,'2018-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1723,3,NULL,'2018-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1724,4,NULL,'2018-11-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1725,1,NULL,'2018-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1726,2,NULL,'2018-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1727,3,NULL,'2018-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1728,4,NULL,'2018-11-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1729,1,NULL,'2018-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1730,2,NULL,'2018-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1731,3,NULL,'2018-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1732,4,NULL,'2018-11-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1733,1,NULL,'2018-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1734,2,NULL,'2018-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1735,3,NULL,'2018-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1736,4,NULL,'2018-11-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1737,1,NULL,'2018-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1738,2,NULL,'2018-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1739,3,NULL,'2018-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1740,4,NULL,'2018-11-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1741,1,NULL,'2018-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1742,2,NULL,'2018-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1743,3,NULL,'2018-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1744,4,NULL,'2018-11-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1745,1,NULL,'2018-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1746,2,NULL,'2018-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1747,3,NULL,'2018-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1748,4,NULL,'2018-11-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1749,1,NULL,'2018-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1750,2,NULL,'2018-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1751,3,NULL,'2018-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1752,4,NULL,'2018-11-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1753,1,NULL,'2018-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1754,2,NULL,'2018-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1755,3,NULL,'2018-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1756,4,NULL,'2018-11-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1757,1,NULL,'2018-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1758,2,NULL,'2018-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1759,3,NULL,'2018-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1760,4,NULL,'2018-11-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1761,1,NULL,'2018-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1762,2,NULL,'2018-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1763,3,NULL,'2018-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1764,4,NULL,'2018-11-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1765,1,NULL,'2018-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1766,2,NULL,'2018-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1767,3,NULL,'2018-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1768,4,NULL,'2018-11-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1769,1,NULL,'2018-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1770,2,NULL,'2018-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1771,3,NULL,'2018-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1772,4,NULL,'2018-11-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1773,1,NULL,'2018-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1774,2,NULL,'2018-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1775,3,NULL,'2018-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1776,4,NULL,'2018-11-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1777,1,NULL,'2018-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1778,2,NULL,'2018-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1779,3,NULL,'2018-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1780,4,NULL,'2018-11-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1781,1,NULL,'2018-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1782,2,NULL,'2018-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1783,3,NULL,'2018-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1784,4,NULL,'2018-11-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1785,1,NULL,'2018-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1786,2,NULL,'2018-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1787,3,NULL,'2018-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1788,4,NULL,'2018-11-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1789,1,NULL,'2018-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1790,2,NULL,'2018-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1791,3,NULL,'2018-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1792,4,NULL,'2018-11-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1793,1,NULL,'2018-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1794,2,NULL,'2018-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1795,3,NULL,'2018-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1796,4,NULL,'2018-11-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1797,1,NULL,'2018-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1798,2,NULL,'2018-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1799,3,NULL,'2018-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1800,4,NULL,'2018-11-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1801,1,NULL,'2018-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1802,2,NULL,'2018-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1803,3,NULL,'2018-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1804,4,NULL,'2018-11-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1805,1,NULL,'2018-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1806,2,NULL,'2018-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1807,3,NULL,'2018-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1808,4,NULL,'2018-11-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1809,1,NULL,'2018-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1810,2,NULL,'2018-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1811,3,NULL,'2018-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1812,4,NULL,'2018-11-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1813,1,NULL,'2018-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1814,2,NULL,'2018-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1815,3,NULL,'2018-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1816,4,NULL,'2018-11-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1817,1,NULL,'2018-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1818,2,NULL,'2018-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1819,3,NULL,'2018-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1820,4,NULL,'2018-11-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1821,1,NULL,'2018-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1822,2,NULL,'2018-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1823,3,NULL,'2018-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1824,4,NULL,'2018-11-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1825,1,NULL,'2018-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1826,2,NULL,'2018-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1827,3,NULL,'2018-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1828,4,NULL,'2018-12-01','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1829,1,NULL,'2018-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1830,2,NULL,'2018-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1831,3,NULL,'2018-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1832,4,NULL,'2018-12-02','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1833,1,NULL,'2018-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1834,2,NULL,'2018-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1835,3,NULL,'2018-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1836,4,NULL,'2018-12-03','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1837,1,NULL,'2018-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1838,2,NULL,'2018-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1839,3,NULL,'2018-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1840,4,NULL,'2018-12-04','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1841,1,NULL,'2018-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1842,2,NULL,'2018-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1843,3,NULL,'2018-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1844,4,NULL,'2018-12-05','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1845,1,NULL,'2018-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1846,2,NULL,'2018-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1847,3,NULL,'2018-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1848,4,NULL,'2018-12-06','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1849,1,NULL,'2018-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1850,2,NULL,'2018-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1851,3,NULL,'2018-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1852,4,NULL,'2018-12-07','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1853,1,NULL,'2018-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1854,2,NULL,'2018-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1855,3,NULL,'2018-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1856,4,NULL,'2018-12-08','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1857,1,NULL,'2018-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1858,2,NULL,'2018-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1859,3,NULL,'2018-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1860,4,NULL,'2018-12-09','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1861,1,NULL,'2018-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1862,2,NULL,'2018-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1863,3,NULL,'2018-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1864,4,NULL,'2018-12-10','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1865,1,NULL,'2018-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1866,2,NULL,'2018-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1867,3,NULL,'2018-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1868,4,NULL,'2018-12-11','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1869,1,NULL,'2018-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1870,2,NULL,'2018-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1871,3,NULL,'2018-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1872,4,NULL,'2018-12-12','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1873,1,NULL,'2018-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1874,2,NULL,'2018-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1875,3,NULL,'2018-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1876,4,NULL,'2018-12-13','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1877,1,NULL,'2018-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1878,2,NULL,'2018-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1879,3,NULL,'2018-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1880,4,NULL,'2018-12-14','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1881,1,NULL,'2018-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1882,2,NULL,'2018-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1883,3,NULL,'2018-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1884,4,NULL,'2018-12-15','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1885,1,NULL,'2018-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1886,2,NULL,'2018-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1887,3,NULL,'2018-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1888,4,NULL,'2018-12-16','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1889,1,NULL,'2018-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1890,2,NULL,'2018-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1891,3,NULL,'2018-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1892,4,NULL,'2018-12-17','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1893,1,NULL,'2018-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1894,2,NULL,'2018-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1895,3,NULL,'2018-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1896,4,NULL,'2018-12-18','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1897,1,NULL,'2018-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1898,2,NULL,'2018-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1899,3,NULL,'2018-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1900,4,NULL,'2018-12-19','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1901,1,NULL,'2018-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1902,2,NULL,'2018-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1903,3,NULL,'2018-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1904,4,NULL,'2018-12-20','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1905,1,NULL,'2018-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1906,2,NULL,'2018-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1907,3,NULL,'2018-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1908,4,NULL,'2018-12-21','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1909,1,NULL,'2018-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1910,2,NULL,'2018-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1911,3,NULL,'2018-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1912,4,NULL,'2018-12-22','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1913,1,NULL,'2018-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1914,2,NULL,'2018-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1915,3,NULL,'2018-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1916,4,NULL,'2018-12-23','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1917,1,NULL,'2018-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1918,2,NULL,'2018-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1919,3,NULL,'2018-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1920,4,NULL,'2018-12-24','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1921,1,NULL,'2018-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1922,2,NULL,'2018-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1923,3,NULL,'2018-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1924,4,NULL,'2018-12-25','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1925,1,NULL,'2018-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1926,2,NULL,'2018-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1927,3,NULL,'2018-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1928,4,NULL,'2018-12-26','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1929,1,NULL,'2018-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1930,2,NULL,'2018-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1931,3,NULL,'2018-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1932,4,NULL,'2018-12-27','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1933,1,NULL,'2018-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1934,2,NULL,'2018-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1935,3,NULL,'2018-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1936,4,NULL,'2018-12-28','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1937,1,NULL,'2018-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1938,2,NULL,'2018-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1939,3,NULL,'2018-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1940,4,NULL,'2018-12-29','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1941,1,NULL,'2018-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1942,2,NULL,'2018-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1943,3,NULL,'2018-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1944,4,NULL,'2018-12-30','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1945,1,NULL,'2018-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1946,2,NULL,'2018-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1947,3,NULL,'2018-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0),(1948,4,NULL,'2018-12-31','available','2017-09-14 23:08:39','admin','2017-09-14 23:08:39','admin',1,0);

/*Table structure for table `pos_unit` */

DROP TABLE IF EXISTS `pos_unit`;

CREATE TABLE `pos_unit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
  `unit_name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `createdby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `satuan_code_idx` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_unit` */

insert  into `pos_unit`(`id`,`unit_code`,`unit_name`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Kg','Kilogram','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(2,'Ton','Ton','administrator','2014-07-20 00:33:41','administrator','0000-00-00 00:00:00',1,0),(3,'Btl','Botol','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(4,'Galon','Galon','administrator','2014-08-17 20:02:18','administrator','2014-08-18 03:02:18',1,0),(5,'Pack','Pack','administrator','2014-08-17 20:05:31','administrator','2014-08-18 03:05:31',1,0),(7,'Box','Box','administrator','2014-08-28 08:45:52','administrator','2014-08-28 15:45:52',1,0),(8,'pail','pail','andri','2014-09-17 14:51:09','andri','2014-09-17 14:51:34',1,0),(9,'tabung','tabung','andri','2014-09-17 14:57:47','andri','2014-09-17 14:57:47',1,0),(10,'EKOR','EKOR','andri','2014-09-18 15:21:03','andri','2014-09-18 15:21:03',1,0),(11,'Bal','Bal','andri','2014-09-18 15:31:06','andri','2014-09-18 15:31:06',1,0),(12,'PCS','PCS','andri','2014-09-18 15:41:30','andri','2014-09-18 15:41:30',1,0),(13,'PAPAN','PAPAN','andri','2014-09-18 16:21:02','andri','2014-09-18 16:21:02',1,0),(14,'SISIR','SISIR','andri','2014-09-18 16:22:25','andri','2014-09-18 16:22:25',1,0),(15,'TANDAN','TANDAN','andri','2014-09-18 16:22:37','andri','2014-09-18 16:22:37',1,0),(16,'Krat','Krat','andri','2014-09-27 10:09:34','andri','2014-09-27 10:09:34',1,0),(17,'Ctn','Ctn','andri','2014-09-27 10:09:58','andri','2014-09-27 10:11:16',1,0),(18,'Laof','Loaf','andri','2014-09-27 10:11:30','andri','2014-09-27 10:11:30',1,0),(19,'Roll','Roll','andri','2014-09-27 10:11:45','andri','2014-09-27 10:11:45',1,0),(20,'Toples','Toples','andri','2014-09-27 10:12:14','andri','2014-09-27 10:12:14',1,0),(21,'Can','Can','andri','2014-09-27 10:13:02','andri','2014-09-27 10:13:02',1,0),(22,'liter','liter','andri','2014-09-27 13:13:08','andri','2014-09-27 13:13:08',1,0),(23,'Slop','Slop','andri','2014-10-01 16:22:55','andri','2014-10-01 16:22:55',1,0),(24,'Buah','Buah','andri','2014-10-01 16:23:09','andri','2014-10-01 16:23:09',1,0),(25,'pasang','pasang','siska','2014-10-03 10:41:59','siska','2014-10-03 10:41:59',1,0),(26,'gr','gram','siska','2016-02-03 15:48:10','siska','2016-02-03 15:48:10',1,0),(27,'ml','ml','siska','2016-02-03 16:11:24','siska','2016-02-03 16:11:24',1,0);

/*Table structure for table `pos_varian` */

DROP TABLE IF EXISTS `pos_varian`;

CREATE TABLE `pos_varian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `varian_name` varchar(100) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `pos_varian` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
