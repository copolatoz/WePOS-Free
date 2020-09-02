/*

MIGRASI DATABASE
WePOS - Cafe: v3.42.21 ke v3.42.22
Updated: 02-09-2020 07:00

*********************************************************************

*/


UPDATE apps_options SET option_value = '3.42.22' WHERE option_var = 'wepos_version';
#
UPDATE apps_options SET option_value = 'WePOS.Cafe' WHERE option_var = 'app_name';
#
UPDATE apps_options SET option_value = 'WePOS.Cafe' WHERE option_var = 'app_name_short';
#
UPDATE apps_options SET option_value = '2020' WHERE option_var = 'app_release';
#
ALTER TABLE pos_stock_opname_detail
ADD `use_stok_kode_unik` TINYINT(1) DEFAULT '0';
#
CREATE TABLE `pos_stock_opname_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `stod_id` int(11) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_stock_koreksi_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `koreksi_id` bigint(20) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_receive_kode_unik` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `received_id` int(11) DEFAULT NULL,
  `varian_name` varchar(100) DEFAULT NULL,
  `kode_unik` varchar(255) DEFAULT NULL,
  `temp_id` varchar(255) DEFAULT NULL,
  `po_detail_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_item_category
MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
MODIFY `item_category_code` CHAR(6) NOT NULL,
ADD `as_product_category` tinyint(1) DEFAULT '0';
#
ALTER TABLE pos_product_category
MODIFY `product_category_code` CHAR(6) NOT NULL,
ADD `from_item_category` INT(11) DEFAULT '0';
#
ALTER TABLE pos_item_subcategory
MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
MODIFY `item_subcategory_code` CHAR(6) NOT NULL,
ADD `item_category_id` INT(11) NOT NULL;
#
DROP TABLE pos_item_kode_unik;
#
CREATE TABLE `pos_item_kode_unik` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) DEFAULT NULL,
  `kode_unik` varchar(255) NOT NULL,
  `ref_in` varchar(50) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `ref_out` varchar(50) DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `storehouse_id` smallint(6) DEFAULT NULL,
  `qty_kode` smallint(6) DEFAULT '1',
  `item_hpp` double DEFAULT '0',
  `varian_name` varchar(50) DEFAULT NULL,
  `varian_group` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `use_tax` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_item_kode_unik_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kode_unik_id` bigint(20) DEFAULT NULL,
  `ref_in` varchar(50) DEFAULT NULL,
  `date_in` datetime DEFAULT NULL,
  `ref_out` varchar(50) DEFAULT NULL,
  `date_out` datetime DEFAULT NULL,
  `storehouse_id` smallint(6) DEFAULT NULL,
  `item_hpp` double DEFAULT '0',
  `item_sales` double DEFAULT NULL,
  `varian_name` varchar(50) DEFAULT NULL,
  `varian_group` varchar(50) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_customer
