/*

MIGRASI DATABASE
WePOS - Cafe: v3.42.19 ke v3.42.20
Updated: 21-12-2019 18:00

*********************************************************************

*/


ALTER TABLE `pos_supplier` 
MODIFY `supplier_code` VARCHAR(20) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM') DEFAULT 'MERCHANT',
ADD `supplier_no` MEDIUMINT(9) DEFAULT 0;
#
ALTER TABLE `pos_sales` 
ADD `sales_code` VARCHAR(20) DEFAULT NULL,
ADD `sales_email` VARCHAR(50) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM') DEFAULT 'MERCHANT',
ADD `sales_no` MEDIUMINT(9) DEFAULT 0;
#
ALTER TABLE `pos_customer` 
MODIFY `customer_code` VARCHAR(20) DEFAULT NULL,
ADD `source_from` ENUM('MERCHANT','WSM','ELVO') DEFAULT 'MERCHANT',
ADD `customer_no` MEDIUMINT(9) DEFAULT 0;
#
UPDATE apps_options SET option_value = '01-03-2019', updated = '2019-03-07 00:00:01' WHERE option_var IN ('closing_sales_start_date','stock_rekap_start_date','closing_purchasing_start_date','closing_inventory_start_date','closing_accounting_start_date');
#
UPDATE apps_options SET option_value = '0', updated = '2019-03-07 00:00:01' WHERE option_var IN ('view_multiple_store');
#
UPDATE apps_options SET option_value = 'https://wepos.id', updated = '2019-03-07 00:00:01' WHERE option_var IN ('ipserver_management_systems');
#
UPDATE apps_modules SET module_name = 'Backup Master Data' WHERE module_name = 'Syncronize Master Data Store';
#
UPDATE apps_modules SET module_name = 'Backup Data Transaksi' WHERE module_name = 'Backup Transaksi Store';
#
UPDATE apps_modules SET is_active = 0 WHERE module_name = 'Sync & Backup';
#
DROP TABLE IF EXISTS `pos_reservation`;
CREATE TABLE `pos_reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_number` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_memo` tinytext,
  `reservation_status` enum('progress','done','cancel') NOT NULL DEFAULT 'progress',
  `createdby` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `reservation_from` enum('phone','direct','sales','online','whatsapp') NOT NULL DEFAULT 'direct',
  `reservation_customer_name` varchar(100) DEFAULT NULL,
  `reservation_customer_address` varchar(255) DEFAULT NULL,
  `reservation_customer_phone` varchar(100) DEFAULT NULL,
  `reservation_total_qty` float DEFAULT NULL,
  `reservation_sub_total` double DEFAULT '0',
  `reservation_discount` double DEFAULT '0',
  `reservation_tax` double DEFAULT '0',
  `reservation_service` double DEFAULT '0',
  `reservation_total_price` double DEFAULT '0',
  `reservation_payment` enum('cash','debit','credit','credit_ar') DEFAULT 'cash',
  `reservation_dp` double DEFAULT '0',
  `sales_id` mediumint(9) DEFAULT NULL,
  `sales_percentage` decimal(5,2) DEFAULT '0.00',
  `sales_price` double DEFAULT '0',
  `sales_type` char(20) DEFAULT NULL,
  `customer_id` int(11) DEFAULT '0',
  `bank_id` int(1) DEFAULT '0',
  `card_no` char(50) DEFAULT NULL,
  `total_guest` smallint(6) DEFAULT '0',
  `reservation_total_hpp` double DEFAULT '0',
  `billing_id` int(11) DEFAULT '0',
  `billing_no` varchar(20) DEFAULT NULL,
  `reservation_tipe` enum('dinein','takeaway','delivery') NOT NULL DEFAULT 'dinein',
  `reservation_time` datetime DEFAULT NULL,
  `reservation_customer_phone2` varchar(100) DEFAULT NULL,
  `reservation_customer_phone3` varchar(100) DEFAULT NULL,
  `preparing_date` date NOT NULL,
  `preparing_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pr_number_idx` (`reservation_number`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_reservation_detail`;
