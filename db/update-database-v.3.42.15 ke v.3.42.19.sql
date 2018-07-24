
#Migrasi v3.42.15 ke v3.42.19

ALTER TABLE `acc_account_payable`
ADD `no_kontrabon` varchar(30) DEFAULT NULL;

ALTER TABLE `acc_autoposting`
MODIFY `autoposting_tipe` enum('purchasing','sales','other','pelunasan_account_payable','account_payable','account_receivable','pembayaran_account_receivable','cashflow_penerimaan','cashflow_pengeluaran','cashflow_mutasi_kas_bank') DEFAULT 'other';

ALTER TABLE `apps_clients`
ADD `client_ip` char(30) DEFAULT NULL,
ADD `mysql_user` char(30) DEFAULT NULL,
ADD `mysql_pass` varchar(100) DEFAULT NULL,
ADD `mysql_port` char(10) DEFAULT NULL,
ADD `mysql_database` char(100) DEFAULT NULL;

ALTER TABLE `apps_supervisor_log`
MODIFY `supervisor_access_id` int(11) DEFAULT NULL,
MODIFY `supervisor_access` char(100) DEFAULT NULL,
MODIFY `log_data` text NOT NULL,
ADD `ref_id_1` varchar(50) DEFAULT '',
ADD `ref_id_2` varchar(50) DEFAULT '';

ALTER TABLE `pos_billing`
ADD  `block_table` tinyint(1) DEFAULT '0',
ADD  `is_reservation` tinyint(1) DEFAULT '0';

ALTER TABLE `pos_billing_detail`
ADD  `product_type` enum('item','package') DEFAULT 'item',
ADD  `package_item` tinyint(1) DEFAULT '0';

ALTER TABLE `pos_billing_detail_split`
ADD  `product_type` enum('item','package') DEFAULT 'item',
ADD  `package_item` tinyint(1) DEFAULT '0';

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `pos_customer`
ADD `keterangan_blacklist` varchar(255) DEFAULT NULL;

ALTER TABLE `pos_item_kode_unik`
ADD `item_hpp` double DEFAULT '0';

ALTER TABLE `pos_items`
MODIFY `min_stock` float DEFAULT '0',
ADD `item_sku` varchar(50) DEFAULT NULL;


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `pos_printer`
ADD `print_method` enum('ESC/POS','JSPRINT','BROWSER') DEFAULT 'ESC/POS',
ADD `print_logo` tinyint(1) DEFAULT '0';

ALTER TABLE `pos_product_gramasi`
ADD `product_varian_id` int(11) DEFAULT '0',
ADD `varian_id` int(11) DEFAULT '0';

ALTER TABLE `pos_product_package`
ADD `normal_price` double DEFAULT '0',
ADD `has_varian` smallint(6) DEFAULT '0',
ADD `product_varian_id` int(11) DEFAULT '0',
ADD `varian_id` int(11) DEFAULT '0';

ALTER TABLE `pos_receiving`
MODIFY `no_surat_jalan` varchar(100) DEFAULT '';
  

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `pos_supplier`
ADD `supplier_status` enum('ok','warning','blacklist') DEFAULT 'ok',
ADD `keterangan_blacklist` varchar(255) DEFAULT NULL;
  
ALTER TABLE `pos_supplier_item`
MODIFY `item_price` double DEFAULT '0',
MODIFY `item_hpp` double DEFAULT '0',
ADD `last_in` double DEFAULT '0',
ADD `old_last_in` double DEFAULT '0';
  
ALTER TABLE `pos_table`
ADD `room_id` int(11) DEFAULT '0',
ADD `kapasitas` smallint(6) DEFAULT '0',
ADD `table_tipe` enum('dinein','takeaway','delivery') NOT NULL DEFAULT 'dinein';
  