MODIFY `customer_code` VARCHAR(20) NOT NULL,
ADD `customer_city` VARCHAR(255) DEFAULT NULL,
ADD `limit_kredit` DOUBLE DEFAULT '0',
ADD `termin` smallint(6) DEFAULT NULL;
#
ALTER TABLE pos_supplier
MODIFY `supplier_code` VARCHAR(20) NOT NULL,
ADD `supplier_city` VARCHAR(255) DEFAULT NULL,
ADD `supplier_termin` smallint(6) DEFAULT NULL;
#
ALTER TABLE pos_po_detail
ADD `po_detail_tax` double DEFAULT '0';
#
CREATE TABLE `pos_purchasing` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `purchasing_number` VARCHAR(20) NOT NULL,
  `supplier_id` INT(11) DEFAULT NULL,
  `supplier_invoice` VARCHAR(100) DEFAULT NULL,
  `purchasing_date` DATE DEFAULT NULL,
  `purchasing_total_qty` FLOAT DEFAULT '0',
  `purchasing_sub_total` DOUBLE DEFAULT NULL,
  `purchasing_discount` DOUBLE DEFAULT NULL,
  `purchasing_tax` DOUBLE DEFAULT NULL,
  `purchasing_shipping` DOUBLE DEFAULT NULL,
  `purchasing_total_price` DOUBLE DEFAULT '0',
  `purchasing_payment` ENUM('cash','credit') NOT NULL DEFAULT 'cash',
  `purchasing_status` ENUM('progress','done','cancel') NOT NULL DEFAULT 'progress',
  `purchasing_memo` TINYTEXT,
  `purchasing_project` VARCHAR(100) DEFAULT NULL,
  `purchasing_ship_to` VARCHAR(100) DEFAULT NULL,
  `createdby` VARCHAR(50) DEFAULT NULL,
  `created` TIMESTAMP NULL DEFAULT NULL,
  `updatedby` VARCHAR(50) DEFAULT NULL,
  `updated` TIMESTAMP NULL DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT '1',
  `is_deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `approval_status` ENUM('progress','done') DEFAULT NULL,
  `use_approval` TINYINT(1) DEFAULT '0',
  `storehouse_id` INT(11) DEFAULT '0',
  `purchasing_termin` TINYINT(4) DEFAULT '0',
  `purchasing_storehouse` INT(11) DEFAULT NULL,
  `use_tax` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number_idx` (`purchasing_number`),
  KEY `fk_po_supplier` (`supplier_id`)
) ENGINE=INNODB;
#
CREATE TABLE `pos_purchasing_detail` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `purchasing_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `purchasing_detail_purchase` DOUBLE DEFAULT NULL,
  `purchasing_detail_qty` FLOAT DEFAULT NULL,
  `unit_id` INT(11) DEFAULT NULL,
  `purchasing_detail_total` DOUBLE DEFAULT '0',
  `supplier_item_id` INT(11) DEFAULT NULL,
  `purchasing_detail_potongan` DOUBLE DEFAULT '0',
  `temp_id` VARCHAR(20) DEFAULT NULL,
  `from_supplier_item` TINYINT(1) DEFAULT '0',
  `storehouse_id` INT(11) DEFAULT '0',
  `use_stok_kode_unik` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_po_detail_po` (`purchasing_id`),
  KEY `fk_po_detail_barang` (`item_id`)
) ENGINE=INNODB;
#
CREATE TABLE `pos_purchasing_kode_unik` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `purchasingd_id` INT(11) DEFAULT NULL,
  `varian_name` VARCHAR(100) DEFAULT NULL,
  `kode_unik` VARCHAR(255) DEFAULT NULL,
  `temp_id` VARCHAR(20) DEFAULT NULL,
  `item_id` INT(11) DEFAULT NULL,
  `use_tax` TINYINT(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=INNODB;
#
CREATE TABLE `pos_varian_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `varian_name` varchar(100) NOT NULL,
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE `acc_account_payable`
ADD `purchasing_id` INT(11) DEFAULT NULL;
#
INSERT INTO `apps_options`(`option_var`,`option_value`,`option_description`,`created`,`createdby`,`updated`,`updatedby`,`is_active`,`is_deleted`) VALUES 
('nontrx_override_on',0,NULL,'2020-07-07 00:00:00','administrator',NULL,NULL,'1','0'),
('nontrx_sales_auto',0,NULL,'2020-07-07 00:00:00','administrator',NULL,NULL,'1','0'),
('nontrx_backup_onsettlement',0,NULL,'2020-07-07 00:00:00','administrator',NULL,NULL,'1','0'),
('allow_app_all_user',0,NULL,'2020-07-07 00:00:00','administrator',NULL,NULL,'1','0'),
('standalone_cashier',0,NULL,'2020-07-07 00:00:00','administrator',NULL,NULL,'1','0');;
#
CREATE TABLE `pos_nontrx_target` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) DEFAULT '0',
  `nontrx_only_dinein` tinyint(1) DEFAULT '0',
  `nontrx_tahun` mediumint(9) DEFAULT '0',
  `nontrx_bulan` tinyint(4) DEFAULT '0',
  `nontrx_bulan_target` double DEFAULT '0',
  `nontrx_bulan_realisasi` double DEFAULT '0',
  `nontrx_minggu` smallint(6) DEFAULT '0',
  `nontrx_minggu_target` double DEFAULT '0',
  `nontrx_minggu_realisasi` double DEFAULT '0',
  `nontrx_curr_minggu` mediumint(9) DEFAULT '0',
  `nontrx_hari` tinyint(1) DEFAULT '0',
  `nontrx_hari_target` double DEFAULT '0',
  `nontrx_hari_realisasi` double DEFAULT '0',
  `nontrx_curr_tanggal` date DEFAULT NULL,
  `nontrx_shift1` tinyint(1) DEFAULT '0',
  `nontrx_shift1_target` double DEFAULT '0',
  `nontrx_shift1_realisasi` double DEFAULT '0',
  `nontrx_shift2` tinyint(1) DEFAULT '0',
  `nontrx_shift2_target` double DEFAULT '0',
  `nontrx_shift2_realisasi` double DEFAULT '0',
  `nontrx_shift3` tinyint(1) DEFAULT '0',
  `nontrx_shift3_target` double DEFAULT '0',
  `nontrx_shift3_realisasi` double DEFAULT '0',
  `nontrx_range_sales_from` double DEFAULT '0',
  `nontrx_range_sales_till` double DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `nontrx_range_jam_from` char(5) DEFAULT '08:00',
  `nontrx_range_jam_till` char(5) DEFAULT '22:00',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_nontrx_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `nontrx_tanggal` date DEFAULT NULL,
  `nontrx_tahun` mediumint(9) DEFAULT '0',
  `nontrx_bulan` tinyint(4) DEFAULT '0',
  `nontrx_minggu` smallint(6) DEFAULT '0',
  `nontrx_hari_realisasi` double DEFAULT '0',
  `nontrx_shift1_realisasi` double DEFAULT '0',
  `nontrx_shift2_realisasi` double DEFAULT '0',
  `nontrx_shift3_realisasi` double DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_billing_trx` (
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
  `block_table` tinyint(1) DEFAULT '0',
  `is_reservation` tinyint(1) DEFAULT '0',
  `billing_no_simple` varchar(10) DEFAULT NULL,
  `txmark` tinyint(1) DEFAULT '0',
  `txmark_no` varchar(20) DEFAULT NULL,
  `txmark_no_simple` varchar(10) DEFAULT NULL,
  `group_date` date DEFAULT NULL,
  `diskon_sebelum_pajak_service` tinyint(1) DEFAULT '0',
  `shift` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_no` (`billing_no`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_billing_detail_trx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` mediumint(9) NOT NULL,
  `order_qty` float DEFAULT '0',
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
  `storehouse_id` int(11) DEFAULT '0',
  `diskon_sebelum_pajak_service` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE `pos_billing_detail`
MODIFY `order_qty` float DEFAULT '0';
#
ALTER TABLE `pos_closing_sales`
ADD `discount_billing` double DEFAULT '0',
ADD `discount_item` double DEFAULT '0',
ADD `total_payment_4` double DEFAULT '0',
ADD `qty_payment_4` smallint(6) DEFAULT '0';
#
CREATE TABLE `pos_closing_sales_trx` (
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
  `total_payment_4` double DEFAULT '0',
  `qty_payment_4` smallint(6) DEFAULT '0',
  `createdby` varchar(50) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `updatedby` varchar(50) DEFAULT NULL,
  `updated` timestamp NULL DEFAULT NULL,
  `discount_billing` double DEFAULT '0',
  `discount_item` double DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_product DROP INDEX item_product_idx;
#
ALTER TABLE pos_items DROP INDEX item_code;
#
ALTER TABLE pos_item_category DROP INDEX item_category_code;
#
ALTER TABLE pos_bank DROP INDEX bank_code_idx;
#
UPDATE apps_modules SET module_breadcrumb = '3. Cashier & Reservation>Non-Tax/Compliment>Set Manual Non-Tax/Compliment', 
start_menu_path = '3. Cashier & Reservation>Non-Tax/Compliment>Set Manual Non-Tax/Compliment', 
module_name = 'Set Manual Non-Tax/Compliment', 
module_description = 'Set Manual Non-Tax/Compliment'
WHERE module_controller = 'reprintBillingTax';
#
UPDATE apps_modules SET is_active = 0
WHERE module_controller IN ('cashierExpress','billingCashierApp');
#
CREATE TABLE `pos_billing_detail_gramasi` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `billing_id` int(11) DEFAULT NULL,
  `billing_detail_id` int(11) DEFAULT NULL,
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
  `item_hpp` double DEFAULT '0',
  `unit_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
CREATE TABLE `pos_billing_detail_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billing_id` int(11) NOT NULL,
  `billing_detail_id` int(11) DEFAULT NULL,
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
  `product_qty` float DEFAULT '0',
  `product_varian_id_item` int(11) DEFAULT '0',
  `varian_id_item` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#
ALTER TABLE pos_printer
MODIFY `print_method` ENUM('ESC/POS','JSPRINT','BROWSER','RAWBT') DEFAULT 'ESC/POS';
#
CREATE VIEW `pos_billing_transaksi` AS (SELECT billing_no AS no_billing,payment_date AS tanggal_billing,total_billing AS subtotal,discount_total AS diskon, service_total AS service_charge, tax_total AS pajak, grand_total FROM pos_billing_trx);