#
CREATE TABLE `pos_reservation_detail` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `has_varian` TINYINT(1) DEFAULT '0',
  `use_tax` TINYINT(1) DEFAULT '0',
  `use_service` TINYINT(1) DEFAULT '0',
  `tax_price` DOUBLE DEFAULT '0',
  `service_price` DOUBLE DEFAULT '0',
  `varian_id` INT(11) DEFAULT NULL,
  `product_varian_id` INT(11) DEFAULT '0',
  `resd_qty` FLOAT DEFAULT '0',
  `resd_hpp` DOUBLE DEFAULT '0',
  `resd_price` DOUBLE DEFAULT '0',
  `resd_tax` DOUBLE DEFAULT '0',
  `resd_service` DOUBLE DEFAULT '0',
  `resd_potongan` DOUBLE DEFAULT '0',
  `resd_total` DOUBLE DEFAULT '0',
  `resd_grandtotal` DOUBLE DEFAULT '0',
  `supplier_id` INT(11) DEFAULT '0',
  `is_kerjasama` TINYINT(1) DEFAULT '0',
  `persentase_bagi_hasil` FLOAT DEFAULT '0',
  `total_bagi_hasil` DOUBLE DEFAULT NULL,
  `resd_status` TINYINT(1) NOT NULL DEFAULT '0',
  `grandtotal_bagi_hasil` DOUBLE DEFAULT '0',
  `resd_notes` CHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE IF EXISTS `pos_retur`;
#
CREATE TABLE `pos_retur` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `retur_number` varchar(20) NOT NULL,
  `retur_ref` enum('penjualan','penjualan_so') DEFAULT NULL,
  `retur_type` enum('barang','batal_order') NOT NULL,
  `retur_date` date NOT NULL,
  `retur_memo` tinytext,
  `total_qty` float NOT NULL DEFAULT '0',
  `total_price` double NOT NULL DEFAULT '0',
  `total_tax` double DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `ref_no` varchar(30) DEFAULT NULL,
  `retur_status` enum('progress','done') DEFAULT 'progress',
  `storehouse_id` int(11) DEFAULT '0',
  `customer_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `retur_number_idx` (`retur_number`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_retur_detail`;
#
CREATE TABLE `pos_retur_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `retur_id` bigint(20) NOT NULL,
  `item_product_id` int(11) NOT NULL,
  `returd_qty_before` int(11) DEFAULT NULL,
  `returd_price` double DEFAULT '0',
  `returd_hpp` double DEFAULT '0',
  `returd_tax` double DEFAULT '0',
  `returd_qty` float DEFAULT '0',
  `returd_total` double DEFAULT '0',
  `returd_ref_id` bigint(20) DEFAULT NULL,
  `returd_refd_id` bigint(20) DEFAULT NULL,
  `returd_note` varchar(255) DEFAULT NULL,
  `data_stok_kode_unik` text,
  `use_stok_kode_unik` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `pos_notify_log`;
#
CREATE TABLE `pos_notify_log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `log_date` DATE DEFAULT NULL,
  `log_type` ENUM('master_data','inventory','sales','finance','accounting','app') DEFAULT NULL,
  `log_info` VARCHAR(255) DEFAULT NULL,
  `log_data` MEDIUMTEXT NOT NULL,
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
DROP TABLE IF EXISTS `pos_notify_log`;
#
CREATE TABLE `pos_notify_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_date` date DEFAULT NULL,
  `log_type` enum('master_data','inventory','sales','finance','accounting','app') DEFAULT NULL,
  `log_info` varchar(255) DEFAULT NULL,
  `log_data` mediumtext NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `acc_autoposting_detail`;
