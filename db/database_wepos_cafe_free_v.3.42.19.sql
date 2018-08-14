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
 `ap_date` date DEFAULT NULL,
 `no_kontrabon` varchar(30) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `ap_no` (`ap_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `acc_account_payable` */

/*Table structure for table `acc_autoposting` */

DROP TABLE IF EXISTS `acc_autoposting`;

CREATE TABLE `acc_autoposting` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `autoposting_name` varchar(100) NOT NULL,
 `autoposting_tipe` enum('purchasing','sales','other','pelunasan_account_payable','account_payable','account_receivable','pembayaran_account_receivable','cashflow_penerimaan','cashflow_pengeluaran','cashflow_mutasi_kas_bank') DEFAULT 'other',
 `rek_id_debet` int(11) DEFAULT NULL,
 `rek_id_kredit` int(11) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) DEFAULT '1',
 `is_deleted` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `acc_autoposting` */

insert into `acc_autoposting`(`id`,`autoposting_name`,`autoposting_tipe`,`rek_id_debet`,`rek_id_kredit`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Hutang Pembelian Bahan Baku ke Supplier','account_payable',24,74,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:39',1,0),(2,'Hutang Pembelian Alat/barang Supplier','account_payable',31,74,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(3,'Pelunasan Hutang Supplier via Kas Besar','pelunasan_account_payable',74,9,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(4,'Pelunasan Hutang Supplier via Bank Mandiri','pelunasan_account_payable',74,12,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(5,'Pelunasan Hutang Supplier via Bank BCA','pelunasan_account_payable',74,13,'administrator','2016-04-05 21:47:27','administrator','2016-04-05 21:47:27',1,0),(6,'Piutang Penjualan (Sales/Cashier)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(7,'Piutang Penjualan (Sales Order/Reservasi)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(8,'Piutang Penjualan (Marketplace/Online)','account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(9,'Pembayaran Piutang via Kas Besar','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(10,'Pembayaran Piutang via Bank BCA','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0),(11,'Pembayaran Piutang via Bank Mandiri','pembayaran_account_receivable',NULL,NULL,NULL,NULL,NULL,NULL,1,0);

/*Table structure for table `apps_clients` */

DROP TABLE IF EXISTS `apps_clients`;

CREATE TABLE `apps_clients` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_code` varchar(50) NOT NULL,
 `client_name` varchar(100) NOT NULL,
 `client_address` varchar(100) DEFAULT NULL,
 `city_id` int(11) DEFAULT NULL,
 `province_id` int(11) DEFAULT NULL,
 `client_postcode` varchar(5) DEFAULT NULL,
 `country_id` int(11) DEFAULT NULL,
 `client_phone` varchar(50) DEFAULT NULL,
 `client_fax` varchar(50) DEFAULT NULL,
 `client_email` varbinary(100) DEFAULT NULL,
 `client_logo` varchar(200) DEFAULT NULL,
 `client_website` varchar(50) DEFAULT NULL,
 `client_notes` varchar(400) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `client_ip` char(30) DEFAULT NULL,
 `mysql_user` char(30) DEFAULT NULL,
 `mysql_pass` varchar(100) DEFAULT NULL,
 `mysql_port` char(10) DEFAULT NULL,
 `mysql_database` char(100) DEFAULT NULL, `merchant_verified` enum('unverified','verified') DEFAULT 'unverified', `merchant_xid` varchar(50) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `rs_kode` (`client_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_clients` */
insert into `apps_clients`(`id`,`client_code`,`client_name`,`client_address`,`city_id`,`province_id`,`client_postcode`,`country_id`,`client_phone`,`client_fax`,`client_email`,`client_logo`,`client_website`,`client_notes`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`client_ip`,`mysql_user`,`mysql_pass`,`mysql_port`,`mysql_database`,`merchant_verified`,`merchant_xid`) values (1,'','WEPOS','JL. Kebon Sirih Dalam No.26',NULL,0,NULL,NULL,'081222549676','-','contact@wepos.id','logo.png',NULL,NULL,'','2014-06-28 04:07:01','system','2018-08-01 15:41:20',1,0,NULL,NULL,NULL,NULL,NULL,'unverified','');

/*Table structure for table `apps_clients_structure` */

DROP TABLE IF EXISTS `apps_clients_structure`;

CREATE TABLE `apps_clients_structure` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_structure_name` varchar(255) NOT NULL,
 `client_structure_notes` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_clients_structure` */

insert into `apps_clients_structure`(`id`,`client_structure_name`,`client_structure_notes`,`client_structure_parent`,`client_structure_order`,`is_child`,`role_id`,`client_id`,`client_unit_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Apps Administrator','Apps Super Admin',0,0,0,1,0,1,'1','0000-00-00 00:00:00','1','0000-00-00 00:00:00',1,0),(2,'Apps Admin','',1,0,1,2,1,2,'1','2014-08-11 14:55:11','administrator','0000-00-00 00:00:00',1,0),(3,'Finance','',10,0,1,9,1,3,'1','2014-08-11 14:58:00','administrator','2014-09-13 11:17:37',1,0),(4,'Accounting','',10,0,1,8,1,3,'1','2014-08-11 14:59:00','administrator','2017-09-15 13:07:16',1,0),(5,'Supervisor Operational','',13,0,1,8,1,4,'1','2014-08-11 15:00:05','administrator','2016-07-28 12:36:56',1,0),(6,'Kitchen','',5,0,1,5,1,4,'1','2014-08-11 15:00:54','administrator','2017-09-15 13:03:54',0,0),(7,'Crew','',5,0,1,5,1,4,'1','2014-08-11 15:12:00','administrator','2017-09-15 13:04:31',1,0),(8,'HRD','',13,0,1,9,1,1,'1','2014-08-11 15:12:19','administrator','2017-09-15 13:05:51',1,0),(9,'Service','',5,0,1,5,1,4,'administrator','2014-09-13 11:15:33','administrator','2017-09-15 13:04:44',0,0),(10,'Manager Finance Accounting','',13,0,1,8,1,3,'administrator','2014-09-13 11:17:25','administrator','2017-09-15 13:06:05',1,0),(11,'Purchasing','',10,0,1,8,1,3,'administrator','2014-09-13 11:23:27','administrator','2017-09-15 13:07:24',1,0),(12,'F & B','',13,0,1,8,1,1,'administrator','2014-09-13 13:00:12','administrator','2017-09-15 13:07:31',1,0),(13,'General Manager','',2,0,1,16,1,1,'administrator','2014-12-15 10:50:47','administrator','2014-12-15 10:57:18',1,0),(14,'GRO','',5,0,1,5,1,4,'administrator','2015-09-04 12:57:13','administrator','2017-09-15 13:04:53',0,0);

/*Table structure for table `apps_clients_unit` */

DROP TABLE IF EXISTS `apps_clients_unit`;

CREATE TABLE `apps_clients_unit` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `client_unit_name` varchar(255) NOT NULL,
 `client_id` int(11) NOT NULL,
 `client_unit_code` char(10) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_clients_unit` */

insert into `apps_clients_unit`(`id`,`client_unit_name`,`client_id`,`client_unit_code`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Management',1,'MNG','1','0000-00-00 00:00:00','1','0000-00-00 00:00:00',1,0),(2,'IT Dept.',1,'IT','1','2014-06-04 08:38:17','administrator','0000-00-00 00:00:00',1,0),(3,'Accounting',1,'ACC','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0),(4,'Operational',1,'OPR','administrator','0000-00-00 00:00:00','administrator','0000-00-00 00:00:00',1,0);

/*Table structure for table `apps_modules` */

DROP TABLE IF EXISTS `apps_modules`;

CREATE TABLE `apps_modules` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `module_name` varchar(255) NOT NULL,
 `module_author` varchar(100) DEFAULT NULL,
 `module_version` varchar(10) DEFAULT NULL,
 `module_description` varchar(255) DEFAULT NULL,
 `module_folder` varchar(255) NOT NULL,
 `module_controller` varchar(255) NOT NULL,
 `module_is_menu` tinyint(1) DEFAULT '0',
 `module_breadcrumb` varchar(100) NOT NULL,
 `module_order` int(5) DEFAULT '0',
 `module_icon` varchar(50) DEFAULT NULL,
 `module_shortcut_icon` varchar(50) DEFAULT NULL,
 `module_glyph_icon` varchar(50) DEFAULT NULL,
 `module_glyph_font` varchar(100) DEFAULT NULL,
 `module_free` tinyint(1) DEFAULT '1',
 `running_background` tinyint(1) DEFAULT '0',
 `show_on_start_menu` tinyint(1) DEFAULT '1',
 `show_on_right_start_menu` tinyint(4) DEFAULT '0',
 `start_menu_path` varchar(255) DEFAULT NULL,
 `start_menu_order` int(11) DEFAULT '0',
 `start_menu_icon` varchar(100) DEFAULT NULL,
 `start_menu_glyph` varchar(100) DEFAULT NULL,
 `show_on_context_menu` tinyint(1) DEFAULT '0',
 `context_menu_icon` varchar(100) DEFAULT NULL,
 `context_menu_glyph` varchar(100) DEFAULT NULL,
 `show_on_shorcut_desktop` tinyint(1) DEFAULT NULL,
 `desktop_shortcut_icon` varchar(100) DEFAULT NULL,
 `desktop_shortcut_glyph` varchar(100) DEFAULT NULL,
 `show_on_preference` tinyint(1) DEFAULT '0',
 `preference_icon` varchar(100) DEFAULT NULL,
 `preference_glyph` varchar(100) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `module_controller` (`module_controller`)) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `apps_modules` */insert into `apps_modules`(`id`,`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Setup Aplikasi','dev@wepos.id','v.1.0','','systems','setupAplikasi',1,'1. Master Aplikasi>Setup Aplikasi',1,'icon-cog','icon-cog','','',1,0,1,0,'1. Master Aplikasi>Setup Aplikasi',1000,'icon-cog','',0,'icon-cog','',1,'icon-cog','',0,'icon-cog','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(2,'Client Info','dev@wepos.id','v.1.0.0','Client Info','systems','clientInfo',0,'1. Master Aplikasi>Client Info',1,'icon-home','icon-home','','',1,0,1,0,'1. Master Aplikasi>Client Info',1101,'icon-home','',0,'icon-home','',1,'icon-home','',1,'icon-home','','administrator','2018-07-03 07:47:08','administrator','2018-07-03 07:47:08',1,0),(3,'Client Unit','dev@wepos.id','v.1.0','','systems','DataClientUnit',1,'1. Master Aplikasi>Client Unit',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Client Unit',1102,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:10','administrator','2018-07-30 00:00:00',1,0),(4,'Data Structure','dev@wepos.id','v.1.0','','systems','DataStructure',1,'1. Master Aplikasi>Data Structure',1,'icon-building','icon-building','','',1,0,1,0,'1. Master Aplikasi>Data Structure',1103,'icon-building','',0,'icon-building','',1,'icon-building','',1,'icon-building','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(5,'Role Manager','dev@wepos.id','v.1.2','Role Manager','systems','Roles',1,'1. Master Aplikasi>Role Manager',1,'icon-role-modules','icon-role-modules','','',1,0,1,0,'1. Master Aplikasi>Role Manager',1201,'icon-role-modules','',0,'icon-role-modules','',1,'icon-role-modules','',1,'icon-role-modules','','administrator','2018-07-10 08:52:15','administrator','2018-07-30 00:00:00',1,0),(6,'Data User','dev@wepos.id','v.1.0','','systems','UserData',1,'1. Master Aplikasi>Data User',1,'icon-user-data','icon-user-data','','',1,0,1,0,'1. Master Aplikasi>Data User',1203,'icon-user-data','',0,'icon-user-data','',1,'icon-user-data','',0,'icon-user-data','','administrator','2018-07-10 08:52:11','administrator','2018-07-30 00:00:00',1,0),(7,'User Profile','dev@wepos.id','v.1.0','','systems','UserProfile',1,'1. Master Aplikasi>User Profile',1,'user','user','','',1,0,1,1,'1. Master Aplikasi>User Profile',1301,'user','',1,'user','',1,'user','',1,'user','','administrator','2018-07-10 08:52:17','administrator','2018-07-30 00:00:00',1,0),(8,'Desktop Shortcuts','dev@wepos.id','v.1.0','Shortcuts Manager to Desktop','systems','DesktopShortcuts',1,'1. Master Aplikasi>Desktop Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,1,'1. Master Aplikasi>Desktop Shortcuts',1302,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-10 08:52:12','administrator','2018-07-30 00:00:00',1,0),(9,'QuickStart Shortcuts','dev@wepos.id','v.1.0','','systems','QuickStartShortcuts',0,'1. Master Aplikasi>QuickStart Shortcuts',1,'icon-preferences','icon-preferences','','',1,0,1,0,'1. Master Aplikasi>QuickStart Shortcuts',1303,'icon-preferences','',0,'icon-preferences','',1,'icon-preferences','',1,'icon-preferences','','administrator','2018-07-24 07:43:19','administrator','2018-07-21 09:16:19',1,0),(10,'Refresh Aplikasi','dev@wepos.id','v.1.0.0','','systems','refreshModule',0,'Refresh Aplikasi',1,'icon-refresh','icon-refresh','','',1,0,0,0,'Refresh Aplikasi',1304,'icon-refresh','',0,'icon-refresh','',1,'icon-refresh','',0,'icon-refresh','','administrator','2018-07-17 15:00:19','administrator','2018-07-17 15:00:19',1,0),(11,'Lock Screen','dev@wepos.id','v.1.0.0','User Lock Screen','systems','lockScreen',0,'LockScreen',1,'icon-grid','icon-grid','','',1,1,0,0,'LockScreen',1305,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:40:20','administrator','2018-07-30 00:00:00',1,0),(12,'Logout','dev@wepos.id','v.1.0.0','Just Logout Module','systems','logoutModule',0,'Logout',1,'icon-grid','icon-grid','','',1,1,0,0,'Logout',1306,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 01:36:16','administrator','2018-07-20 15:06:35',1,0),(13,'WePOS Update','dev@wepos.id','v.1.0.0','WePOS Update','systems','weposUpdate',0,'1. Master Aplikasi>WePOS Update',1,'icon-sync','icon-grid','','',1,0,1,0,'1. Master Aplikasi>WePOS Update',1401,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(14,'Notifikasi Sistem','dev@wepos.id','v.1.0.0','Notifikasi Sistem','systems','systemNotify',0,'Notifikasi Sistem',1,'icon-info','icon-info','','',1,1,0,0,'Notifikasi Sistem',1402,'icon-info','',0,'icon-info','',0,'icon-info','',0,'icon-info','','administrator','2018-07-22 08:00:58','administrator','2018-07-22 08:00:58',1,0),(15,'Menu Category','dev@wepos.id','v.1.0','','master_pos','productCategory',0,'2. Master POS>Menu Category',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Menu Category',2101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:07','administrator','2018-07-30 00:00:00',1,0),(16,'Master Menu & Package','dev@wepos.id','v.1.0','Master Menu & Package','master_pos','masterProduct',0,'2. Master POS>Master Menu',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Menu',2102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(17,'Master Warehouse','dev@wepos.id','v.1.0.0','Master Warehouse','master_pos','masterStoreHouse',0,'2. Master POS>Master Warehouse',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Warehouse',2201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:56','administrator','2018-07-21 20:05:16',1,0),(18,'Master Unit','dev@wepos.id','v.1.0.0','Master Unit','master_pos','masterUnit',0,'2. Master POS>Master Unit',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Unit',2202,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:13','administrator','2018-07-12 22:15:29',1,0),(19,'Master Supplier','dev@wepos.id','v.1.0.0','Master Supplier','master_pos','masterSupplier',0,'2. Master POS>Supplier',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Supplier',2203,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:25:04','administrator','2018-07-21 20:04:34',1,0),(20,'Item Category','dev@wepos.id','v.1.0.0','Item Category','master_pos','itemCategory',0,'2. Master POS>Item Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Category',2210,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(21,'Item Sub Category','dev@wepos.id','v.1.0.0','Item Sub Category','master_pos','itemSubCategory',0,'2. Master POS>Item Sub Category',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Item Sub Category',2211,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-05 00:36:29','administrator','2018-07-15 20:31:54',1,0),(22,'Master Item','dev@wepos.id','v.1.0.0','Data Item','master_pos','masterItemCafe',0,'2. Master POS>Master Item',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Item',2230,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-13 14:04:34','administrator','2018-07-13 14:04:34',1,0),(23,'Discount Planner','dev@wepos.id','v.1.0','Planning All discount Menu','master_pos','discountPlanner',0,'2. Master POS>Discount Planner',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Discount Planner',2301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:26:01','administrator','2018-07-30 00:00:00',1,0),(24,'Printer Manager','dev@wepos.id','v.1.0','Printer Manager','master_pos','masterPrinter',0,'2. Master POS>Printer Manager',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Printer Manager',2302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:24:50','administrator','2018-07-21 20:06:25',1,0),(25,'Master Bank','dev@wepos.id','v.1.0.0','Master Bank','master_pos','masterBank',0,'2. Master POS>Master Bank',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Bank',2304,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:24:53','administrator','2018-07-21 20:05:03',1,0),(26,'Master Floor Plan','dev@wepos.id','v.1.0','','master_pos','masterFloorplan',0,'2. Master POS>Master Floor Plan',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Floor Plan',2307,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:51','administrator','2018-07-30 00:00:00',1,0),(27,'Master Room','dev@wepos.id','v.1.0','Master Room','master_pos','masterRoom',0,'2. Master POS>Master Room',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Room',2308,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:24:38','administrator','2018-07-30 00:00:00',1,0),(28,'Master Table','dev@wepos.id','v.1.0.0','','master_pos','masterTable',0,'2. Master POS>Master Table',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Master Table',2309,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:54','administrator','2018-07-30 00:00:00',1,0),(29,'Table Inventory','dev@wepos.id','v.1.0.0','','master_pos','tableInventory',0,'2. Master POS>Table Inventory',2,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>Table Inventory',2310,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 17:26:59','administrator','2018-07-30 00:00:00',1,0),(30,'Warehouse Access','dev@wepos.id','v.1.0.0','Warehouse Access','master_pos','warehouseAccess',0,'2. Master POS>User Access>Warehouse Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Warehouse Access',2401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-27 19:23:32','administrator','2018-07-21 20:02:49',1,0),(31,'Printer Access','dev@wepos.id','v.1.0.0','Printer Access','master_pos','printerAccess',0,'2. Master POS>User Access>Printer Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Printer Access',2402,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-21 20:02:38',1,0),(32,'Supervisor Access','dev@wepos.id','v.1.0.0','Supervisor Access','master_pos','supervisorAccess',0,'2. Master POS>User Access>Supervisor Access',1,'icon-grid','icon-grid','','',1,0,1,0,'2. Master POS>User Access>Supervisor Access',2403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 22:53:04','administrator','2018-07-21 20:02:58',1,0),(33,'Open Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','openCashierShift',0,'3. Cashier & Reservation>Open Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Open Cashier (Shift)',3001,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:12','administrator','2018-07-30 00:00:00',1,0),(34,'Close Cashier (Shift)','dev@wepos.id','v.1.0','','cashier','closeCashierShift',0,'3. Cashier & Reservation>Close Cashier (Shift)',7,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Close Cashier (Shift)',3002,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 17:28:17','administrator','2018-07-30 00:00:00',1,0),(35,'List Open Close Cashier','dev@wepos.id','v.1.0.0','','cashier','listOpenCloseCashier',0,'3. Cashier & Reservation>List Open Close Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>List Open Close Cashier',3003,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-20 07:59:55','administrator','2018-07-20 07:59:55',1,0),(36,'Cashier','dev@wepos.id','v.1.0','Cashier','cashier','billingCashier',0,'3. Cashier & Reservation>Cashier',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier',3101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-10 03:28:03','administrator','2018-07-22 12:58:59',1,0),(37,'Cashier Receipt Setup','dev@wepos.id','v.1.0.0','Cashier Receipt Setup','cashier','cashierReceiptSetup',0,'3. Cashier & Reservation>Cashier Receipt Setup',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Cashier Receipt Setup',3301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-11 06:13:49','administrator','2018-07-22 12:59:09',1,0),(38,'Purchase Order/Pembelian','dev@wepos.id','v.1.0.0','Purchase Order/Pembelian','purchase','purchaseOrder',0,'4. Purchase & Receive>Purchase Order/Pembelian',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Purchase Order/Pembelian',4201,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 03:27:18','administrator','2018-07-15 15:07:08',1,0),(39,'Receiving List/Penerimaan Barang','dev@wepos.id','v.1.0.0','Receiving List/Penerimaan Barang','inventory','receivingList',0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'4. Purchase & Receive>Receiving List/Penerimaan Barang',4301,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:05:57','administrator','2018-07-22 13:04:22',1,0),(40,'Daftar Stok Barang','dev@wepos.id','v.1.0.0','Daftar Stok Barang','inventory','listStock',0,'5. Inventory>Daftar Stok Barang',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Daftar Stok Barang',5101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-24 13:22:20',1,0),(41,'Stock Opname','dev@wepos.id','v.1.0.0','Module Stock Opname','inventory','stockOpname',0,'5. Inventory>Stock Opname',1,'icon-grid','icon-grid','','',1,0,1,0,'5. Inventory>Stock Opname',5401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-10 12:06:05','administrator','2018-07-24 13:22:51',1,0),(42,'Closing Sales','dev@wepos.id','v.1.0.0','Closing Sales','audit_closing','closingSales',0,'8. Closing & Audit>Closing Sales',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Sales',8101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(43,'Closing Purchasing','dev@wepos.id','v.1.0.0','Closing Purchasing','audit_closing','closingPurchasing',0,'8. Closing & Audit>Closing Purchasing',1,'icon-grid','icon-grid','','',1,0,1,0,'8. Closing & Audit>Closing Purchasing',8102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:47:56','administrator','2018-07-03 21:51:27',1,0),(44,'Auto Closing Generator','dev@wepos.id','v.1.0.0','Auto Closing Generator','monitoring','generateAutoClosing',0,'9. Sync, Backup, Generate>Auto Closing Generator',1,'icon-grid','icon-grid','','',1,0,1,0,'9. Sync, Backup, Generate>Auto Closing Generator',9102,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 21:43:42','administrator','2018-07-03 21:43:42',1,0),(45,'Syncronize Master Data Store','dev@wepos.id','v.1.0.0','Syncronize Master Data Store','sync_backup','syncData',0,'9. Sync, Backup, Generate>Syncronize Master Data Store',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Syncronize Master Data Store',9201,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(46,'Backup Transaksi Store','dev@wepos.id','v.1.0.0','Backup Transaksi Store','sync_backup','backupTrx',0,'9. Sync, Backup, Generate>Backup Transaksi Store',1,'icon-backup','icon-backup','','',1,0,1,0,'9. Sync, Backup, Generate>Backup Transaksi Store',9202,'icon-backup','',0,'icon-backup','',1,'icon-backup','',1,'icon-backup','','administrator','2018-07-25 12:17:26','administrator','2018-07-26 21:06:01',1,0),(47,'Sync & Backup','dev@wepos.id','v.1.0.0','Sync & Backup','sync_backup','syncBackup',0,'9. Sync, Backup, Generate>Sync & Backup',1,'icon-sync','icon-sync','','',1,0,1,0,'9. Sync, Backup, Generate>Sync & Backup',9203,'icon-sync','',0,'icon-sync','',1,'icon-sync','',1,'icon-sync','','administrator','2018-07-25 12:14:44','administrator','2018-07-26 21:05:47',1,0),(48,'Sales Report','dev@wepos.id','v.1.0','Sales Report','billing','reportSales',0,'6. Reports>Sales (Billing)>Sales Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report',6101,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 01:28:24','administrator','2018-07-17 17:01:16',1,0),(49,'Sales Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesRecap',0,'6. Reports>Sales (Billing)>Sales Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Report (Recap)',6104,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:30:29','administrator','2018-07-24 16:38:02',1,0),(50,'Sales By Discount','dev@wepos.id','v.1.0.0','Sales By Discount','billing','salesByDiscount',0,'6. Reports>Sales (Billing)>Sales By Discount',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales By Discount',6105,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(51,'Sales Summary Report (SSR)','dev@wepos.id','v.1.0.0','Sales Summary Report (SSR)','billing','salesSummaryReport',0,'6. Reports>Sales (Billing)>Sales Summary Reports (SSR)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Sales Summary Reports (SSR)',6108,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 20:43:42','administrator','2018-07-15 20:43:42',1,0),(52,'Cancel Billing Report','dev@wepos.id','v.1.0.0','','billing','reportCancelBill',0,'6. Reports>Sales (Billing)>Report Cancel Billing',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Billing)>Report Cancel Billing',6113,'icon-grid','',0,'icon-grid','',1,'icon-grid','',0,'icon-grid','','administrator','2018-07-19 09:45:34','administrator','2018-07-24 16:26:54',1,0),(53,'Sales By Menu','dev@wepos.id','v.1.0.0','Sales By Menu','billing','reportSalesByMenu',0,'6. Reports>Sales (Menu)>Sales By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Menu)>Sales By Menu',6120,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 05:51:55','administrator','2018-07-17 17:47:33',1,0),(54,'Sales Profit Report','dev@wepos.id','v.1.0.0','','billing','reportSalesProfit',0,'6. Reports>Sales (Profit)>Sales Profit Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report',6131,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:46:57','administrator','2018-07-24 17:21:51',1,0),(55,'Sales Profit Report (Recap)','dev@wepos.id','v.1.0.0','','billing','reportSalesProfitRecap',0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit Report (Recap)',6134,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:58:17','administrator','2018-07-24 17:23:59',1,0),(56,'Sales Profit By Menu','dev@wepos.id','v.1.0.0','Sales Profit By Menu','billing','reportSalesProfitByMenu',0,'6. Reports>Sales (Profit)>Sales Profit By Menu',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Profit)>Sales Profit By Menu',6135,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-24 16:53:21','administrator','2018-07-17 19:38:07',1,0),(57,'Bagi Hasil','dev@wepos.id','v.1.0.0','Bagi Hasil Detail','billing','reportSalesBagiHasil',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil',6301,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(58,'Bagi Hasil (Recap)','dev@wepos.id','v.1.0.0','Bagi Hasil (Recap)','billing','reportSalesBagiHasilRecap',0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)',6302,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-15 06:43:42','administrator','2018-07-15 06:43:42',1,0),(59,'Purchase Report','dev@wepos.id','v.1.0.0','Purchase Report','purchase','reportPurchase',0,'6. Reports>Purchase/Pembelian>Purchase Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report',6401,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-16 21:28:58','administrator','2018-07-09 19:08:45',1,0),(60,'Purchase Report (Recap)','dev@wepos.id','v.1.0.0','Purchase Report (Recap)','purchase','reportPurchaseRecap',0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Purchase Report (Recap)',6403,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(61,'Last Purchase Price','dev@wepos.id','v.1.0.0','Last Purchase Price','purchase','reportLastPurchasePrice',0,'6. Reports>Purchase/Pembelian>Last Purchase Price',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Purchase/Pembelian>Last Purchase Price',6404,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:23:40','administrator','2018-07-09 19:08:25',1,0),(62,'Receiving Report','dev@wepos.id','v.1.0.0','Receiving Report','inventory','reportReceiving',0,'6. Reports>Receiving (In)>Receiving Report',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report',6501,'icon-grid','',0,'icon-grid','',0,'icon-grid','',0,'icon-grid','','administrator','2018-07-17 13:31:50','administrator','2018-07-09 19:00:32',1,0),(63,'Receiving Report (Recap)','dev@wepos.id','v.1.0.0','Receiving Report (Recap)','inventory','reportReceivingRecap',0,'6. Reports>Receiving (In)>Receiving Report (Recap)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Receiving (In)>Receiving Report (Recap)',6504,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-09 15:57:19','administrator','2018-07-09 19:01:16',1,0),(64,'Monitoring Stock (Actual)','dev@wepos.id','v.1.0.0','Monitoring Stock (Actual)','inventory','reportMonitoringStock',0,'6. Reports>Warehouse>Monitoring Stock (Actual)',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Monitoring Stock (Actual)',6642,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-11 23:44:12','administrator','2018-07-18 00:45:36',1,0),(65,'Kartu Stok','dev@wepos.id','v.1.0.0','Kartu Stok','inventory','kartuStok',0,'6. Reports>Warehouse>Kartu Stock',1,'icon-grid','icon-grid','','',1,0,1,0,'6. Reports>Warehouse>Kartu Stock',6643,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2018-07-03 06:43:42','administrator','2018-07-18 00:46:03',1,0);
/*Table structure for table `apps_modules_method` */

DROP TABLE IF EXISTS `apps_modules_method`;

CREATE TABLE `apps_modules_method` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `method_function` varchar(100) NOT NULL,
 `module_id` int(11) NOT NULL,
 `method_description` varchar(255) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_modules_method` */

/*Table structure for table `apps_modules_preload` */

DROP TABLE IF EXISTS `apps_modules_preload`;

CREATE TABLE `apps_modules_preload` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `preload_filename` varchar(100) NOT NULL,
 `preload_folderpath` varchar(255) DEFAULT NULL,
 `module_id` int(100) NOT NULL,
 `preload_description` varchar(255) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_options` */
insert into `apps_options`(`id`,`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) values (1,'timezone_default','Asia/Jakarta','Timezone Asia/Jakarta','2018-07-08 23:12:43','administrator',NULL,'administrator',1,0),(2,'report_place_default','Bandung',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(3,'input_chinese_text','',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(4,'payment_id_cash','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(5,'payment_id_debit','2',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(6,'payment_id_credit','3',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(7,'warehouse_primary','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(8,'auto_logout_time','3600',NULL,'2018-07-16 12:12:12','administrator',NULL,NULL,1,0),(9,'use_login_pin','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(10,'wepos_tipe','cafe','retail/cafe/foodcourt','2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(11,'retail_warehouse','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(12,'spv_access_active','open_close_cashier,cancel_billing,cancel_order,change_ppn,change_service,change_dp,set_compliment_item,clear_compliment_item',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(13,'supervisor_pin_mode','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(14,'management_systems','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(15,'ipserver_management_systems','',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(16,'multiple_store','0',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(17,'view_multiple_store','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(18,'autobackup_on_settlement','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(19,'wepos_update_version','',NULL,'2018-07-02 19:11:21','',NULL,NULL,1,0),(20,'wepos_update_version2','',NULL,'2018-07-02 19:11:21','',NULL,NULL,1,0),(21,'wepos_update_next_version','',NULL,'2018-07-02 19:13:04','',NULL,NULL,1,0),(22,'wepos_update_next_version2','',NULL,'2018-07-02 19:13:04','',NULL,NULL,1,0),(23,'wepos_connected_id','',NULL,'2018-07-02 19:13:04','',NULL,NULL,1,0),(24,'current_date','1533074400',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(25,'store_connected_id','',NULL,'2018-07-02 19:32:18','',NULL,NULL,1,0),(26,'store_connected_code','',NULL,'2018-07-02 19:32:18','',NULL,NULL,1,0),(27,'print_preview_billing','1',NULL,'2018-07-04 00:00:00','administrator',NULL,NULL,1,0),(28,'big_size_width','1024',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(29,'big_size_height','768',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(30,'thumb_size_width','375',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(31,'thumb_size_height','250',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(32,'tiny_size_width','160',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(33,'tiny_size_height','120',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(34,'big_size_real','1',NULL,'2018-07-13 20:00:00','administrator',NULL,NULL,1,0),(35,'auto_item_code','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(36,'item_code_separator','.',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(37,'item_code_format','{Cat}.{SubCat}.{ItemNo}',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(38,'item_no_length','4',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(39,'so_count_stock','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(40,'ds_count_stock','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(41,'ds_auto_terima','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(42,'auto_add_supplier_item_when_purchasing','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(43,'purchasing_request_order','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(44,'use_approval_po','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(45,'auto_add_supplier_ap','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(46,'receiving_select_warehouse','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(47,'stock_rekap_start_date','01-08-2018',NULL,'2018-07-12 18:00:00','administrator',NULL,NULL,1,0),(48,'persediaan_barang','average','average,fifo','2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(49,'hide_empty_stock_on_report','1',NULL,'2018-07-10 20:00:00','administrator',NULL,NULL,1,0),(50,'approval_change_payment_po_done','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(51,'use_item_sku','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(52,'autocut_stok_sales_to_usage','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(53,'autocut_stok_sales_to_usage_spv','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(54,'item_sku_from_code','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(55,'include_tax','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(56,'include_service','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(57,'role_id_kasir','1,2,5',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(58,'takeaway_no_tax','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(59,'takeaway_no_service','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(60,'use_pembulatan','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(61,'pembulatan_dinamis','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(62,'cashier_pembulatan_keatas','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(63,'cashier_max_pembulatan','100','MAX PEMBULATAN','2018-07-05 11:41:36','',NULL,NULL,1,0),(64,'default_tax_percentage','10','DEF TAX','2018-07-17 22:46:13','administrator','2018-07-10 03:44:35','administrator',1,0),(65,'default_service_percentage','0','DEF SERVICE','2018-07-17 22:46:36','administrator','2018-07-10 03:44:35','administrator',1,0),(66,'table_available_after_paid','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(67,'hide_compliment_order','1',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(68,'hide_takeaway_order_apps','1',NULL,'2018-07-06 14:50:09','administrator',NULL,NULL,1,0),(69,'hide_compliment_order_apps','1',NULL,'2018-07-06 14:50:09','administrator',NULL,NULL,1,0),(70,'use_order_counter','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(71,'order_menu_after_booked_on_tablet','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(72,'order_menu_after_reserved_on_tablet','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(73,'autohold_create_billing','1',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(74,'diskon_sebelum_pajak_service','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(75,'default_discount_payment','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(76,'no_midnight','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(77,'billing_log','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(78,'save_order_note','0',NULL,'2018-07-10 20:00:00','administrator',NULL,NULL,1,0),(79,'order_timer','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(80,'hide_button_invoice','0',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(81,'hide_button_halfpayment','0',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(82,'hide_button_mergebill','1',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(83,'hide_button_splitbill','1',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(84,'hide_button_logoutaplikasi','1',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(85,'set_ta_table_ta','1',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(86,'cashier_credit_ar','0',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(87,'min_noncash','0',NULL,'2018-07-12 20:00:00','administrator',NULL,NULL,1,0),(88,'must_choose_customer','0',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(89,'no_hold_billing','1',NULL,'2018-07-04 00:00:00','administrator',NULL,NULL,1,0),(90,'default_tipe_billing','1',NULL,'2018-07-04 00:00:00','administrator',NULL,NULL,1,0),(91,'reservation_cek_stok','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(92,'reservation_cashier','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(93,'reservation_cashier_spv','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(94,'tujuan_penerimaan_dp_reservation','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(95,'jenis_penerimaan_dp_reservation','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(96,'default_discount_id_reservation','',NULL,'2018-07-02 19:11:13','',NULL,NULL,1,0),(97,'cashierReceipt_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]PHONE: 0812-2254-9676\n[set_tab1b]\n[size=1]NO:{billing_no}[tab]{user}\n[size=0][align=0]--------------------------------\n[set_tab1]\n{order_data}\n[align=0]--------------------------------\n[set_tab2]\n[tab]SUB TOTAL[tab]{subtotal}\n{hide_empty}[tab]PAJAK[tab]{tax_total}\n{hide_empty}[tab]DISC[tab]{potongan}\n{hide_empty}[tab]COMPLIMENT[tab]{compliment}\n{hide_empty}[tab]DP[tab]{dp_total}\n{hide_empty}[tab]PEMBULATAN[tab]{rounded}\n[size=1][tab]GRAND TOTAL[tab]{grand_total}\n[size=0][tab]TUNAI[tab]{cash}\n[tab]KEMBALI[tab]{return}\n[tab]{payment_type}','cashier print receipt layout','2018-07-08 04:20:46','','2018-07-20 17:58:56',NULL,1,0),(98,'kitchenReceipt_layout','PRINT OUT KITCHEN\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}\n','kitchen print layout - order done','2018-07-06 09:47:01','','2018-07-31 12:47:27',NULL,1,0),(99,'cashierReceipt_layout_footer','[align=1][size=1]\n[size=0][align=0]--------------------------------\n[align=1]{date_time}\n\n[align=1]Terima Kasih\n[align=1]Kami Senang Melayani Anda\n\n\n\n','','2018-07-08 02:38:49','','2018-07-26 17:00:55',NULL,1,0),(100,'barReceipt_layout','PRINT OUT BAR\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}\n','bar receipt layout','2018-07-06 09:49:11','administrator','2018-07-31 12:47:27','administrator',1,0),(101,'qcReceipt_layout','PRINT OUT CHECKER\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data_kitchen}\n[size=0][set_tab1]{order_data_bar}','QC receipt layout','2018-07-08 02:51:16','administrator','2018-07-31 12:47:27','administrator',1,0),(102,'cashierReceipt_invoice_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]PHONE: 0812-2254-9676\n[set_tab1b]\n[size=1]NO:{billing_no}[tab]{user}\n[size=0][align=0]--------------------------------\n[set_tab1]\n{order_data}\n[align=0]--------------------------------\n[set_tab2]\n[tab]SUB TOTAL[tab]{subtotal}\n{hide_empty}[tab]PAJAK[tab]{tax_total}\n{hide_empty}[tab]DISC[tab]{potongan}\n{hide_empty}[tab]COMPLIMENT[tab]{compliment}\n{hide_empty}[tab]DP[tab]{dp_total}\n{hide_empty}[tab]PEMBULATAN[tab]{rounded}\n[size=1][tab]GRAND TOTAL[tab]{grand_total}\n','cashier print invoice layout','2018-07-08 04:26:35','administrator','2018-07-20 17:58:56','administrator',1,0),(103,'otherReceipt_layout','PRINT OUT OTHER\n[align=0][size=1]MEJA: {table_no}\n[size=0]date: {date_time}\nuser: {user}\n[size=0][set_tab1]{order_data}',NULL,'2018-07-06 09:16:23','administrator',NULL,NULL,1,0),(104,'cashierReceipt_bagihasil_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]{supplier_name}\n\n[set_tab1b]\n[size=1]{tanggal_shift} {jam_shift}[tab]\n[size=0][align=0]--------------------------------\n[set_tab3]\n{sales_data}\n[align=0]--------------------------------\n[set_tab1]\n[size=0]TOTAL ITEM[tab]{total_qty}\nTOTAL SALES[tab]{total_sales}\nTOTAL TOKO[tab]{total_toko}\nTOTAL SUPPLIER[tab]{total_supplier}\n\n',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(105,'cashierReceipt_settlement_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]PHONE: 0812-2254-9676\n\n[align=1]SETTLEMENT\n[set_tab1b]\n[align=0][size=0]{tanggal_shift} {jam_shift}[tab]\n[align=0][size=0]--------------------------------\n[set_tab3]\n{summary_data}\n[align=0][size=0]--------------------------------\n[set_tab3]\n[align=0]{payment_data}',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(106,'cashierReceipt_openclose_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]PHONE: 0812-2254-9676\n\n[set_tab1b]\n[align=0][size=0]{tipe_openclose}: {shift_on}[tab]\n[align=0][size=0]{tanggal_shift} {jam_shift}[tab]\n[size=0][align=0]--------------------------------\n[set_tab3]\n{uang_kertas_data}\n{uang_koin_data}{summary_data}\n[align=0]--------------------------------\n[set_tab3]\n[align=0]{payment_data}approved: {spv_user}\n\n\n',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(107,'reservationReceipt_layout','[align=1][size=1]WePOS Cafe\n[align=1][size=0]JL.Kebon Sirih Dalam\n[align=1]PHONE: 0812-2254-9676\n[set_tab1b]\n[size=1]NO:{billing_no}[tab]{user}\n[size=0][align=0]--------------------------------\n[set_tab1]\n{order_data}\n[align=0]--------------------------------\n[set_tab2]\n[tab]SUB TOTAL[tab]{subtotal}\n{hide_empty}[tab]PAJAK[tab]{tax_total}\n{hide_empty}[tab]DISC[tab]{potongan}\n{hide_empty}[tab]COMPLIMENT[tab]{compliment}\n{hide_empty}[tab]DP[tab]{dp_total}\n{hide_empty}[tab]PEMBULATAN[tab]{rounded}\n[size=1][tab]GRAND TOTAL[tab]{grand_total}\n','cashier print receipt layout','2018-07-08 04:20:46','','2018-07-20 17:58:56',NULL,1,0),(108,'print_chinese_text','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(109,'print_order_peritem_kitchen','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(110,'print_order_peritem_bar','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(111,'print_order_peritem_other','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(112,'printMonitoring_qc','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(113,'printMonitoring_kitchen','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(114,'printMonitoring_bar','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(115,'printMonitoring_other','0',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(116,'printMonitoringTime_qc','2000',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(117,'printMonitoringTime_kitchen','2000',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(118,'printMonitoringTime_bar','2000',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(119,'printMonitoringTime_other','2000',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(120,'cleanPrintMonitoring','06:00',NULL,'2018-07-23 19:00:00','administrator',NULL,NULL,1,0),(121,'show_multiple_print_qc','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(122,'multiple_print_qc','1',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(123,'print_qc_then_order','1',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(124,'show_multiple_print_billing','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(125,'multiple_print_billing','1',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(126,'print_qc_order_when_payment','0',NULL,'2018-07-11 20:00:00','administrator',NULL,NULL,1,0),(127,'do_print_cashierReceipt_default','1',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(128,'printer_tipe_cashierReceipt_default','EPSON',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(129,'printer_pin_cashierReceipt_default','32 CHAR',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(130,'printer_ip_cashierReceipt_default','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(131,'printer_id_cashierReceipt_default','1',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(132,'do_print_qcReceipt_default','1',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(133,'printer_tipe_qcReceipt_default','EPSON',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(134,'printer_pin_qcReceipt_default','32 CHAR',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(135,'printer_ip_qcReceipt_default','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(136,'printer_id_qcReceipt_default','1',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(137,'do_print_kitchenReceipt_default','1',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(138,'printer_tipe_kitchenReceipt_default','EPSON',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(139,'printer_pin_kitchenReceipt_default','32 CHAR',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(140,'printer_ip_kitchenReceipt_default','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(141,'printer_id_kitchenReceipt_default','1',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(142,'do_print_barReceipt_default','1',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(143,'printer_tipe_barReceipt_default','EPSON',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(144,'printer_pin_barReceipt_default','42 CHAR',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(145,'printer_ip_barReceipt_default','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(146,'printer_id_barReceipt_default','1',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(147,'do_print_otherReceipt_default','1',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(148,'printer_tipe_otherReceipt_default','EPSON',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(149,'printer_pin_otherReceipt_default','32 CHAR',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(150,'printer_ip_otherReceipt_default','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(151,'printer_id_otherReceipt_default','1',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(152,'do_print_cashierReceipt_127.0.0.1','1',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(153,'printer_tipe_cashierReceipt_127.0.0.1','EPSON',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(154,'printer_pin_cashierReceipt_127.0.0.1','32 CHAR',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(155,'printer_ip_cashierReceipt_127.0.0.1','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(156,'printer_id_cashierReceipt_127.0.0.1','1',NULL,'2018-07-28 19:57:55','administrator','2018-07-28 19:57:55','administrator',1,0),(157,'do_print_qcReceipt_127.0.0.1','1',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(158,'printer_tipe_qcReceipt_127.0.0.1','EPSON',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(159,'printer_pin_qcReceipt_127.0.0.1','32 CHAR',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(160,'printer_ip_qcReceipt_127.0.0.1','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(161,'printer_id_qcReceipt_127.0.0.1','1',NULL,'2018-07-05 00:11:02','administrator','2018-07-05 00:11:02','administrator',1,0),(162,'do_print_kitchenReceipt_127.0.0.1','1',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(163,'printer_tipe_kitchenReceipt_127.0.0.1','EPSON',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(164,'printer_pin_kitchenReceipt_127.0.0.1','32 CHAR',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(165,'printer_ip_kitchenReceipt_127.0.0.1','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(166,'printer_id_kitchenReceipt_127.0.0.1','1',NULL,'2018-07-05 00:09:35','administrator','2018-07-05 00:09:35','administrator',1,0),(167,'do_print_barReceipt_127.0.0.1','1',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(168,'printer_tipe_barReceipt_127.0.0.1','EPSON',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(169,'printer_pin_barReceipt_127.0.0.1','42 CHAR',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(170,'printer_ip_barReceipt_127.0.0.1','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(171,'printer_id_barReceipt_127.0.0.1','1',NULL,'2018-07-28 19:30:31','administrator','2018-07-28 19:30:31','administrator',1,0),(172,'do_print_otherReceipt_127.0.0.1','1',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(173,'printer_tipe_otherReceipt_127.0.0.1','EPSON',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(174,'printer_pin_otherReceipt_127.0.0.1','32 CHAR',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(175,'printer_ip_otherReceipt_127.0.0.1','PC-NAME\\PRINTER-SHARE-NAME',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(176,'printer_id_otherReceipt_127.0.0.1','1',NULL,'2018-07-05 00:09:24','administrator','2018-07-05 00:09:24','administrator',1,0),(177,'closing_sales_start_date','01-08-2018',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(178,'closing_purchasing_start_date','01-08-2018',NULL,'2018-07-09 00:00:00','',NULL,NULL,1,0),(179,'closing_inventory_start_date','01-08-2018',NULL,'2018-07-02 12:00:00','',NULL,NULL,1,0),(180,'closing_accounting_start_date','01-08-2018',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(181,'autoclosing_generate_sales','1',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(182,'autoclosing_generate_purchasing','1',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(183,'autoclosing_generate_inventory','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(184,'autoclosing_generate_stock','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(185,'autoclosing_generate_accounting','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(186,'autoclosing_closing_sales','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(187,'autoclosing_closing_purchasing','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(188,'autoclosing_closing_inventory','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(189,'autoclosing_closing_accounting','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(190,'autoclosing_auto_cancel_billing','1',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(191,'autoclosing_auto_cancel_receiving','1',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(192,'autoclosing_auto_cancel_distribution','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(193,'autoclosing_auto_cancel_production','0',NULL,'2018-07-08 12:43:06','',NULL,NULL,1,0),(194,'autoclosing_skip_open_jurnal','0',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(195,'autoclosing_generate_timer','360000',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(196,'autoclosing_closing_time','03:00',NULL,'2018-07-01 12:00:43','',NULL,NULL,1,0),(197,'account_payable_non_accounting','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(198,'account_receivable_non_accounting','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(199,'cashflow_receivable_non_accounting','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0),(200,'cashflow_non_accounting','0',NULL,'2018-07-06 20:00:00','administrator',NULL,NULL,1,0);

/*Table structure for table `apps_roles` */

DROP TABLE IF EXISTS `apps_roles`;

CREATE TABLE `apps_roles` ( `id` smallint(6) NOT NULL AUTO_INCREMENT, `role_name` char(50) NOT NULL, `role_description` char(100) DEFAULT NULL, `role_window_mode` enum('full','lite') DEFAULT 'full', `client_id` tinyint(4) NOT NULL, `createdby` char(50) DEFAULT NULL, `created` timestamp NULL DEFAULT NULL, `updatedby` char(50) DEFAULT NULL, `updated` timestamp NULL DEFAULT NULL, `is_active` tinyint(1) NOT NULL DEFAULT '1', `is_deleted` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `apps_roles` */insert into `apps_roles`(`id`,`role_name`,`role_description`,`role_window_mode`,`client_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Super Admin','Super Admin Roles','full',1,'administrator','2014-08-11 21:20:00','admin','2018-08-01 15:48:40',1,0),(2,'Apps Admin','Application Admin','full',1,'administrator','2014-08-11 21:22:25','admin','2018-08-01 15:48:48',1,0),(3,'Purchasing','','full',1,'administrator','2016-10-17 10:45:20','admin','2018-08-01 15:50:05',1,0),(4,'Inventory','','full',1,'administrator','2016-10-17 10:46:13','admin','2018-08-01 15:50:32',1,0),(5,'Cashier','','full',1,'administrator','2016-10-17 10:47:48','admin','2018-08-01 15:51:30',1,0);

/*Table structure for table `apps_roles_module` */

DROP TABLE IF EXISTS `apps_roles_module`;

CREATE TABLE `apps_roles_module` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `role_id` int(11) NOT NULL,
 `module_id` int(11) NOT NULL,
 `start_menu_path` varchar(255) DEFAULT NULL,
 `module_order` int(11) DEFAULT '0',
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 KEY `FK_idi_group_rule_list` (`module_id`),
 KEY `FK_idi_group_rule_list2` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `apps_roles_module` */insert into `apps_roles_module`(`id`,`role_id`,`module_id`,`start_menu_path`,`module_order`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,43,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(2,1,42,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(3,1,57,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(4,1,58,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(5,1,52,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(6,1,50,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(7,1,53,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(8,1,56,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(9,1,54,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(10,1,55,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(11,1,48,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(12,1,49,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(13,1,51,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(14,1,36,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(15,1,37,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(16,1,34,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(17,1,35,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(18,1,33,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(19,1,40,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(20,1,65,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(21,1,64,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(22,1,39,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(23,1,62,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(24,1,63,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(25,1,41,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(26,1,23,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(27,1,20,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(28,1,21,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(29,1,25,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(30,1,26,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(31,1,22,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(32,1,16,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(33,1,27,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(34,1,19,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(35,1,28,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(36,1,18,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(37,1,17,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(38,1,15,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(39,1,31,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(40,1,24,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(41,1,32,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(42,1,29,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(43,1,30,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(44,1,44,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(45,1,61,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(46,1,38,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(47,1,59,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(48,1,60,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(49,1,46,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(50,1,47,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(51,1,45,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(52,1,2,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(53,1,3,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(54,1,4,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(55,1,6,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(56,1,8,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(57,1,11,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(58,1,12,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(59,1,14,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(60,1,9,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(61,1,10,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(62,1,5,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(63,1,1,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(64,1,7,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(65,1,13,NULL,0,'admin','2018-08-01 16:46:52','admin','2018-08-01 16:46:52',1,0),(66,2,43,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(67,2,42,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(68,2,57,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(69,2,58,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(70,2,52,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(71,2,50,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(72,2,53,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(73,2,56,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(74,2,54,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(75,2,55,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(76,2,48,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(77,2,49,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(78,2,51,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(79,2,36,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(80,2,37,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(81,2,34,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(82,2,35,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(83,2,33,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(84,2,40,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(85,2,65,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(86,2,64,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(87,2,39,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(88,2,62,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(89,2,63,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(90,2,41,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(91,2,23,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(92,2,20,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(93,2,21,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(94,2,25,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(95,2,26,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(96,2,22,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(97,2,16,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(98,2,27,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(99,2,19,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(100,2,28,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(101,2,18,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(102,2,17,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(103,2,15,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(104,2,31,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(105,2,24,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(106,2,32,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(107,2,29,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(108,2,30,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(109,2,44,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(110,2,61,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(111,2,38,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(112,2,59,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(113,2,60,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(114,2,46,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(115,2,47,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(116,2,45,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(117,2,2,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(118,2,3,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(119,2,4,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(120,2,6,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(121,2,8,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(122,2,11,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(123,2,12,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(124,2,14,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(125,2,9,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(126,2,10,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(127,2,5,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(128,2,1,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(129,2,7,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(130,2,13,NULL,0,'admin','2018-08-01 16:46:59','admin','2018-08-01 16:46:59',1,0),(131,3,43,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(132,3,7,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(133,3,10,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(134,3,9,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(135,3,14,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(136,3,12,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(137,3,8,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(138,3,61,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(139,3,38,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(140,3,59,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(141,3,60,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(142,3,20,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(143,3,21,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(144,3,22,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(145,3,18,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(146,3,40,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(147,3,65,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(148,3,64,NULL,0,'admin','2018-08-01 16:49:34','admin','2018-08-01 16:49:34',1,0),(149,4,40,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(150,4,65,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(151,4,64,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(152,4,39,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(153,4,62,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(154,4,63,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(155,4,41,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(156,4,8,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(157,4,12,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(158,4,14,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(159,4,9,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(160,4,10,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(161,4,7,NULL,0,'admin','2018-08-01 16:50:04','admin','2018-08-01 16:50:04',1,0),(162,5,42,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(163,5,7,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(164,5,10,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(165,5,9,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(166,5,14,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(167,5,12,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(168,5,8,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(169,5,33,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(170,5,35,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(171,5,34,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(172,5,36,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(173,5,51,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(174,5,48,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(175,5,49,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(176,5,53,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(177,5,50,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0),(178,5,52,NULL,0,'admin','2018-08-01 16:51:04','admin','2018-08-01 16:51:04',1,0);
/*Table structure for table `apps_roles_widget` */

DROP TABLE IF EXISTS `apps_roles_widget`;

CREATE TABLE `apps_roles_widget` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `role_id` int(11) NOT NULL,
 `widget_id` int(11) NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
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
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_supervisor` */
insert into `apps_supervisor`(`id`,`user_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,'administrator','2014-08-16 11:55:26','administrator','2014-08-10 10:44:35',1,0),(2,2,'administrator','2016-07-25 17:06:00','administrator','2016-07-25 17:06:00',1,0);

/*Table structure for table `apps_supervisor_access` */

DROP TABLE IF EXISTS `apps_supervisor_access`;

CREATE TABLE `apps_supervisor_access` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `supervisor_id` int(25) NOT NULL,
 `supervisor_access` varchar(100) NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `apps_supervisor_access` */insert into `apps_supervisor_access`(`id`,`supervisor_id`,`supervisor_access`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,2,'cancel_billing','admin','2018-08-01 14:58:46','admin','2018-08-01 14:58:46',1,0),(2,2,'cancel_order','admin','2018-08-01 14:58:54','admin','2018-08-01 14:58:54',1,0),(3,2,'set_compliment_item','admin','2018-08-01 14:59:13','admin','2018-08-01 14:59:13',1,0),(4,2,'clear_compliment_item','admin','2018-08-01 14:59:23','admin','2018-08-01 14:59:23',1,0),(5,2,'open_close_cashier','admin','2018-08-01 14:59:32','admin','2018-08-01 14:59:32',1,0);
/*Table structure for table `apps_supervisor_log` */

DROP TABLE IF EXISTS `apps_supervisor_log`;

CREATE TABLE `apps_supervisor_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `supervisor_id` int(25) NOT NULL,
 `supervisor_access_id` int(11) DEFAULT NULL,
 `supervisor_access` varchar(100) DEFAULT NULL,
 `log_data` text NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `ref_id_1` varchar(50) DEFAULT '',
 `ref_id_2` varchar(50) DEFAULT '',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_supervisor_log` */

/*Table structure for table `apps_users` */

DROP TABLE IF EXISTS `apps_users`;

CREATE TABLE `apps_users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_username` varchar(50) NOT NULL,
 `user_password` varchar(64) NOT NULL,
 `role_id` int(11) NOT NULL,
 `user_firstname` varchar(100) NOT NULL,
 `user_lastname` varchar(100) DEFAULT NULL,
 `user_email` varchar(50) DEFAULT NULL,
 `user_phone` varchar(50) DEFAULT NULL,
 `user_mobile` varchar(50) DEFAULT NULL,
 `user_address` varchar(255) DEFAULT NULL,
 `client_id` int(11) NOT NULL DEFAULT '1',
 `client_structure_id` int(11) NOT NULL,
 `avatar` varchar(255) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `user_pin` char(8) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `user_username` (`user_username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users` */
insert into `apps_users`(`id`,`user_username`,`user_password`,`role_id`,`user_firstname`,`user_lastname`,`user_email`,`user_phone`,`user_mobile`,`user_address`,`client_id`,`client_structure_id`,`avatar`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`user_pin`) values (1,'administrator','e10adc3949ba59abbe56e057f20f883e',1,'Super','Admin','contact@wepos.id','6281222549676','1231239990111','Bandung - West Java - Indonesia',1,1,'0','administrator','2018-07-17 08:00:00','administrator','2018-07-17 08:00:00',1,0,'9999'),(2,'admin','e10adc3949ba59abbe56e057f20f883e',2,'Admin','WePOS','contact@wepos.id','','','',1,2,'0','administrator','2018-07-17 08:00:00','administrator','2018-07-17 08:00:00',1,0,'1234'),(3,'kasir','e10adc3949ba59abbe56e057f20f883e',5,'Kasir','WePOS','contact@wepos.id',NULL,NULL,NULL,1,7,'0','administrator','2018-07-17 08:00:00','administrator','2018-07-17 08:00:00',1,0,'7890');

/*Table structure for table `apps_users_desktop` */

DROP TABLE IF EXISTS `apps_users_desktop`;

CREATE TABLE `apps_users_desktop` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `dock` enum('top','bottom','left','right') NOT NULL DEFAULT 'bottom',
 `window_mode` enum('full','lite') DEFAULT 'full',
 `wallpaper` varchar(255) NOT NULL DEFAULT 'default.jpg',
 `wallpaperStretch` tinyint(1) NOT NULL DEFAULT '0',
 `wallpaper_id` int(11) NOT NULL DEFAULT '1',
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `apps_users_desktop` */

insert into `apps_users_desktop`(`id`,`user_id`,`dock`,`window_mode`,`wallpaper`,`wallpaperStretch`,`wallpaper_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,1,'bottom','full','default.jpg',1,1,'','2014-07-02 07:21:35',NULL,NULL,1,0),(2,2,'bottom','full','default.jpg',0,1,NULL,NULL,NULL,NULL,1,0),(3,3,'bottom','full','default.jpg',0,1,NULL,NULL,NULL,NULL,1,0);

/*Table structure for table `apps_users_quickstart` */

DROP TABLE IF EXISTS `apps_users_quickstart`;

CREATE TABLE `apps_users_quickstart` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_id` int(11) NOT NULL,
 `module_id` int(11) NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
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
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
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
 `widget_name` varchar(255) NOT NULL,
 `widget_author` varchar(100) DEFAULT NULL,
 `widget_version` varchar(10) DEFAULT NULL,
 `widget_description` varchar(255) DEFAULT NULL,
 `widget_controller` varchar(255) NOT NULL,
 `widget_order` int(5) DEFAULT '0',
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `module_controller` (`widget_controller`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `apps_widgets` *//*Table structure for table `pos_bank` */

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_bank` */

insert into `pos_bank`(`id`,`bank_code`,`bank_name`,`payment_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'A1','DEBIT',2,'administrator','2017-03-31 14:29:28','administrator','2017-03-31 14:29:28',1,0),(2,'A2','CC VISA',3,'administrator','2017-03-31 14:29:44','administrator','2017-03-31 14:29:44',1,0),(3,'A3','CC MASTERCARD',3,'administrator','2017-03-31 14:30:05','administrator','2017-03-31 14:30:05',1,0),(4,'B1','BCA',2,'administrator','2017-03-31 14:30:22','administrator','2017-03-31 14:30:22',1,0),(5,'B2','BCA FLAZZ',2,'administrator','2017-03-31 14:30:43','administrator','2017-03-31 14:30:43',1,0),(6,'B3','BCA MASTERCARD',3,'administrator','2017-03-31 14:31:06','administrator','2017-03-31 14:31:06',1,0),(7,'B4','BCA VISA',3,'administrator','2017-03-31 14:31:24','administrator','2017-03-31 14:31:24',1,0),(8,'C1','BNI',2,'administrator','2017-03-31 14:31:44','administrator','2017-03-31 14:31:44',1,0),(9,'C2','BNI VISA',3,'administrator','2017-03-31 14:32:00','administrator','2017-03-31 14:32:00',1,0),(10,'C3','BNI MASTERCARD',3,'administrator','2017-03-31 14:32:20','administrator','2017-03-31 14:32:20',1,0),(11,'D1','MANDIRI',2,'administrator','2017-05-26 14:38:07','administrator','2017-05-26 14:38:07',1,0),(12,'D2','MANDIRI VISA',3,'administrator','2017-05-26 14:38:24','administrator','2017-05-26 14:38:24',1,0),(13,'D3','MANDIRI MASTERCARD',3,'administrator','2017-05-26 14:38:24','administrator','2017-05-26 14:38:24',1,0),(14,'E1','T-Cash',2,'administrator','2017-10-23 11:49:50','administrator','2017-10-23 11:49:50',1,0),(15,'E2','OVO',2,'administrator','2017-10-23 11:49:50','administrator','2017-10-23 11:49:50',1,0);

/*Table structure for table `pos_billing` */

DROP TABLE IF EXISTS `pos_billing`;

CREATE TABLE `pos_billing` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `billing_no` varchar(20) NOT NULL,
 `table_id` mediumint(9) DEFAULT NULL, `table_no` char(20) DEFAULT NULL,
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
 `block_table` tinyint(1) DEFAULT '0',
 `is_reservation` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `billing_no` (`billing_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
 `is_kerjasama` tinyint(1) DEFAULT '0',
 `supplier_id` int(11) DEFAULT '0',
 `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
 `total_bagi_hasil` double DEFAULT '0',
 `grandtotal_bagi_hasil` double DEFAULT '0',
 `use_stok_kode_unik` tinyint(1) DEFAULT '0',
 `data_stok_kode_unik` text,
 `product_type` enum('item','package') DEFAULT 'item',
 `package_item` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
 `is_kerjasama` tinyint(1) DEFAULT '0',
 `supplier_id` int(11) DEFAULT '0',
 `persentase_bagi_hasil` decimal(5,2) DEFAULT '0.00',
 `total_bagi_hasil` double DEFAULT '0',
 `grandtotal_bagi_hasil` double DEFAULT '0',
 `use_stok_kode_unik` tinyint(1) DEFAULT '0',
 `data_stok_kode_unik` text,
 `product_type` enum('item','package') DEFAULT 'item',
 `package_item` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_billing_detail_split` */

/*Table structure for table `pos_billing_detail_timer` */

DROP TABLE IF EXISTS `pos_billing_detail_timer`;

CREATE TABLE `pos_billing_detail_timer` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `bild_id` int(11) NOT NULL,
 `order_start` datetime DEFAULT NULL,
 `order_done` datetime DEFAULT NULL,
 `order_time` double DEFAULT NULL,
 `done_by` varchar(50) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) DEFAULT '1',
 `is_deleted` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_billing_detail_timer` */

/*Table structure for table `pos_billing_log` */

DROP TABLE IF EXISTS `pos_billing_log`;

CREATE TABLE `pos_billing_log` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `billing_id` int(25) NOT NULL,
 `trx_type` varchar(20) DEFAULT NULL,
 `trx_info` varchar(255) DEFAULT NULL,
 `log_data` text NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_closing` *//*Table structure for table `pos_closing_inventory` */DROP TABLE IF EXISTS `pos_closing_inventory`;CREATE TABLE `pos_closing_inventory` ( `id` int(11) NOT NULL AUTO_INCREMENT, `tanggal` date DEFAULT NULL, `inventory_item` smallint(6) DEFAULT '0', `inventory_in_qty` float DEFAULT '0', `inventory_in_hpp` float DEFAULT '0', `inventory_out_qty` float DEFAULT '0', `inventory_out_hpp` float DEFAULT '0', `inventory_stok` float DEFAULT '0', `inventory_hpp` double DEFAULT '0', `receiving_total` smallint(6) DEFAULT '0', `receiving_item_total` smallint(6) DEFAULT '0', `receiving_item_qty` float DEFAULT '0', `receiving_item_hpp` double DEFAULT '0', `usage_total` smallint(6) DEFAULT '0', `usage_item_total` smallint(6) DEFAULT '0', `usage_item_qty` float DEFAULT '0', `usage_item_hpp` double DEFAULT '0', `waste_total` smallint(6) DEFAULT '0', `waste_item_total` smallint(6) DEFAULT '0', `waste_item_qty` float DEFAULT '0', `waste_item_hpp` double DEFAULT '0', `waste_persentase` decimal(5,2) DEFAULT '0.00', `mutasi_total` smallint(6) DEFAULT '0', `mutasi_item_total` smallint(6) DEFAULT '0', `mutasi_item_qty` float DEFAULT '0', `mutasi_item_hpp` double DEFAULT '0', `createdby` varchar(50) DEFAULT NULL, `created` timestamp NULL DEFAULT NULL, `updatedby` varchar(50) DEFAULT NULL, `updated` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `pos_closing_inventory` *//*Table structure for table `pos_closing_log` */

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
 `keterangan_blacklist` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_discount` */

insert into `pos_discount`(`id`,`discount_name`,`discount_percentage`,`discount_price`,`min_total_billing`,`discount_date_type`,`discount_product`,`discount_desc`,`date_start`,`date_end`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_discount_billing`,`discount_max_price`,`discount_type`,`is_promo`,`discount_allow_day`,`use_discount_time`,`discount_time_start`,`discount_time_end`,`is_buy_get`,`is_sistem_tawar`) values (1,'DISCOUNT VIP','15.00',0,0,'unlimited_date',NULL,'','0000-00-00 00:00:00','0000-00-00 00:00:00','administrator','2017-01-23 10:33:12','administrator','2017-11-01 16:52:43',1,0,0,0,0,0,0,0,'9:00 AM','12:20 AM',0,0),(2,'DISCOUNT KARYAWAN','25.00',0,0,'unlimited_date',NULL,'','0000-00-00 00:00:00','0000-00-00 00:00:00','administrator','2017-03-22 11:53:50','admin','2018-07-18 02:45:07',1,0,0,0,0,0,0,0,'12:00 AM','12:00 AM',0,0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_divisi` */

insert into `pos_divisi`(`id`,`divisi_name`,`divisi_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Accounting','Accounting Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:01:24',1,0),(2,'Kitchen','Kitchen Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:01:42',1,0),(3,'Manager','Manager Department','administrator','2014-12-11 16:00:56','administrator','2014-12-11 16:00:56',1,0),(4,'Supervisor','Supervisor Department','administrator','2014-12-11 16:02:08','administrator','2014-12-11 16:02:08',1,0),(5,'Bar','Bar Department','administrator','2014-12-11 16:02:32','administrator','2014-12-11 16:02:32',1,0),(6,'Kasir','Kasir Department','administrator','2014-12-11 16:02:47','administrator','2014-12-11 16:02:47',1,0),(7,'Service','Service Department','administrator','2014-12-11 16:03:06','administrator','2014-12-11 16:03:06',1,0),(8,'Steward','Steward Department','administrator','2014-12-11 16:03:34','administrator','2014-12-11 16:03:34',1,0),(9,'Security','Security Department','administrator','2014-12-11 16:03:51','administrator','2014-12-11 16:03:51',1,0),(10,'Housekeeping','Housekeeping Department','administrator','2014-12-11 16:04:15','administrator','2014-12-11 16:04:15',1,0),(11,'Admin','Admin Department','administrator','2014-12-11 16:04:32','administrator','2014-12-11 16:04:32',1,0),(12,'Marketing','Marketing Department','administrator','2014-12-11 16:04:47','administrator','2014-12-11 16:04:47',1,0),(13,'Purcashing','Purcashing Department','administrator','2014-12-17 17:58:36','administrator','2014-12-17 17:58:36',1,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_floorplan` */

insert into `pos_floorplan`(`id`,`floorplan_name`,`floorplan_desc`,`floorplan_image`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Take Away','','','administrator','2016-07-25 15:07:25','administrator','2016-07-25 15:07:25',1,0),(2,'Non-Smoking','','','administrator','2016-07-25 15:07:36','administrator','2016-07-25 15:07:36',1,0),(3,'Smoking','','','administrator','2016-07-25 15:12:28','administrator','2016-07-25 15:14:10',1,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_item_category` */

insert into `pos_item_category`(`id`,`item_category_name`,`item_category_code`,`item_category_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'BAHAN MENTAH','A','','admin','2017-10-15 11:11:56','admin','2017-10-15 11:12:28',1,0),(2,'BAHAN SETENGAH JADI','B','','admin','2017-10-15 11:12:21','admin','2017-10-15 11:12:21',1,0),(3,'BAHAN MATANG','C','','admin','2017-10-15 11:13:56','admin','2017-10-15 11:13:56',1,0);

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
 `item_hpp` double DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_item_subcategory` */

insert into `pos_item_subcategory`(`id`,`item_subcategory_name`,`item_subcategory_code`,`item_subcategory_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (19,'Daging','SB1','','administrator','2017-09-01 16:56:03','admin','2018-07-18 02:32:03',1,0);

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
 `min_stock` float DEFAULT '0',
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
 `item_sku` varchar(50) DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `item_code` (`item_code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_items` */

insert into `pos_items`(`id`,`item_code`,`item_name`,`item_type`,`item_manufacturer`,`item_desc`,`item_image`,`item_price`,`category_id`,`unit_id`,`supplier_id`,`item_hpp`,`last_in`,`old_last_in`,`min_stock`,`total_qty_stok`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`sales_price`,`use_for_sales`,`id_ref_product`,`sales_use_tax`,`sales_use_service`,`is_kerjasama`,`persentase_bagi_hasil`,`total_bagi_hasil`,`subcategory_id`,`item_no`,`use_stok_kode_unik`,`item_sku`) values (1,'A.SB1.0001','Wings Hot Cripsy','main',NULL,'',NULL,10000,3,7,0,10000,10000,10000,10,-100,'admin','2017-10-15 11:32:08','admin','2018-07-18 02:32:40',1,0,0,0,0,0,0,0,'0.00',0,19,1,0,''),(2,'A.SB1.0002','Daging Ayam','support',NULL,'',NULL,10000,1,1,0,10000,10000,10000,10,-100,'administrator','2017-11-28 15:32:00','admin','2018-07-18 02:30:16',1,0,0,0,0,0,0,0,'0.00',0,19,2,0,'');

/*Table structure for table `pos_ooo_menu` */

DROP TABLE IF EXISTS `pos_ooo_menu`;

CREATE TABLE `pos_ooo_menu` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `product_id` int(11) DEFAULT NULL,
 `tanggal` date DEFAULT NULL,
 `keterangan` varchar(100) DEFAULT NULL,
 `created` datetime DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `updated` datetime DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `is_active` tinyint(1) DEFAULT '0',
 `is_deleted` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_ooo_menu` */

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_open_close_shift` */

/*Table structure for table `pos_order_note` */

DROP TABLE IF EXISTS `pos_order_note`;

CREATE TABLE `pos_order_note` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `order_note_text` varchar(255) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_order_note` */

insert into `pos_order_note`(`id`,`order_note_text`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Tanpa MSG/Mecin','admin','2018-07-18 02:24:02','admin','2018-07-18 02:24:02',1,0),(2,'No Ice','admin','2018-07-18 02:24:13','admin','2018-07-18 02:24:13',1,0),(3,'Tanpa Gula','admin','2018-07-18 02:24:22','admin','2018-07-18 02:24:22',1,0),(4,'Gula Sedikit','admin','2018-07-18 02:24:31','admin','2018-07-18 02:24:31',1,0),(5,'Es Sedikit','admin','2018-07-18 02:24:44','admin','2018-07-18 02:24:44',1,0),(6,'Tidak Pedas','admin','2018-07-18 02:25:14','admin','2018-07-18 02:25:42',1,0),(7,'Pedas Medium','admin','2018-07-18 02:25:24','admin','2018-07-18 02:25:49',1,0),(8,'Pedas Banget','admin','2018-07-18 02:25:33','admin','2018-07-18 02:25:33',1,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_payment_type` */

insert into `pos_payment_type`(`id`,`payment_type_name`,`payment_type_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Cash','Paid by Cash','administrator','2014-06-28 03:32:50','administrator','0000-00-00 00:00:00',1,0),(2,'Debit Card','Paid by Debit Card','administrator','2014-06-28 03:32:50','administrator','2014-06-28 03:32:50',1,0),(3,'Credit Card','Paid by Credit Card','administrator','2014-06-28 03:32:50','administrator','2014-06-28 03:32:50',1,0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_print_monitoring` */

/*Table structure for table `pos_printer` */

DROP TABLE IF EXISTS `pos_printer`;

CREATE TABLE `pos_printer` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `printer_ip` varchar(255) DEFAULT NULL,
 `printer_name` varchar(255) DEFAULT NULL,
 `printer_tipe` varchar(30) DEFAULT NULL,
 `printer_pin` varchar(10) DEFAULT NULL,
 `is_print_anywhere` tinyint(1) DEFAULT '0',
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `print_method` enum('ESC/POS','JSPRINT','BROWSER') DEFAULT 'ESC/POS',
 `print_logo` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_printer` */

insert into `pos_printer`(`id`,`printer_ip`,`printer_name`,`printer_tipe`,`printer_pin`,`is_print_anywhere`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`print_method`,`print_logo`) values (1,'NAMA-PC\\Kitchen','Kitchen','EPSON','48 CHAR',1,'administrator','2016-07-25 14:23:35','admin','2018-07-18 02:52:03',1,0,'ESC/POS',0),(2,'NAMA-PC\\Kasir','Kasir dan Bar','EPSON','48 CHAR',1,'administrator','2016-07-28 10:38:41','admin','2018-07-18 02:51:59',1,0,'ESC/POS',0);

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
 `product_chinese_name` varchar(255) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_product` */

insert into `pos_product`(`id`,`product_name`,`product_desc`,`product_price`,`product_hpp`,`product_image`,`product_type`,`product_group`,`category_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`product_chinese_name`,`price_include_tax`,`price_include_service`,`discount_manual`,`has_varian`,`normal_price`,`use_tax`,`use_service`,`from_item`,`id_ref_item`,`is_kerjasama`,`persentase_bagi_hasil`,`total_bagi_hasil`,`supplier_id`) values (1,'Mineral Water ','',7000,1250,NULL,'item','beverage',1,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:23:27',1,0,NULL,0,0,1,0,7000,1,1,0,0,0,'0.00',0,0),(2,'Rice Bowl Ayam Katsu Teriyaki','',31000,16079,NULL,'item','food',2,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:23:37',1,0,NULL,0,0,1,0,31000,1,1,0,0,0,'0.00',0,0),(3,'Rice Bowl Sapi Teriyaki','',31000,16919,NULL,'item','food',2,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:23:17',1,0,NULL,0,0,1,0,31000,1,1,0,0,0,'0.00',0,0),(4,'Rice Bowl Ayam Geprek','',31000,14837,NULL,'item','food',2,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:22:51',1,0,NULL,0,0,1,0,31000,1,1,0,0,0,'0.00',0,0),(5,'Americano (H)','',18000,3825,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:22:35',1,0,NULL,0,0,1,0,18000,1,1,0,0,0,'0.00',0,0),(6,'Cappuccino (H)','',22000,6218,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:22:23',1,0,NULL,0,0,1,0,22000,1,1,0,0,0,'0.00',0,0),(7,'Latte (H)','',22000,6696,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:22:13',1,0,NULL,0,0,1,0,22000,1,1,0,0,0,'0.00',0,0),(8,'Latte (I)','',24000,5422,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:22:04',1,0,NULL,0,0,1,0,24000,1,1,0,0,0,'0.00',0,0),(9,'Spaghetti Bolognaise','',36000,14144,NULL,'item','food',3,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:21:53',1,0,NULL,0,0,1,0,36000,1,1,0,0,0,'0.00',0,0),(10,'Chicken Wings','',30000,12000,NULL,'item','food',5,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:21:44',0,0,NULL,0,0,1,0,30000,1,1,0,0,0,'0.00',0,0),(11,'Chocolate (H)','',22000,5671,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:21:30',1,0,NULL,0,0,1,0,22000,1,1,0,0,0,'0.00',0,0),(12,'Chocolate (I)','',24000,5453,NULL,'item','beverage',4,'administrator','2017-09-13 20:44:48','admin','2018-07-18 02:21:21',1,0,NULL,0,0,1,0,24000,1,1,0,0,0,'0.00',0,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_product_category` */

insert into `pos_product_category`(`id`,`product_category_name`,`product_category_desc`,`parent_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'BOTTLE BEVERAGE','BOTTLE BEVERAGE',0,'administrator','2017-03-02 14:17:59','administrator','2017-03-02 14:17:59',1,0),(2,'RICE BOWL','RICE BOWL',0,'administrator','2017-03-02 14:18:38','admin','2018-07-18 02:20:19',1,0),(3,'PASTA','PASTA',0,'administrator','2017-03-02 14:18:57','admin','2018-07-18 02:20:24',1,0),(4,'COFFEE','COFFEE',0,'administrator','2017-03-31 13:58:54','administrator','2017-03-31 13:58:54',1,0),(5,'MAKANAN RINGAN','MAKANAN RINGAN',0,'administrator','2017-03-31 14:00:29','administrator','2017-11-01 17:38:54',1,0),(6,'MAKANAN BERAT','MAKANAN BERAT',0,'administrator','2017-03-31 14:00:29','administrator','2017-11-01 17:38:54',1,0);

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
 `product_varian_id` int(11) DEFAULT '0',
 `varian_id` int(11) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
 `normal_price` double DEFAULT '0',
 `has_varian` smallint(6) DEFAULT '0',
 `product_varian_id` int(11) DEFAULT '0',
 `varian_id` int(11) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
 `no_surat_jalan` varchar(100) DEFAULT '',
 PRIMARY KEY (`id`),
 UNIQUE KEY `receiv_number_idx` (`receive_number`),
 KEY `fk_receiving_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_receiving` *//*Table structure for table `pos_retur` */DROP TABLE IF EXISTS `pos_retur`;CREATE TABLE `pos_retur` ( `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT, `retur_number` varchar(20) NOT NULL, `retur_type` enum('po','so') NOT NULL, `retur_date` datetime NOT NULL, `retur_memo` tinytext, `total_qty` int(11) NOT NULL, `total_price` double NOT NULL, `createdby` varchar(50) DEFAULT NULL, `created` timestamp NULL DEFAULT NULL, `updatedby` varchar(50) DEFAULT NULL, `updated` timestamp NULL DEFAULT NULL, `is_active` tinyint(1) NOT NULL DEFAULT '1', `is_deleted` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), UNIQUE KEY `retur_number_idx` (`retur_number`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `pos_retur` *//*Table structure for table `pos_retur_detail` */DROP TABLE IF EXISTS `pos_retur_detail`;CREATE TABLE `pos_retur_detail` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `retur_id` bigint(20) NOT NULL, `item_product_id` int(11) NOT NULL, `retur_det_qty_before` int(11) DEFAULT NULL, `retur_det_price` double DEFAULT NULL, `retur_det_qty` int(11) DEFAULT NULL, `retur_det_total` double DEFAULT NULL, `retur_ref_id` bigint(20) DEFAULT NULL, `retur_ref_det_id` bigint(20) DEFAULT NULL, `status_ref` varchar(20) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `pos_retur_detail` */
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_ro_detail` */

/*Table structure for table `pos_room` */

DROP TABLE IF EXISTS `pos_room`;

CREATE TABLE `pos_room` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `room_name` varchar(100) NOT NULL,
 `room_no` varchar(10) NOT NULL,
 `room_desc` varchar(100) DEFAULT NULL,
 `floorplan_id` int(11) NOT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) DEFAULT '1',
 `is_deleted` tinyint(1) DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_room` */

insert into `pos_room`(`id`,`room_name`,`room_no`,`room_desc`,`floorplan_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Room A','A','',3,'admin','2018-07-18 02:53:19','admin','2018-07-18 02:53:25',1,0),(2,'Room B','B','',2,'admin','2018-07-18 02:53:38','admin','2018-07-18 02:53:38',1,0);

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_stock` *//*Table structure for table `pos_stock_koreksi` */DROP TABLE IF EXISTS `pos_stock_koreksi`;CREATE TABLE `pos_stock_koreksi` ( `id` bigint(20) NOT NULL AUTO_INCREMENT, `item_id` bigint(20) NOT NULL, `total_stock_awal` float DEFAULT NULL, `total_stock_koreksi` float DEFAULT NULL, `total_stock_akhir` float DEFAULT NULL, `trx_date` date NOT NULL, `storehouse_id` int(11) DEFAULT NULL, `unit_id` int(11) DEFAULT NULL, `item_hpp` double DEFAULT '0', `keterangan` varchar(255) DEFAULT NULL, `trx_type` enum('in','out') DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;/*Data for the table `pos_stock_koreksi` *//*Table structure for table `pos_stock_opname` */

DROP TABLE IF EXISTS `pos_stock_opname`;

CREATE TABLE `pos_stock_opname` ( `id` int(11) NOT NULL AUTO_INCREMENT, `sto_number` varchar(255) NOT NULL, `sto_date` date NOT NULL, `sto_memo` varchar(255) DEFAULT NULL, `createdby` varchar(50) DEFAULT NULL, `created` timestamp NULL DEFAULT NULL, `updatedby` varchar(50) DEFAULT NULL, `updated` timestamp NULL DEFAULT NULL, `is_active` tinyint(1) NOT NULL DEFAULT '1', `is_deleted` tinyint(1) NOT NULL DEFAULT '0', `storehouse_id` int(11) DEFAULT NULL, `sto_status` enum('progress','done','cancel') DEFAULT 'progress', PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_storehouse` */

insert into `pos_storehouse`(`id`,`storehouse_code`,`storehouse_name`,`storehouse_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_primary`) values (1,'G1','Gudang Utama','Gudang Utama','administrator','2014-11-01 11:05:16','administrator','2014-11-01 11:05:16',1,0,1);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_storehouse_users` */

insert into `pos_storehouse_users`(`id`,`storehouse_id`,`user_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`is_retail_warehouse`) values (2,1,2,'administrator','2016-03-27 19:40:25','admin','2018-07-18 03:01:01',1,0,1),(3,1,3,'admin','2018-07-18 03:01:11','admin','2018-07-18 03:01:11',1,0,1);

/*Table structure for table `pos_supplier` */

DROP TABLE IF EXISTS `pos_supplier`;

CREATE TABLE `pos_supplier` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `supplier_code` varchar(10) DEFAULT NULL,
 `supplier_name` varchar(255) NOT NULL,
 `supplier_contact_person` varchar(40) DEFAULT NULL,
 `supplier_address` varchar(255) DEFAULT NULL,
 `supplier_phone` varchar(100) DEFAULT NULL,
 `supplier_fax` varchar(100) DEFAULT NULL,
 `supplier_email` varchar(100) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `supplier_status` enum('ok','warning','blacklist') DEFAULT 'ok',
 `keterangan_blacklist` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_supplier` */

insert into `pos_supplier`(`id`,`supplier_code`,`supplier_name`,`supplier_contact_person`,`supplier_address`,`supplier_phone`,`supplier_fax`,`supplier_email`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`supplier_status`,`keterangan_blacklist`) values (1,'','Supplier Daging','H.Ahmad','','123','','','admin','2018-07-18 02:29:07','admin','2018-07-18 02:29:07',1,0,'ok','');

/*Table structure for table `pos_supplier_item` */

DROP TABLE IF EXISTS `pos_supplier_item`;

CREATE TABLE `pos_supplier_item` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `supplier_id` int(11) NOT NULL,
 `item_id` int(11) NOT NULL,
 `unit_id` int(11) DEFAULT NULL,
 `item_price` double DEFAULT '0',
 `item_hpp` double DEFAULT '0',
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 `last_in` double DEFAULT '0',
 `old_last_in` double DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_supplier_item` */

insert into `pos_supplier_item`(`id`,`supplier_id`,`item_id`,`unit_id`,`item_price`,`item_hpp`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`last_in`,`old_last_in`) values (1,1,1,7,10000,10000,'admin','2018-07-18 11:51:55','admin','2018-07-18 11:51:55',1,0,10000,10000),(2,1,2,1,10000,10000,'admin','2018-07-18 11:51:55','admin','2018-07-18 11:51:55',1,0,10000,10000);

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
 `room_id` int(11) DEFAULT '0',
 `kapasitas` smallint(6) DEFAULT '0',
 `table_tipe` enum('dinein','takeaway','delivery') NOT NULL DEFAULT 'dinein',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_table` */

insert into `pos_table`(`id`,`table_name`,`table_no`,`table_desc`,`floorplan_id`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`,`room_id`,`kapasitas`,`table_tipe`) values (1,'TA 1','TA 1','Take Away 1',1,'administrator','2016-07-25 15:07:55','administrator','2016-07-27 16:33:45',1,0,0,1,'takeaway'),(2,'TA 2','TA 2','Take Away 2',1,'administrator','2016-07-25 15:08:37','administrator','2016-07-27 16:36:20',1,0,0,1,'takeaway'),(3,'TA 3','TA 3','Take Away 3',1,'administrator','2016-07-25 15:12:55','administrator','2016-07-25 15:14:31',1,0,0,1,'takeaway'),(4,'NS1','NS1','No.1 Non-Smoking',3,'administrator','2016-07-27 16:28:18','administrator','2016-07-27 16:34:00',1,0,2,4,'dinein'),(5,'NS2','NS2','No.2 Non-Smoking',3,'administrator','2016-07-27 16:28:40','administrator','2016-07-27 16:34:12',1,0,2,4,'dinein'),(6,'NS3','NS3','No.3 Non-Smoking',3,'administrator','2016-07-27 16:30:38','admin','2018-07-18 02:57:56',1,0,2,4,'dinein'),(7,'SM1','SM1','No.1 Smoking',2,'administrator','2016-07-27 16:30:53','admin','2018-07-18 02:58:04',1,0,1,4,'dinein'),(8,'SM2','SM2','No.2 Smoking',2,'administrator','2016-07-27 16:31:07','admin','2018-07-18 02:58:10',1,0,1,4,'dinein'),(9,'SM3','SM3','No.3 Smoking',2,'administrator','2016-07-27 16:31:18','admin','2018-07-18 02:57:28',1,0,1,4,'dinein');

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
) ENGINE=InnoDB AUTO_INCREMENT=1657 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_table_inventory` */

insert into `pos_table_inventory`(`id`,`table_id`,`billing_no`,`tanggal`,`status`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) values (1,1,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(2,2,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(3,3,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(4,4,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(5,5,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(6,6,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(7,7,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(8,8,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(9,9,NULL,'2018-07-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(10,1,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(11,2,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(12,3,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(13,4,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(14,5,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(15,6,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(16,7,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(17,8,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(18,9,NULL,'2018-07-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(19,1,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(20,2,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(21,3,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(22,4,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(23,5,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(24,6,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(25,7,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(26,8,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(27,9,NULL,'2018-07-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(28,1,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(29,2,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(30,3,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(31,4,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(32,5,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(33,6,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(34,7,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(35,8,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(36,9,NULL,'2018-07-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(37,1,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(38,2,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(39,3,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(40,4,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(41,5,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(42,6,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(43,7,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(44,8,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(45,9,NULL,'2018-07-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(46,1,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(47,2,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(48,3,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(49,4,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(50,5,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(51,6,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(52,7,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(53,8,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(54,9,NULL,'2018-07-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(55,1,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(56,2,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(57,3,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(58,4,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(59,5,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(60,6,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(61,7,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(62,8,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(63,9,NULL,'2018-07-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(64,1,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(65,2,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(66,3,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(67,4,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(68,5,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(69,6,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(70,7,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(71,8,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(72,9,NULL,'2018-07-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(73,1,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(74,2,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(75,3,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(76,4,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(77,5,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(78,6,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(79,7,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(80,8,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(81,9,NULL,'2018-07-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(82,1,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(83,2,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(84,3,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(85,4,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(86,5,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(87,6,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(88,7,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(89,8,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(90,9,NULL,'2018-07-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(91,1,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(92,2,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(93,3,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(94,4,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(95,5,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(96,6,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(97,7,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(98,8,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(99,9,NULL,'2018-07-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(100,1,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(101,2,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(102,3,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(103,4,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(104,5,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(105,6,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(106,7,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(107,8,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(108,9,NULL,'2018-07-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(109,1,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(110,2,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(111,3,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(112,4,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(113,5,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(114,6,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(115,7,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(116,8,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(117,9,NULL,'2018-07-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(118,1,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(119,2,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(120,3,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(121,4,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(122,5,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(123,6,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(124,7,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(125,8,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(126,9,NULL,'2018-07-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(127,1,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(128,2,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(129,3,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(130,4,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(131,5,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(132,6,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(133,7,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(134,8,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(135,9,NULL,'2018-07-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(136,1,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(137,2,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(138,3,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(139,4,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(140,5,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(141,6,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(142,7,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(143,8,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(144,9,NULL,'2018-07-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(145,1,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(146,2,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(147,3,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(148,4,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(149,5,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(150,6,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(151,7,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(152,8,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(153,9,NULL,'2018-07-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(154,1,'','2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(155,2,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(156,3,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(157,4,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(158,5,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(159,6,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(160,7,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(161,8,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(162,9,NULL,'2018-07-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(163,1,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(164,2,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(165,3,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(166,4,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(167,5,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(168,6,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(169,7,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(170,8,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(171,9,NULL,'2018-07-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(172,1,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(173,2,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(174,3,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(175,4,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(176,5,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(177,6,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(178,7,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(179,8,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(180,9,NULL,'2018-07-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(181,1,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(182,2,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(183,3,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(184,4,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(185,5,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(186,6,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(187,7,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(188,8,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(189,9,NULL,'2018-07-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(190,1,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(191,2,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(192,3,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(193,4,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(194,5,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(195,6,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(196,7,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(197,8,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(198,9,NULL,'2018-07-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(199,1,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(200,2,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(201,3,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(202,4,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(203,5,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(204,6,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(205,7,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(206,8,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(207,9,NULL,'2018-07-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(208,1,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(209,2,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(210,3,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(211,4,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(212,5,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(213,6,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(214,7,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(215,8,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(216,9,NULL,'2018-07-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(217,1,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(218,2,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(219,3,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(220,4,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(221,5,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(222,6,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(223,7,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(224,8,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(225,9,NULL,'2018-07-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(226,1,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(227,2,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(228,3,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(229,4,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(230,5,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(231,6,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(232,7,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(233,8,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(234,9,NULL,'2018-07-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(235,1,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(236,2,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(237,3,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(238,4,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(239,5,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(240,6,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(241,7,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(242,8,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(243,9,NULL,'2018-07-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(244,1,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(245,2,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(246,3,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(247,4,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(248,5,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(249,6,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(250,7,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(251,8,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(252,9,NULL,'2018-07-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(253,1,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(254,2,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(255,3,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(256,4,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(257,5,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(258,6,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(259,7,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(260,8,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(261,9,NULL,'2018-07-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(262,1,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(263,2,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(264,3,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(265,4,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(266,5,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(267,6,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(268,7,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(269,8,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(270,9,NULL,'2018-07-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(271,1,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(272,2,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(273,3,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(274,4,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(275,5,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(276,6,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(277,7,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(278,8,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(279,9,NULL,'2018-07-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(280,1,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(281,2,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(282,3,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(283,4,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(284,5,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(285,6,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(286,7,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(287,8,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(288,9,NULL,'2018-08-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(289,1,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(290,2,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(291,3,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(292,4,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(293,5,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(294,6,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(295,7,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(296,8,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(297,9,NULL,'2018-08-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(298,1,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(299,2,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(300,3,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(301,4,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(302,5,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(303,6,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(304,7,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(305,8,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(306,9,NULL,'2018-08-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(307,1,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(308,2,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(309,3,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(310,4,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(311,5,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(312,6,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(313,7,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(314,8,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(315,9,NULL,'2018-08-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(316,1,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(317,2,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(318,3,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(319,4,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(320,5,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(321,6,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(322,7,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(323,8,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(324,9,NULL,'2018-08-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(325,1,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(326,2,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(327,3,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(328,4,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(329,5,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(330,6,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(331,7,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(332,8,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(333,9,NULL,'2018-08-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(334,1,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(335,2,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(336,3,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(337,4,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(338,5,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(339,6,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(340,7,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(341,8,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(342,9,NULL,'2018-08-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(343,1,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(344,2,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(345,3,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(346,4,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(347,5,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(348,6,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(349,7,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(350,8,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(351,9,NULL,'2018-08-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(352,1,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(353,2,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(354,3,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(355,4,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(356,5,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(357,6,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(358,7,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(359,8,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(360,9,NULL,'2018-08-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(361,1,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(362,2,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(363,3,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(364,4,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(365,5,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(366,6,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(367,7,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(368,8,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(369,9,NULL,'2018-08-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(370,1,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(371,2,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(372,3,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(373,4,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(374,5,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(375,6,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(376,7,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(377,8,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(378,9,NULL,'2018-08-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(379,1,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(380,2,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(381,3,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(382,4,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(383,5,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(384,6,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(385,7,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(386,8,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(387,9,NULL,'2018-08-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(388,1,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(389,2,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(390,3,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(391,4,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(392,5,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(393,6,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(394,7,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(395,8,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(396,9,NULL,'2018-08-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(397,1,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(398,2,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(399,3,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(400,4,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(401,5,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(402,6,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(403,7,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(404,8,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(405,9,NULL,'2018-08-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(406,1,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(407,2,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(408,3,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(409,4,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(410,5,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(411,6,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(412,7,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(413,8,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(414,9,NULL,'2018-08-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(415,1,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(416,2,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(417,3,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(418,4,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(419,5,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(420,6,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(421,7,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(422,8,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(423,9,NULL,'2018-08-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(424,1,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(425,2,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(426,3,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(427,4,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(428,5,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(429,6,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(430,7,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(431,8,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(432,9,NULL,'2018-08-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(433,1,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(434,2,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(435,3,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(436,4,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(437,5,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(438,6,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(439,7,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(440,8,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(441,9,NULL,'2018-08-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(442,1,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(443,2,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(444,3,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(445,4,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(446,5,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(447,6,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(448,7,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(449,8,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(450,9,NULL,'2018-08-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(451,1,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(452,2,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(453,3,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(454,4,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(455,5,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(456,6,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(457,7,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(458,8,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(459,9,NULL,'2018-08-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(460,1,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(461,2,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(462,3,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(463,4,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(464,5,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(465,6,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(466,7,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(467,8,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(468,9,NULL,'2018-08-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(469,1,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(470,2,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(471,3,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(472,4,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(473,5,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(474,6,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(475,7,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(476,8,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(477,9,NULL,'2018-08-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(478,1,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(479,2,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(480,3,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(481,4,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(482,5,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(483,6,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(484,7,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(485,8,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(486,9,NULL,'2018-08-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(487,1,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(488,2,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(489,3,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(490,4,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(491,5,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(492,6,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(493,7,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(494,8,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(495,9,NULL,'2018-08-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(496,1,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(497,2,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(498,3,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(499,4,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(500,5,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(501,6,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(502,7,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(503,8,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(504,9,NULL,'2018-08-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(505,1,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(506,2,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(507,3,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(508,4,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(509,5,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(510,6,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(511,7,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(512,8,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(513,9,NULL,'2018-08-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(514,1,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(515,2,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(516,3,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(517,4,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(518,5,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(519,6,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(520,7,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(521,8,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(522,9,NULL,'2018-08-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(523,1,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(524,2,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(525,3,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(526,4,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(527,5,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(528,6,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(529,7,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(530,8,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(531,9,NULL,'2018-08-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(532,1,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(533,2,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(534,3,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(535,4,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(536,5,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(537,6,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(538,7,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(539,8,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(540,9,NULL,'2018-08-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(541,1,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(542,2,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(543,3,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(544,4,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(545,5,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(546,6,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(547,7,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(548,8,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(549,9,NULL,'2018-08-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(550,1,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(551,2,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(552,3,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(553,4,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(554,5,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(555,6,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(556,7,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(557,8,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(558,9,NULL,'2018-08-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(559,1,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(560,2,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(561,3,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(562,4,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(563,5,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(564,6,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(565,7,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(566,8,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(567,9,NULL,'2018-09-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(568,1,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(569,2,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(570,3,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(571,4,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(572,5,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(573,6,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(574,7,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(575,8,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(576,9,NULL,'2018-09-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(577,1,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(578,2,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(579,3,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(580,4,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(581,5,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(582,6,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(583,7,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(584,8,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(585,9,NULL,'2018-09-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(586,1,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(587,2,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(588,3,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(589,4,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(590,5,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(591,6,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(592,7,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(593,8,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(594,9,NULL,'2018-09-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(595,1,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(596,2,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(597,3,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(598,4,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(599,5,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(600,6,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(601,7,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(602,8,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(603,9,NULL,'2018-09-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(604,1,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(605,2,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(606,3,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(607,4,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(608,5,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(609,6,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(610,7,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(611,8,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(612,9,NULL,'2018-09-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(613,1,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(614,2,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(615,3,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(616,4,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(617,5,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(618,6,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(619,7,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(620,8,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(621,9,NULL,'2018-09-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(622,1,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(623,2,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(624,3,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(625,4,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(626,5,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(627,6,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(628,7,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(629,8,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(630,9,NULL,'2018-09-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(631,1,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(632,2,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(633,3,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(634,4,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(635,5,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(636,6,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(637,7,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(638,8,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(639,9,NULL,'2018-09-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(640,1,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(641,2,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(642,3,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(643,4,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(644,5,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(645,6,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(646,7,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(647,8,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(648,9,NULL,'2018-09-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(649,1,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(650,2,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(651,3,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(652,4,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(653,5,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(654,6,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(655,7,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(656,8,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(657,9,NULL,'2018-09-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(658,1,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(659,2,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(660,3,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(661,4,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(662,5,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(663,6,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(664,7,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(665,8,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(666,9,NULL,'2018-09-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(667,1,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(668,2,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(669,3,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(670,4,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(671,5,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(672,6,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(673,7,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(674,8,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(675,9,NULL,'2018-09-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(676,1,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(677,2,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(678,3,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(679,4,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(680,5,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(681,6,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(682,7,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(683,8,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(684,9,NULL,'2018-09-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(685,1,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(686,2,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(687,3,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(688,4,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(689,5,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(690,6,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(691,7,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(692,8,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(693,9,NULL,'2018-09-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(694,1,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(695,2,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(696,3,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(697,4,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(698,5,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(699,6,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(700,7,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(701,8,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(702,9,NULL,'2018-09-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(703,1,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(704,2,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(705,3,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(706,4,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(707,5,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(708,6,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(709,7,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(710,8,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(711,9,NULL,'2018-09-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(712,1,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(713,2,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(714,3,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(715,4,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(716,5,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(717,6,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(718,7,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(719,8,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(720,9,NULL,'2018-09-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(721,1,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(722,2,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(723,3,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(724,4,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(725,5,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(726,6,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(727,7,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(728,8,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(729,9,NULL,'2018-09-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(730,1,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(731,2,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(732,3,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(733,4,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(734,5,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(735,6,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(736,7,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(737,8,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(738,9,NULL,'2018-09-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(739,1,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(740,2,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(741,3,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(742,4,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(743,5,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(744,6,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(745,7,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(746,8,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(747,9,NULL,'2018-09-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(748,1,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(749,2,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(750,3,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(751,4,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(752,5,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(753,6,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(754,7,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(755,8,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(756,9,NULL,'2018-09-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(757,1,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(758,2,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(759,3,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(760,4,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(761,5,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(762,6,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(763,7,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(764,8,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(765,9,NULL,'2018-09-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(766,1,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(767,2,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(768,3,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(769,4,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(770,5,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(771,6,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(772,7,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(773,8,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(774,9,NULL,'2018-09-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(775,1,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(776,2,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(777,3,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(778,4,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(779,5,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(780,6,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(781,7,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(782,8,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(783,9,NULL,'2018-09-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(784,1,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(785,2,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(786,3,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(787,4,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(788,5,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(789,6,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(790,7,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(791,8,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(792,9,NULL,'2018-09-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(793,1,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(794,2,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(795,3,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(796,4,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(797,5,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(798,6,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(799,7,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(800,8,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(801,9,NULL,'2018-09-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(802,1,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(803,2,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(804,3,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(805,4,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(806,5,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(807,6,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(808,7,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(809,8,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(810,9,NULL,'2018-09-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(811,1,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(812,2,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(813,3,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(814,4,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(815,5,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(816,6,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(817,7,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(818,8,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(819,9,NULL,'2018-09-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(820,1,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(821,2,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(822,3,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(823,4,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(824,5,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(825,6,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(826,7,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(827,8,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(828,9,NULL,'2018-09-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(829,1,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(830,2,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(831,3,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(832,4,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(833,5,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(834,6,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(835,7,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(836,8,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(837,9,NULL,'2018-10-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(838,1,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(839,2,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(840,3,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(841,4,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(842,5,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(843,6,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(844,7,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(845,8,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(846,9,NULL,'2018-10-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(847,1,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(848,2,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(849,3,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(850,4,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(851,5,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(852,6,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(853,7,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(854,8,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(855,9,NULL,'2018-10-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(856,1,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(857,2,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(858,3,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(859,4,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(860,5,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(861,6,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(862,7,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(863,8,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(864,9,NULL,'2018-10-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(865,1,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(866,2,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(867,3,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(868,4,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(869,5,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(870,6,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(871,7,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(872,8,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(873,9,NULL,'2018-10-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(874,1,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(875,2,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(876,3,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(877,4,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(878,5,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(879,6,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(880,7,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(881,8,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(882,9,NULL,'2018-10-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(883,1,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(884,2,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(885,3,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(886,4,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(887,5,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(888,6,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(889,7,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(890,8,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(891,9,NULL,'2018-10-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(892,1,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(893,2,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(894,3,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(895,4,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(896,5,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(897,6,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(898,7,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(899,8,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(900,9,NULL,'2018-10-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(901,1,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(902,2,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(903,3,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(904,4,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(905,5,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(906,6,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(907,7,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(908,8,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(909,9,NULL,'2018-10-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(910,1,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(911,2,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(912,3,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(913,4,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(914,5,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(915,6,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(916,7,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(917,8,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(918,9,NULL,'2018-10-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(919,1,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(920,2,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(921,3,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(922,4,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(923,5,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(924,6,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(925,7,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(926,8,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(927,9,NULL,'2018-10-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(928,1,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(929,2,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(930,3,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(931,4,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(932,5,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(933,6,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(934,7,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(935,8,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(936,9,NULL,'2018-10-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(937,1,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(938,2,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(939,3,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(940,4,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(941,5,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(942,6,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(943,7,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(944,8,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(945,9,NULL,'2018-10-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(946,1,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(947,2,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(948,3,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(949,4,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(950,5,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(951,6,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(952,7,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(953,8,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(954,9,NULL,'2018-10-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(955,1,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(956,2,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(957,3,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(958,4,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(959,5,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(960,6,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(961,7,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(962,8,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(963,9,NULL,'2018-10-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(964,1,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(965,2,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(966,3,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(967,4,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(968,5,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(969,6,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(970,7,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(971,8,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(972,9,NULL,'2018-10-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(973,1,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(974,2,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(975,3,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(976,4,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(977,5,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(978,6,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(979,7,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(980,8,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(981,9,NULL,'2018-10-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(982,1,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(983,2,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(984,3,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(985,4,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(986,5,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(987,6,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(988,7,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(989,8,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(990,9,NULL,'2018-10-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(991,1,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(992,2,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(993,3,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(994,4,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(995,5,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(996,6,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(997,7,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(998,8,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(999,9,NULL,'2018-10-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1000,1,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1001,2,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1002,3,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1003,4,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1004,5,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1005,6,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1006,7,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1007,8,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1008,9,NULL,'2018-10-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1009,1,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1010,2,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1011,3,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1012,4,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1013,5,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1014,6,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1015,7,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1016,8,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1017,9,NULL,'2018-10-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1018,1,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1019,2,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1020,3,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1021,4,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1022,5,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1023,6,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1024,7,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1025,8,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1026,9,NULL,'2018-10-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1027,1,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1028,2,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1029,3,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1030,4,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1031,5,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1032,6,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1033,7,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1034,8,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1035,9,NULL,'2018-10-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1036,1,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1037,2,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1038,3,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1039,4,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1040,5,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1041,6,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1042,7,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1043,8,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1044,9,NULL,'2018-10-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1045,1,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1046,2,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1047,3,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1048,4,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1049,5,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1050,6,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1051,7,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1052,8,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1053,9,NULL,'2018-10-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1054,1,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1055,2,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1056,3,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1057,4,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1058,5,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1059,6,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1060,7,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1061,8,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1062,9,NULL,'2018-10-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1063,1,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1064,2,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1065,3,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1066,4,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1067,5,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1068,6,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1069,7,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1070,8,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1071,9,NULL,'2018-10-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1072,1,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1073,2,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1074,3,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1075,4,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1076,5,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1077,6,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1078,7,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1079,8,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1080,9,NULL,'2018-10-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1081,1,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1082,2,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1083,3,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1084,4,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1085,5,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1086,6,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1087,7,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1088,8,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1089,9,NULL,'2018-10-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1090,1,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1091,2,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1092,3,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1093,4,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1094,5,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1095,6,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1096,7,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1097,8,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1098,9,NULL,'2018-10-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1099,1,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1100,2,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1101,3,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1102,4,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1103,5,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1104,6,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1105,7,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1106,8,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1107,9,NULL,'2018-10-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1108,1,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1109,2,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1110,3,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1111,4,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1112,5,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1113,6,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1114,7,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1115,8,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1116,9,NULL,'2018-11-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1117,1,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1118,2,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1119,3,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1120,4,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1121,5,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1122,6,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1123,7,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1124,8,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1125,9,NULL,'2018-11-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1126,1,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1127,2,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1128,3,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1129,4,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1130,5,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1131,6,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1132,7,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1133,8,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1134,9,NULL,'2018-11-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1135,1,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1136,2,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1137,3,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1138,4,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1139,5,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1140,6,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1141,7,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1142,8,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1143,9,NULL,'2018-11-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1144,1,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1145,2,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1146,3,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1147,4,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1148,5,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1149,6,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1150,7,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1151,8,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1152,9,NULL,'2018-11-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1153,1,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1154,2,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1155,3,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1156,4,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1157,5,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1158,6,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1159,7,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1160,8,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1161,9,NULL,'2018-11-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1162,1,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1163,2,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1164,3,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1165,4,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1166,5,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1167,6,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1168,7,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1169,8,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1170,9,NULL,'2018-11-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1171,1,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1172,2,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1173,3,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1174,4,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1175,5,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1176,6,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1177,7,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1178,8,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1179,9,NULL,'2018-11-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1180,1,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1181,2,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1182,3,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1183,4,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1184,5,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1185,6,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1186,7,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1187,8,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1188,9,NULL,'2018-11-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1189,1,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1190,2,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1191,3,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1192,4,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1193,5,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1194,6,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1195,7,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1196,8,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1197,9,NULL,'2018-11-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1198,1,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1199,2,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1200,3,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1201,4,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1202,5,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1203,6,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1204,7,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1205,8,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1206,9,NULL,'2018-11-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1207,1,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1208,2,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1209,3,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1210,4,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1211,5,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1212,6,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1213,7,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1214,8,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1215,9,NULL,'2018-11-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1216,1,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1217,2,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1218,3,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1219,4,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1220,5,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1221,6,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1222,7,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1223,8,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1224,9,NULL,'2018-11-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1225,1,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1226,2,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1227,3,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1228,4,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1229,5,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1230,6,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1231,7,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1232,8,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1233,9,NULL,'2018-11-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1234,1,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1235,2,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1236,3,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1237,4,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1238,5,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1239,6,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1240,7,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1241,8,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1242,9,NULL,'2018-11-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1243,1,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1244,2,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1245,3,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1246,4,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1247,5,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1248,6,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1249,7,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1250,8,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1251,9,NULL,'2018-11-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1252,1,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1253,2,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1254,3,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1255,4,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1256,5,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1257,6,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1258,7,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1259,8,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1260,9,NULL,'2018-11-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1261,1,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1262,2,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1263,3,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1264,4,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1265,5,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1266,6,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1267,7,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1268,8,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1269,9,NULL,'2018-11-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1270,1,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1271,2,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1272,3,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1273,4,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1274,5,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1275,6,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1276,7,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1277,8,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1278,9,NULL,'2018-11-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1279,1,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1280,2,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1281,3,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1282,4,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1283,5,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1284,6,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1285,7,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1286,8,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1287,9,NULL,'2018-11-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1288,1,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1289,2,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1290,3,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1291,4,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1292,5,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1293,6,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1294,7,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1295,8,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1296,9,NULL,'2018-11-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1297,1,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1298,2,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1299,3,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1300,4,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1301,5,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1302,6,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1303,7,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1304,8,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1305,9,NULL,'2018-11-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1306,1,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1307,2,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1308,3,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1309,4,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1310,5,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1311,6,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1312,7,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1313,8,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1314,9,NULL,'2018-11-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1315,1,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1316,2,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1317,3,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1318,4,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1319,5,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1320,6,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1321,7,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1322,8,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1323,9,NULL,'2018-11-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1324,1,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1325,2,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1326,3,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1327,4,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1328,5,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1329,6,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1330,7,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1331,8,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1332,9,NULL,'2018-11-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1333,1,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1334,2,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1335,3,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1336,4,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1337,5,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1338,6,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1339,7,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1340,8,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1341,9,NULL,'2018-11-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1342,1,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1343,2,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1344,3,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1345,4,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1346,5,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1347,6,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1348,7,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1349,8,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1350,9,NULL,'2018-11-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1351,1,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1352,2,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1353,3,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1354,4,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1355,5,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1356,6,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1357,7,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1358,8,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1359,9,NULL,'2018-11-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1360,1,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1361,2,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1362,3,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1363,4,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1364,5,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1365,6,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1366,7,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1367,8,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1368,9,NULL,'2018-11-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1369,1,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1370,2,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1371,3,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1372,4,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1373,5,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1374,6,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1375,7,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1376,8,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1377,9,NULL,'2018-11-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1378,1,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1379,2,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1380,3,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1381,4,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1382,5,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1383,6,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1384,7,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1385,8,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1386,9,NULL,'2018-12-01','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1387,1,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1388,2,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1389,3,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1390,4,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1391,5,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1392,6,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1393,7,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1394,8,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1395,9,NULL,'2018-12-02','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1396,1,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1397,2,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1398,3,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1399,4,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1400,5,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1401,6,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1402,7,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1403,8,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1404,9,NULL,'2018-12-03','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1405,1,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1406,2,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1407,3,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1408,4,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1409,5,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1410,6,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1411,7,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1412,8,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1413,9,NULL,'2018-12-04','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1414,1,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1415,2,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1416,3,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1417,4,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1418,5,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1419,6,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1420,7,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1421,8,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1422,9,NULL,'2018-12-05','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1423,1,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1424,2,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1425,3,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1426,4,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1427,5,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1428,6,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1429,7,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1430,8,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1431,9,NULL,'2018-12-06','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1432,1,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1433,2,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1434,3,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1435,4,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1436,5,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1437,6,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1438,7,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1439,8,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1440,9,NULL,'2018-12-07','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1441,1,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1442,2,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1443,3,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1444,4,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1445,5,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1446,6,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1447,7,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1448,8,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1449,9,NULL,'2018-12-08','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1450,1,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1451,2,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1452,3,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1453,4,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1454,5,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1455,6,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1456,7,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1457,8,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1458,9,NULL,'2018-12-09','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1459,1,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1460,2,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1461,3,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1462,4,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1463,5,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1464,6,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1465,7,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1466,8,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1467,9,NULL,'2018-12-10','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1468,1,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1469,2,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1470,3,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1471,4,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1472,5,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1473,6,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1474,7,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1475,8,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1476,9,NULL,'2018-12-11','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1477,1,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1478,2,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1479,3,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1480,4,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1481,5,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1482,6,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1483,7,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1484,8,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1485,9,NULL,'2018-12-12','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1486,1,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1487,2,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1488,3,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1489,4,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1490,5,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1491,6,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1492,7,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1493,8,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1494,9,NULL,'2018-12-13','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1495,1,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1496,2,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1497,3,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1498,4,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1499,5,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1500,6,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1501,7,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1502,8,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1503,9,NULL,'2018-12-14','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1504,1,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1505,2,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1506,3,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1507,4,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1508,5,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1509,6,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1510,7,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1511,8,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1512,9,NULL,'2018-12-15','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1513,1,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1514,2,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1515,3,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1516,4,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1517,5,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1518,6,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1519,7,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1520,8,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1521,9,NULL,'2018-12-16','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1522,1,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1523,2,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1524,3,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1525,4,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1526,5,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1527,6,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1528,7,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1529,8,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1530,9,NULL,'2018-12-17','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1531,1,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1532,2,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1533,3,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1534,4,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1535,5,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1536,6,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1537,7,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1538,8,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1539,9,NULL,'2018-12-18','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1540,1,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1541,2,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1542,3,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1543,4,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1544,5,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1545,6,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1546,7,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1547,8,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1548,9,NULL,'2018-12-19','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1549,1,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1550,2,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1551,3,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1552,4,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1553,5,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1554,6,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1555,7,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1556,8,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1557,9,NULL,'2018-12-20','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1558,1,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1559,2,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1560,3,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1561,4,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1562,5,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1563,6,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1564,7,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1565,8,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1566,9,NULL,'2018-12-21','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1567,1,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1568,2,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1569,3,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1570,4,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1571,5,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1572,6,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1573,7,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1574,8,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1575,9,NULL,'2018-12-22','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1576,1,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1577,2,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1578,3,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1579,4,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1580,5,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1581,6,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1582,7,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1583,8,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1584,9,NULL,'2018-12-23','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1585,1,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1586,2,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1587,3,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1588,4,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1589,5,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1590,6,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1591,7,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1592,8,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1593,9,NULL,'2018-12-24','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1594,1,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1595,2,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1596,3,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1597,4,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1598,5,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1599,6,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1600,7,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1601,8,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1602,9,NULL,'2018-12-25','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1603,1,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1604,2,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1605,3,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1606,4,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1607,5,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1608,6,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1609,7,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1610,8,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1611,9,NULL,'2018-12-26','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1612,1,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1613,2,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1614,3,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1615,4,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1616,5,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1617,6,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1618,7,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1619,8,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1620,9,NULL,'2018-12-27','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1621,1,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1622,2,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1623,3,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1624,4,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1625,5,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1626,6,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1627,7,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1628,8,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1629,9,NULL,'2018-12-28','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1630,1,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1631,2,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1632,3,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1633,4,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1634,5,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1635,6,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1636,7,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1637,8,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1638,9,NULL,'2018-12-29','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1639,1,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1640,2,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1641,3,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1642,4,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1643,5,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1644,6,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1645,7,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1646,8,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1647,9,NULL,'2018-12-30','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1648,1,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1649,2,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1650,3,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1651,4,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1652,5,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1653,6,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1654,7,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1655,8,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0),(1656,9,NULL,'2018-12-31','available','2018-07-18 01:08:42','admin','2018-07-18 01:08:42','admin',1,0);

/*Table structure for table `pos_unit` */

DROP TABLE IF EXISTS `pos_unit`;

CREATE TABLE `pos_unit` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `unit_code` varchar(10) DEFAULT NULL,
 `unit_name` varchar(255) DEFAULT NULL,
 `createdby` varchar(50) DEFAULT NULL,
 `created` timestamp NULL DEFAULT NULL,
 `updatedby` varchar(50) DEFAULT NULL,
 `updated` timestamp NULL DEFAULT NULL,
 `is_active` tinyint(1) NOT NULL DEFAULT '1',
 `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `satuan_code_idx` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_unit` */

insert into `pos_unit`(`id`,`unit_code`,`unit_name`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Kg','Kilogram','administrator','2014-08-18 03:02:18','administrator','2014-08-18 03:02:18',1,0),(2,'Ton','Ton','administrator','2014-08-18 03:02:18','administrator','2014-08-18 03:02:18',1,0),(3,'Btl','Botol','administrator','2014-08-18 03:02:18','administrator','2014-08-18 03:02:18',1,0),(4,'Galon','Galon','administrator','2014-08-17 20:02:18','administrator','2014-08-18 03:02:18',1,0),(5,'Pack','Pack','administrator','2014-08-17 20:05:31','administrator','2014-08-18 03:05:31',1,0),(7,'Box','Box','administrator','2014-08-28 08:45:52','administrator','2014-08-28 15:45:52',1,0),(8,'Pail','Pail','administrator','2014-09-17 14:51:09','admin','2018-07-18 02:28:20',1,0),(9,'Tabung','Tabung','administrator','2014-09-17 14:57:47','admin','2018-07-18 02:28:10',1,0),(10,'Ekor','Ekor','administrator','2014-09-18 15:21:03','administrator','2014-09-18 15:21:03',1,0);

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

/*Data for the table `pos_varian` */

insert into `pos_varian`(`id`,`varian_name`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values (1,'Large','administrator','2017-11-28 16:10:40','administrator','2017-11-28 16:10:40',1,0),(2,'Small','administrator','2017-11-28 16:10:48','administrator','2017-11-28 16:10:48',1,0),(3,'Medium','administrator','2017-11-28 16:10:54','administrator','2017-11-28 16:10:54',1,0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