#
CREATE TABLE `acc_autoposting_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `autoposting_id` int(11) DEFAULT NULL,
  `rek_id_debet` int(11) NOT NULL,
  `rek_id_kredit` int(11) DEFAULT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
DROP TABLE IF EXISTS `acc_periode_laporan`;
#
CREATE TABLE `acc_periode_laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kd_periode_laporan` varchar(5) NOT NULL DEFAULT '',
  `ket_periode_laporan` varchar(50) NOT NULL,
  `kd_periode_kalender` varchar(5) NOT NULL,
  `nama_bulan_kalender` varchar(50) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
insert  into `acc_periode_laporan`(`id`,`kd_periode_laporan`,`ket_periode_laporan`,`kd_periode_kalender`,`nama_bulan_kalender`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
(1,'01','Januari','01','Januari','administrator','2014-10-21 17:29:20','administrator','2014-10-21 17:31:02',0,0),
(2,'02','Februari','02','Februari','administrator','2014-10-21 17:31:30','administrator','2014-10-21 17:31:30',0,0),
(3,'03','Maret','03','Maret','administrator','2014-10-21 17:32:04','administrator','2014-10-21 17:32:04',0,0),
(4,'04','April','04','April','administrator','2014-10-21 17:32:18','administrator','2014-10-21 17:32:18',0,0),
(5,'05','Mei','05','Mei','administrator','2014-10-21 17:32:33','administrator','2014-10-21 17:32:33',0,0),
(6,'06','Juni','06','Juni','administrator','2014-10-21 17:32:58','administrator','2014-10-21 17:32:58',0,0),
(7,'07','Juli','07','Juli','administrator','2014-10-21 17:33:14','administrator','2014-10-21 17:33:14',0,0),
(8,'08','Agustus','08','Agustus','administrator','2014-10-21 17:34:56','administrator','2014-10-21 17:34:56',0,0),
(9,'09','September','09','September','administrator','2014-10-21 17:35:11','administrator','2014-10-21 17:35:11',0,0),
(10,'10','Oktober','10','Oktober','administrator','2014-10-21 17:35:30','administrator','2014-10-21 17:35:30',0,0),
(11,'11','November','11','November','administrator','2014-10-21 17:35:46','administrator','2014-10-21 17:35:46',0,0),
(12,'12','Desember','12','Desember','administrator','2014-10-21 17:36:01','administrator','2014-10-21 17:36:01',0,0);
#
INSERT  INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) VALUES 
('store_connected_name','',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,'1','0'),
('store_connected_email','',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,'1','0'),
('as_server_backup','0',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,1,0),
('use_wms','0',NULL,'2019-03-01 00:00:07','administrator',NULL,NULL,1,0),
('opsi_no_print_when_payment','0',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,1,0),
('using_item_average_as_hpp','1',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,1,0),
('wepos_version','3.42.20',NULL,'2019-03-07 00:00:01','administrator',NULL,NULL,1,0);
#
insert  into `pos_payment_type`(`id`,`payment_type_name`,`payment_type_desc`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) values 
(4,'AR / Piutang','Paid By AR','administrator','2019-02-14 03:32:50','administrator','2019-02-14 03:32:50',1,0);
#
ALTER TABLE `acc_account_receivable`
MODIFY `ref_id` int(11) DEFAULT 0;
#
ALTER TABLE `acc_kontrabon` 
MODIFY `created` timestamp NULL DEFAULT NULL,
MODIFY `updated` timestamp NULL DEFAULT NULL;
#
ALTER TABLE `pos_billing_detail` 
ADD `storehouse_id` int(11) DEFAULT 0,
MODIFY `data_stok_kode_unik` text;
#
ALTER TABLE `pos_billing_detail_split` 
MODIFY `data_stok_kode_unik` text;
#
ALTER TABLE `pos_po` 
MODIFY `po_memo` tinytext;
#
ALTER TABLE `pos_production` 
MODIFY `pr_memo` tinytext,
MODIFY `createdby` varchar(50) DEFAULT NULL;
#
ALTER TABLE `pos_receive_detail` 
MODIFY `data_stok_kode_unik` text;
#
ALTER TABLE `pos_receiving` 
MODIFY `receive_memo` tinytext;
#
ALTER TABLE `pos_ro` 
MODIFY `ro_memo` tinytext;
#
ALTER TABLE `pos_usagewaste` 
MODIFY `uw_memo` tinytext;
#
ALTER TABLE `apps_clients` 
MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT,
MODIFY `client_code` varchar(50) NOT NULL,
MODIFY `client_name` char(100) NOT NULL,
MODIFY `client_address` char(150) DEFAULT NULL,
MODIFY `city_id` tinyint(4) DEFAULT NULL,
MODIFY `province_id` tinyint(4) DEFAULT NULL,
MODIFY `client_postcode` char(5) DEFAULT NULL,
MODIFY `country_id` tinyint(4) DEFAULT NULL,
MODIFY `client_phone` char(20) DEFAULT NULL,
MODIFY `client_fax` char(20) DEFAULT NULL,
MODIFY `client_email` char(50) DEFAULT NULL,
MODIFY `client_logo` char(50) DEFAULT NULL,
MODIFY `client_website` char(50) DEFAULT NULL,
MODIFY `client_notes` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `created` timestamp NULL DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_clients_structure`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `client_structure_name` char(100) NOT NULL,
MODIFY `client_structure_notes` char(100) DEFAULT NULL,
MODIFY `client_structure_parent` smallint(6) DEFAULT '0',
MODIFY `client_structure_order` smallint(6) DEFAULT '0',
MODIFY `role_id` smallint(6) DEFAULT NULL,
MODIFY `client_id` tinyint(4) DEFAULT NULL,
MODIFY `client_unit_id` tinyint(4) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_clients_unit`
MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT,
MODIFY `client_unit_name` char(50) NOT NULL,
MODIFY `client_id` tinyint(4) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_modules_method`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `method_function` char(100) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `method_description` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_modules_preload`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `preload_filename` char(50) NOT NULL,
MODIFY `preload_folderpath` char(100) DEFAULT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `preload_description` char(100) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_options` 
MODIFY `option_value` mediumtext NOT NULL;
#
ALTER TABLE `apps_roles_module`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `start_menu_path` char(100) DEFAULT NULL,
MODIFY `module_order` smallint(6) DEFAULT '0',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_roles_widget`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `widget_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor_access`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `supervisor_id` smallint(6) NOT NULL,
MODIFY `supervisor_access` char(50) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_supervisor_log` 
MODIFY `supervisor_id` smallint(6) NOT NULL,
MODIFY `supervisor_access` char(100) DEFAULT NULL;
#
ALTER TABLE `apps_users`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_username` char(50) NOT NULL,
MODIFY `user_password` char(64) NOT NULL,
MODIFY `role_id` smallint(6) NOT NULL,
MODIFY `user_firstname` char(50) NOT NULL,
MODIFY `user_lastname` char(50) DEFAULT NULL,
MODIFY `user_email` char(50) DEFAULT NULL,
MODIFY `user_phone` char(50) DEFAULT NULL,
MODIFY `user_mobile` char(50) DEFAULT NULL,
MODIFY `user_address` char(100) DEFAULT NULL,
MODIFY `client_id` tinyint(4) NOT NULL DEFAULT '1',
MODIFY `client_structure_id` smallint(6) NOT NULL,
MODIFY `avatar` char(255) DEFAULT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_desktop`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `wallpaper` char(50) NOT NULL DEFAULT 'default.jpg',
MODIFY `wallpaper_id` tinyint(4) NOT NULL DEFAULT '1',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_quickstart`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_users_shortcut`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `user_id` smallint(6) NOT NULL,
MODIFY `module_id` smallint(6) NOT NULL,
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `apps_widgets`
MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT,
MODIFY `widget_name` char(50) NOT NULL,
MODIFY `widget_author` char(50) DEFAULT NULL,
MODIFY `widget_version` char(10) DEFAULT NULL,
MODIFY `widget_description` char(100) DEFAULT NULL,
MODIFY `widget_controller` char(50) NOT NULL,
MODIFY `widget_order` smallint(6) DEFAULT '0',
MODIFY `createdby` char(50) DEFAULT NULL,
MODIFY `updatedby` char(50) DEFAULT NULL;
#
ALTER TABLE `pos_billing_log` 
MODIFY `log_data` mediumtext NOT NULL;
#
ALTER TABLE `pos_item_category`
MODIFY `item_category_code` char(6) DEFAULT NULL;
#
ALTER TABLE `pos_items` 
MODIFY `item_code` varchar(50) DEFAULT NULL,
MODIFY `item_price` double DEFAULT '0';
#
ALTER TABLE `pos_po_detail`
MODIFY `po_detail_total` double DEFAULT '0';
#
ALTER TABLE `pos_print_monitoring`
MODIFY `receiptTxt` mediumtext NOT NULL;
#
ALTER TABLE `pos_product`
ADD `product_code` varchar(100) DEFAULT NULL,
ADD `product_no` smallint(6) DEFAULT '0',
ADD `unit_id` int(11) DEFAULT '0';
#
ALTER TABLE `pos_product_category`
ADD `product_category_code` char(6) DEFAULT NULL;
#
ALTER TABLE `pos_product_package`
ADD `product_qty` float DEFAULT '1',
ADD `product_varian_id_item` int(11) DEFAULT '0',
ADD `varian_id_item` int(11) DEFAULT '0';
#
ALTER TABLE `pos_stock_opname`
MODIFY `createdby` varchar(50) NOT NULL,
MODIFY `updated` datetime DEFAULT NULL;
#
UPDATE pos_product SET product_code = CONCAT('P',(category_id*1000)+id) WHERE (product_code IS NULL OR product_code = '');
#
UPDATE pos_product_category SET product_category_code = CONCAT('C',(100)+id) WHERE (product_category_code IS NULL OR product_category_code = '');
#
ALTER TABLE `pos_product` 
ADD UNIQUE KEY `item_product_idx` (`product_code`);
#
UPDATE pos_items SET item_code = CONCAT('I',(category_id*1000)+id) WHERE (item_code IS NULL OR item_code = '');
#
UPDATE apps_options SET option_value = '3.42.20', updated = '2019-03-11 00:00:01' WHERE option_var IN ('wepos_version');
#
INSERT  INTO `apps_modules`(`module_name`,`module_author`,`module_version`,`module_description`,`module_folder`,`module_controller`,`module_is_menu`,`module_breadcrumb`,`module_order`,`module_icon`,`module_shortcut_icon`,`module_glyph_icon`,`module_glyph_font`,`module_free`,`running_background`,`show_on_start_menu`,`show_on_right_start_menu`,`start_menu_path`,`start_menu_order`,`start_menu_icon`,`start_menu_glyph`,`show_on_context_menu`,`context_menu_icon`,`context_menu_glyph`,`show_on_shorcut_desktop`,`desktop_shortcut_icon`,`desktop_shortcut_glyph`,`show_on_preference`,`preference_icon`,`preference_glyph`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) VALUES 
('Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Reservation>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-09 08:25:57','administrator','2019-04-09 17:49:57',1,0);
#
INSERT  INTO `apps_roles_module`(`role_id`,`module_id`,`start_menu_path`,`module_order`,`createdby`,`created`,`updatedby`,`updated`,`is_active`,`is_deleted`) VALUES 
(1,169,NULL,0,'administrator','2019-04-09 16:18:38','administrator','2019-04-09 16:18:38',1,0),
(2,169,NULL,0,'administrator','2019-04-09 16:18:38','administrator','2019-04-09 16:18:38',1,0);
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'NO: ', ''), 
option_value = REPLACE(option_value, 'NO:', ''), 
option_value = REPLACE(option_value, 'MEJA: ', ''), 
option_value = REPLACE(option_value, 'MEJA:', '');
#
INSERT INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) VALUES 
('maxday_cashier_report',1,NULL,'2019-05-11 00:00:00','administrator',NULL,NULL,'1','0');
#
ALTER TABLE `pos_billing_detail` 
ADD `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT 0;
#
ALTER TABLE `pos_billing_detail_split` 
ADD `storehouse_id` INT(11) DEFAULT 0,
ADD `diskon_sebelum_pajak_service` TINYINT(1) DEFAULT 0;
#
INSERT INTO `apps_roles_module` (`role_id`, `module_id`, `start_menu_path`, `module_order`, `createdby`, `created`, `updatedby`, `updated`, `is_active`, `is_deleted`) VALUES
(1, 170, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 170, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(1, 171, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 171, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0);
#
INSERT INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) values 
('hide_detail_taxservice',1,NULL,'2019-09-16 00:00:00','administrator',NULL,NULL,'1','0'),
('hide_detail_takeaway',1,NULL,'2019-09-16 00:00:00','administrator',NULL,NULL,'1','0'),
('hide_detail_compliment',1,NULL,'2019-09-16 00:00:00','administrator',NULL,NULL,'1','0'),
('hold_table_timer',0,NULL,'2019-09-16 00:00:00','administrator',NULL,NULL,'1','0'),
('use_block_table',0,NULL,'2019-09-16 00:00:00','administrator',NULL,NULL,'1','0'),
('billing_no_simple',0,NULL,'2019-11-11 00:00:00','administrator',NULL,NULL,'1','0'),
('mode_bazaar_foodcourt',0,NULL,'2019-11-11 00:00:00','administrator',NULL,NULL,'1','0'),
('tandai_pajak_billing',0,NULL,'2019-11-11 00:00:00','administrator',NULL,NULL,'1','0'),
('override_pajak_billing',0,NULL,'2019-11-11 00:00:00','administrator',NULL,NULL,'1','0');
#
UPDATE apps_options SET option_value = '3.42.21', updated = '2019-09-30 00:00:01' WHERE option_var IN ('wepos_version');
#
ALTER TABLE `pos_product_category` 
ADD `list_no` int(11) DEFAULT 0;
#
INSERT INTO `apps_roles_module` (`role_id`, `module_id`, `start_menu_path`, `module_order`, `createdby`, `created`, `updatedby`, `updated`, `is_active`, `is_deleted`) VALUES
(1, 172, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0),
(2, 172, NULL, 0, 'admin', '2018-09-04 10:14:10', 'admin', '2018-09-04 10:14:10', 1, 0);
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'Notes: {qc_notes}', '{qc_notes}'), 
option_value = REPLACE(option_value, 'notes: {qc_notes}', '{qc_notes}'), 
option_value = REPLACE(option_value, '01-03-2019', '01-11-2019'), 
option_value = REPLACE(option_value, '01-04-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-05-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-06-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-07-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-08-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-09-2019', '01-11-2019'),
option_value = REPLACE(option_value, '01-10-2019', '01-11-2019');
#
UPDATE apps_modules 
SET module_name = REPLACE(module_name, 'Re-Print Billing Tax', 'Set Tax Billing Trx'), 
module_description = REPLACE(module_description, 'Re-Print Billing Tax', 'Set Tax Billing Trx'), 
module_breadcrumb = REPLACE(module_breadcrumb, 'Re-Print Billing Tax', 'Set Tax Billing Trx'), 
start_menu_path = REPLACE(start_menu_path, 'Re-Print Billing Tax', 'Set Tax Billing Trx');
#
ALTER TABLE `pos_billing` 
ADD `billing_no_simple` VARCHAR(10) DEFAULT NULL,
ADD `txmark` TINYINT(1) DEFAULT 0,
ADD `txmark_no` VARCHAR(20) DEFAULT NULL,
ADD `txmark_no_simple` VARCHAR(10) DEFAULT NULL,
ADD `group_date` DATE DEFAULT NULL;
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, 'Guest: {guest}', '{guest}'), 
option_value = REPLACE(option_value, 'guest: {guest}', '{guest}');
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, "[set_tab1]\n[align=1][size=0]{tanggal_shift} {jam_shift} - by: {user}", "[align=0][size=0]Shift: {nama_shift}\n[align=0][size=0]Kasir: {user}\n[align=0][size=0]Jam: {tanggal_shift} {jam_shift}")
WHERE option_var = "cashierReceipt_settlement_layout";
#
UPDATE apps_options 
SET option_value = REPLACE(option_value, "[set_tab1b]\n[align=0][size=0]{tipe_openclose}: {shift_on}[tab]\n[align=0][size=0]{tanggal_shift} {jam_shift}[tab]", "[align=0][size=0]{tipe_openclose}: {shift_on}\n[align=0][size=0]Kasir: {shift_kasir}\n[align=0][size=0]Jam: {tanggal_shift} {jam_shift}")
WHERE option_var = "cashierReceipt_openclose_layout";
#
INSERT INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) VALUES 
('add_customer_on_cashier',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('add_sales_on_cashier',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('all_status_order_printed',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('display_kode_menu_dipencarian',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('display_kode_menu_dibilling',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('theme_print_billing',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('print_sebaris_product_name',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('hide_hold_bill_yesterday',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('mode_table_layout_cashier',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jumlah_shift',1,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('shift_active',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('settlement_per_shift',0,NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('nama_shift_1','Non Shift',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_1_start','07:00',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_1_end','23:00',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('nama_shift_2','-',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_2_start','',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_2_end','',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('nama_shift_3','-',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_3_start','',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0'),
('jam_shift_3_end','',NULL,'2019-12-08 00:00:00','administrator',NULL,NULL,'1','0');
#
UPDATE apps_options SET option_value = '3.42.21' WHERE option_var = 'wepos_version';
#
UPDATE apps_options SET option_value = 'WePOS.Cafe' WHERE option_var = 'app_name';
#
UPDATE apps_options SET option_value = 'WePOS.Cafe' WHERE option_var = 'app_name_short';
#
UPDATE apps_options SET option_value = '2019' WHERE option_var = 'app_release';
#
UPDATE pos_billing 
SET total_credit = 0 
WHERE total_cash = total_credit AND payment_id = 1 AND bank_id = 0 AND is_half_payment = 0;
#
ALTER TABLE `pos_open_close_shift` 
ADD `tanggal_jam_shift` datetime DEFAULT NULL;
#
CREATE TABLE `pos_shift_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_shift` int(11) DEFAULT NULL,
  `tanggal_shift` date DEFAULT NULL,
  `jam_shift_start` varchar(5) DEFAULT NULL,
  `jam_shift_end` varchar(5) DEFAULT NULL,
  `tanggal_jam_start` datetime DEFAULT NULL,
  `tanggal_jam_end` datetime DEFAULT NULL,
  `tipe_shift` enum('open','close') DEFAULT NULL,
  `status_active` tinyint(1) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE `pos_billing`
ADD `diskon_sebelum_pajak_service` tinyint(1) DEFAULT '0',
ADD `shift` tinyint(1) DEFAULT '0';
#
CREATE TABLE `pos_shift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_shift` varchar(100) NOT NULL,
  `jam_shift_start` varchar(5) NOT NULL DEFAULT '00:00',
  `jam_shift_end` varchar(5) NOT NULL DEFAULT '00:00',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
insert  into `pos_shift`(`id`,`nama_shift`,`jam_shift_start`,`jam_shift_end`,`createdby`,`created`,`updatedby`,`updated`,`is_deleted`) values 
(1,'Shift Pagi','07:00','23:00','administrator','2019-12-09 19:42:49','administrator','2019-12-09 19:44:33',0),
(2,'','','','administrator','2019-12-09 19:42:49','administrator','2019-12-09 19:44:33',1),
(3,'','','','administrator','2019-12-09 19:42:49','administrator','2019-12-09 19:44:33',1);
