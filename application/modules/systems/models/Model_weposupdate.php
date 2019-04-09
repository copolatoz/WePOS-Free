<?php
class Model_weposupdate extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'options';
	}
	
	function checkClient($check = array())
	{
		extract($check);
		
		$this->load->helper('directory');
		$this->load->helper('file');
		
		if(!empty($check)){
			
			$reset = false;
			if(empty($check['merchant_xid']) AND $check['merchant_verified'] == 'unverified'){
				$reset = true;
			}
			
			if(empty($check['merchant_cor_token']) OR empty($check['merchant_acc_token']) OR empty($check['merchant_mkt_token'])){
				$reset = true;
			}
			
			if(empty($check['produk_nama']) OR empty($check['produk_expired'])){
				$reset = true;
			}else{
				if($check['produk_nama'] == 'Gratis / Free'){
					$reset = true;
				}
				
				if($check['produk_expired'] == 'unlimited'){
					$reset = true;
				}
				
			}
			
			if(empty($check['produk_nama']) OR empty($check['produk_expired'])){
				$reset = true;
			}
			
			$merchant_key = '';
			if(empty($check['$merchant_key'])){
				$reset = false;
			}else{
				$merchant_key = $check['$merchant_key'];
			}
			
			$merchant_last_check = 0;
			if(!empty($check['merchant_last_check'])){
				$merchant_last_check = $check['merchant_last_check'];
			}
			
			$today_check = strtotime(date("d-m-Y H:i:s"));
			$month_check = strtotime(date("d-m-Y H:i:s")) + (ONE_DAY_UNIX*15);
			
			$allow_reset = false;
				
			if(empty($merchant_last_check)){
				
				$allow_reset = true;
				$opt_var = array(
					'merchant_last_check' => $month_check
				);
				
				update_option($opt_var);
				
			}else{
				
				$merchant_last_check_7 = $merchant_last_check + (ONE_DAY_UNIX*7);
				if($merchant_last_check_7 < $today_check){
					$allow_reset = true;
				}else{
					if($merchant_last_check > $month_check){
						$allow_reset = true;
					}
				}
				
				if($allow_reset == true){
					
					$opt_var = array(
						'merchant_last_check' => $today_check
					);
					update_option($opt_var);
					
				}
				
			}
	
			$opt_var = array(
				'mlog_'.$merchant_key,
				'is_cloud'
			);
			$get_opt = get_option_value($opt_var);
			
			if(!empty($check['merchant_mkt_token'])){
				if($check['merchant_mkt_token'] < $today_check){
					
					$mlog = '';
					if(empty($get_opt['mlog_'.$merchant_key])){
						$mlog = $get_opt['mlog_'.$merchant_key];
					}
					
					$resetapp = array(
						'merchant_cor_token'=> '',
						'merchant_acc_token'=> '',
						'merchant_mkt_token'=> '',
						'produk_key' 		=> 'GFR-'.strtotime(date("d-m-Y")),
						'produk_nama'		=> 'Gratis / Free',
						'produk_expired'	=> 'unlimited',
						'mlog_'.$merchant_key => ''
					);
					update_option($resetapp);
					
					if(!empty($mlog) AND empty($get_opt['is_cloud'])){
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						$mlog_json = json_decode($mlog);
						if(!empty($mlog_json)){
							foreach($mlog_json as $v){
								$file_minjs = $minjs_path.'/'.$v;
								@unlink($file_minjs);
							}
						}
					}
					
					$reset = true;
					$allow_reset = true;
				}
			}
			
			if($reset == true AND $allow_reset == true){
				if(!function_exists('doresetapp')){
					
					$this->db->query('TRUNCATE TABLE '.$this->prefix.'modules');
					$this->db->query("INSERT INTO ".$this->prefix."modules (`id`, `module_name`, `module_author`, `module_version`, `module_description`, `module_folder`, `module_controller`, `module_is_menu`, `module_breadcrumb`, `module_order`, `module_icon`, `module_shortcut_icon`, `module_glyph_icon`, `module_glyph_font`, `module_free`, `running_background`, `show_on_start_menu`, `show_on_right_start_menu`, `start_menu_path`, `start_menu_order`, `start_menu_icon`, `start_menu_glyph`, `show_on_context_menu`, `context_menu_icon`, `context_menu_glyph`, `show_on_shorcut_desktop`, `desktop_shortcut_icon`, `desktop_shortcut_glyph`, `show_on_preference`, `preference_icon`, `preference_glyph`, `createdby`, `created`, `updatedby`, `updated`, `is_active`, `is_deleted`) VALUES (1, 'Setup Aplikasi', 'dev@wepos.id', 'v.1.0', '', 'systems', 'setupAplikasiFree', 1, '1. Master Aplikasi>Setup Aplikasi', 1, 'icon-cog', 'icon-cog', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Setup Aplikasi', 1000, 'icon-cog', '', 0, 'icon-cog', '', 1, 'icon-cog', '', 0, 'icon-cog', '', 'administrator', '2019-03-07 01:52:11', 'administrator', '2019-03-07 17:00:00', 1, 0),(2, 'Client Info', 'dev@wepos.id', 'v.1.0.0', 'Client Info', 'systems', 'clientInfo', 0, '1. Master Aplikasi>Client Info', 1, 'icon-home', 'icon-home', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Client Info', 1101, 'icon-home', '', 0, 'icon-home', '', 1, 'icon-home', '', 1, 'icon-home', '', 'administrator', '2019-03-07 00:47:08', 'administrator', '2019-03-07 00:47:08', 1, 0),(3, 'Client Unit', 'dev@wepos.id', 'v.1.0', '', 'systems', 'DataClientUnit', 1, '1. Master Aplikasi>Client Unit', 1, 'icon-building', 'icon-building', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Client Unit', 1102, 'icon-building', '', 0, 'icon-building', '', 1, 'icon-building', '', 1, 'icon-building', '', 'administrator', '2019-03-07 01:52:10', 'administrator', '2019-03-07 17:00:00', 1, 0),(4, 'Data Structure', 'dev@wepos.id', 'v.1.0', '', 'systems', 'DataStructure', 1, '1. Master Aplikasi>Data Structure', 1, 'icon-building', 'icon-building', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Data Structure', 1103, 'icon-building', '', 0, 'icon-building', '', 1, 'icon-building', '', 1, 'icon-building', '', 'administrator', '2019-03-07 01:52:11', 'administrator', '2019-03-07 17:00:00', 1, 0),(5, 'Role Manager', 'dev@wepos.id', 'v.1.2', 'Role Manager', 'systems', 'Roles', 1, '1. Master Aplikasi>Role Manager', 1, 'icon-role-modules', 'icon-role-modules', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Role Manager', 1201, 'icon-role-modules', '', 0, 'icon-role-modules', '', 1, 'icon-role-modules', '', 1, 'icon-role-modules', '', 'administrator', '2019-03-07 01:52:15', 'administrator', '2019-03-07 17:00:00', 1, 0),(6, 'Data User', 'dev@wepos.id', 'v.1.0', '', 'systems', 'UserData', 1, '1. Master Aplikasi>Data User', 1, 'icon-user-data', 'icon-user-data', '', '', 1, 0, 1, 0, '1. Master Aplikasi>Data User', 1203, 'icon-user-data', '', 0, 'icon-user-data', '', 1, 'icon-user-data', '', 0, 'icon-user-data', '', 'administrator', '2019-03-07 01:52:11', 'administrator', '2019-03-07 17:00:00', 1, 0),(7, 'User Profile', 'dev@wepos.id', 'v.1.0', '', 'systems', 'UserProfile', 1, '1. Master Aplikasi>User Profile', 1, 'user', 'user', '', '', 1, 0, 1, 1, '1. Master Aplikasi>User Profile', 1301, 'user', '', 1, 'user', '', 1, 'user', '', 1, 'user', '', 'administrator', '2019-03-07 01:52:17', 'administrator', '2019-03-07 17:00:00', 1, 0),(8, 'Desktop Shortcuts', 'dev@wepos.id', 'v.1.0', 'Shortcuts Manager to Desktop', 'systems', 'DesktopShortcuts', 1, '1. Master Aplikasi>Desktop Shortcuts', 1, 'icon-preferences', 'icon-preferences', '', '', 1, 0, 1, 1, '1. Master Aplikasi>Desktop Shortcuts', 1302, 'icon-preferences', '', 1, 'icon-preferences', '', 1, 'icon-preferences', '', 1, 'icon-preferences', '', 'administrator', '2019-03-07 01:52:12', 'administrator', '2019-03-07 17:00:00', 1, 0),(9, 'QuickStart Shortcuts', 'dev@wepos.id', 'v.1.0', '', 'systems', 'QuickStartShortcuts', 0, '1. Master Aplikasi>QuickStart Shortcuts', 1, 'icon-preferences', 'icon-preferences', '', '', 1, 0, 1, 0, '1. Master Aplikasi>QuickStart Shortcuts', 1303, 'icon-preferences', '', 0, 'icon-preferences', '', 1, 'icon-preferences', '', 1, 'icon-preferences', '', 'administrator', '2019-03-07 00:43:19', 'administrator', '2019-03-07 02:16:19', 1, 0),(10, 'Refresh Aplikasi', 'dev@wepos.id', 'v.1.0.0', '', 'systems', 'refreshModule', 0, 'Refresh Aplikasi', 1, 'icon-refresh', 'icon-refresh', '', '', 1, 0, 0, 0, 'Refresh Aplikasi', 1304, 'icon-refresh', '', 0, 'icon-refresh', '', 1, 'icon-refresh', '', 0, 'icon-refresh', '', 'administrator', '2019-03-07 08:00:19', 'administrator', '2019-03-07 08:00:19', 1, 0),(11, 'Lock Screen', 'dev@wepos.id', 'v.1.0.0', 'User Lock Screen', 'systems', 'lockScreen', 0, 'LockScreen', 1, 'icon-grid', 'icon-grid', '', '', 1, 1, 0, 0, 'LockScreen', 1305, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-02-16 18:40:20', 'administrator', '2019-03-07 17:00:00', 1, 0),(12, 'Logout', 'dev@wepos.id', 'v.1.0.0', 'Just Logout Module', 'systems', 'logoutModule', 0, 'Logout', 1, 'icon-grid', 'icon-grid', '', '', 1, 1, 0, 0, 'Logout', 1306, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-02-16 18:36:16', 'administrator', '2019-03-07 08:06:35', 1, 0),(13, 'WePOS Update', 'dev@wepos.id', 'v.1.0.0', 'WePOS Update', 'systems', 'weposUpdate', 0, '1. Master Aplikasi>WePOS Update', 1, 'icon-sync', 'icon-grid', '', '', 1, 0, 1, 0, '1. Master Aplikasi>WePOS Update', 1401, 'icon-sync', '', 0, 'icon-sync', '', 1, 'icon-sync', '', 1, 'icon-sync', '', 'administrator', '2019-03-07 01:00:58', 'administrator', '2019-03-07 01:00:58', 1, 0),(14, 'Notifikasi Sistem', 'dev@wepos.id', 'v.1.0.0', 'Notifikasi Sistem', 'systems', 'systemNotify', 0, 'Notifikasi Sistem', 1, 'icon-info', 'icon-info', '', '', 1, 1, 0, 0, 'Notifikasi Sistem', 1402, 'icon-info', '', 0, 'icon-info', '', 0, 'icon-info', '', 0, 'icon-info', '', 'administrator', '2019-03-07 01:00:58', 'administrator', '2019-03-07 01:00:58', 1, 0),(15, 'Menu Category', 'dev@wepos.id', 'v.1.0', '', 'master_pos', 'productCategory', 0, '2. Master POS>Menu Category', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Menu Category', 2101, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:26:07', 'administrator', '2019-03-07 17:00:00', 1, 0),(16, 'Master Menu & Package', 'dev@wepos.id', 'v.1.0', 'Master Menu & Package', 'master_pos', 'masterProduct', 0, '2. Master POS>Master Menu', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Menu', 2102, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:24:38', 'administrator', '2019-03-07 17:00:00', 1, 0),(19, 'Master Warehouse', 'dev@wepos.id', 'v.1.0.0', 'Master Warehouse', 'master_pos', 'masterStoreHouse', 0, '2. Master POS>Master Warehouse', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Warehouse', 2201, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 20:24:56', 'administrator', '2019-03-07 13:05:16', 1, 0),(20, 'Master Unit', 'dev@wepos.id', 'v.1.0.0', 'Master Unit', 'master_pos', 'masterUnit', 0, '2. Master POS>Master Unit', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Unit', 2202, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 20:25:13', 'administrator', '2019-03-07 15:15:29', 1, 0),(21, 'Master Supplier', 'dev@wepos.id', 'v.1.0.0', 'Master Supplier', 'master_pos', 'masterSupplier', 0, '2. Master POS>Supplier', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Supplier', 2203, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 20:25:04', 'administrator', '2019-03-07 13:04:34', 1, 0),(22, 'Item Category', 'dev@wepos.id', 'v.1.0.0', 'Item Category', 'master_pos', 'itemCategory', 0, '2. Master POS>Item Category', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Item Category', 2210, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 17:36:29', 'administrator', '2019-03-07 13:31:54', 1, 0),(23, 'Item Sub Category', 'dev@wepos.id', 'v.1.0.0', 'Item Sub Category', 'master_pos', 'itemSubCategory', 0, '2. Master POS>Item Sub Category', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Item Sub Category', 2211, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 17:36:29', 'administrator', '2019-03-07 13:31:54', 1, 0),(24, 'Master Item', 'dev@wepos.id', 'v.1.0.0', 'Data Item', 'master_pos', 'masterItemCafe', 0, '2. Master POS>Master Item', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Item', 2230, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 07:04:34', 'administrator', '2019-03-07 07:04:34', 1, 0),(25, 'Discount Planner', 'dev@wepos.id', 'v.1.0', 'Planning All discount Menu', 'master_pos', 'discountPlannerFree', 0, '2. Master POS>Discount Planner', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Discount Planner', 2301, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:26:01', 'administrator', '2019-03-07 17:00:00', 1, 0),(26, 'Printer Manager', 'dev@wepos.id', 'v.1.0', 'Printer Manager', 'master_pos', 'masterPrinter', 0, '2. Master POS>Printer Manager', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Printer Manager', 2302, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 20:24:50', 'administrator', '2019-03-07 13:06:25', 1, 0),(28, 'Master Bank', 'dev@wepos.id', 'v.1.0.0', 'Master Bank', 'master_pos', 'masterBank', 0, '2. Master POS>Master Bank', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Bank', 2304, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 20:24:53', 'administrator', '2019-03-07 13:05:03', 1, 0),(31, 'Master Floor Plan', 'dev@wepos.id', 'v.1.0', '', 'master_pos', 'masterFloorplan', 0, '2. Master POS>Master Floor Plan', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Floor Plan', 2307, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 10:26:51', 'administrator', '2019-03-07 17:00:00', 1, 0),(32, 'Master Room', 'dev@wepos.id', 'v.1.0', 'Master Room', 'master_pos', 'masterRoom', 0, '2. Master POS>Master Room', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Room', 2308, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:24:38', 'administrator', '2019-03-07 17:00:00', 1, 0),(33, 'Master Table', 'dev@wepos.id', 'v.1.0.0', '', 'master_pos', 'masterTable', 0, '2. Master POS>Master Table', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Master Table', 2309, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 10:26:54', 'administrator', '2019-03-07 17:00:00', 1, 0),(34, 'Table Inventory', 'dev@wepos.id', 'v.1.0.0', '', 'master_pos', 'tableInventory', 0, '2. Master POS>Table Inventory', 2, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>Table Inventory', 2310, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 10:26:59', 'administrator', '2019-03-07 17:00:00', 1, 0),(35, 'Warehouse Access', 'dev@wepos.id', 'v.1.0.0', 'Warehouse Access', 'master_pos', 'warehouseAccess', 0, '2. Master POS>User Access>Warehouse Access', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>User Access>Warehouse Access', 2401, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-02-27 12:23:32', 'administrator', '2019-03-07 13:02:49', 1, 0),(36, 'Printer Access', 'dev@wepos.id', 'v.1.0.0', 'Printer Access', 'master_pos', 'printerAccess', 0, '2. Master POS>User Access>Printer Access', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>User Access>Printer Access', 2402, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 23:43:42', 'administrator', '2019-03-07 13:02:38', 1, 0),(37, 'Supervisor Access', 'dev@wepos.id', 'v.1.0.0', 'Supervisor Access', 'master_pos', 'supervisorAccess', 0, '2. Master POS>User Access>Supervisor Access', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '2. Master POS>User Access>Supervisor Access', 2403, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-02-11 15:53:04', 'administrator', '2019-03-07 13:02:58', 1, 0),(39, 'Open Cashier (Shift)', 'dev@wepos.id', 'v.1.0', '', 'cashier', 'openCashierShift', 0, '3. Cashier & Reservation>Open Cashier (Shift)', 7, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '3. Cashier & Reservation>Open Cashier (Shift)', 3001, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:28:12', 'administrator', '2019-03-07 17:00:00', 1, 0),(40, 'Close Cashier (Shift)', 'dev@wepos.id', 'v.1.0', '', 'cashier', 'closeCashierShift', 0, '3. Cashier & Reservation>Close Cashier (Shift)', 7, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '3. Cashier & Reservation>Close Cashier (Shift)', 3002, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 10:28:17', 'administrator', '2019-03-07 17:00:00', 1, 0),(41, 'List Open Close Cashier', 'dev@wepos.id', 'v.1.0.0', '', 'cashier', 'listOpenCloseCashier', 0, '3. Cashier & Reservation>List Open Close Cashier', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '3. Cashier & Reservation>List Open Close Cashier', 3003, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 00:59:55', 'administrator', '2019-03-07 00:59:55', 1, 0),(42, 'Cashier', 'dev@wepos.id', 'v.1.0', 'Cashier', 'cashier', 'billingCashier', 0, '3. Cashier & Reservation>Cashier', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '3. Cashier & Reservation>Cashier', 3101, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 20:28:03', 'administrator', '2019-03-07 05:58:59', 1, 0),(48, 'Cashier Receipt Setup', 'dev@wepos.id', 'v.1.0.0', 'Cashier Receipt Setup', 'cashier', 'cashierReceiptSetup', 0, '3. Cashier & Reservation>Cashier Receipt Setup', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '3. Cashier & Reservation>Cashier Receipt Setup', 3301, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 23:13:49', 'administrator', '2019-03-07 05:59:09', 1, 0),(51, 'Purchase Order/Pembelian', 'dev@wepos.id', 'v.1.0.0', 'Purchase Order/Pembelian', 'purchase', 'purchaseOrder', 0, '4. Purchase & Receive>Purchase Order/Pembelian', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '4. Purchase & Receive>Purchase Order/Pembelian', 4201, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 20:27:18', 'administrator', '2019-03-07 08:07:08', 1, 0),(52, 'Receiving List/Penerimaan Barang', 'dev@wepos.id', 'v.1.0.0', 'Receiving List/Penerimaan Barang', 'inventory', 'receivingList', 0, '4. Purchase & Receive>Receiving List/Penerimaan Barang', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '4. Purchase & Receive>Receiving List/Penerimaan Barang', 4301, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 05:05:57', 'administrator', '2019-03-07 06:04:22', 1, 0),(53, 'Daftar Stok Barang', 'dev@wepos.id', 'v.1.0.0', 'Daftar Stok Barang', 'inventory', 'listStock', 0, '5. Inventory>Daftar Stok Barang', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '5. Inventory>Daftar Stok Barang', 5101, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 23:43:42', 'administrator', '2019-03-07 06:22:20', 1, 0),(58, 'Stock Opname', 'dev@wepos.id', 'v.1.0.0', 'Module Stock Opname', 'inventory', 'stockOpname', 0, '5. Inventory>Stock Opname', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '5. Inventory>Stock Opname', 5401, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 05:06:05', 'administrator', '2019-03-07 06:22:51', 1, 0),(77, 'Closing Sales', 'dev@wepos.id', 'v.1.0.0', 'Closing Sales', 'audit_closing', 'closingSales', 0, '8. Closing & Audit>Closing Sales', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '8. Closing & Audit>Closing Sales', 8101, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 14:43:42', 'administrator', '2019-03-07 14:43:42', 1, 0),(78, 'Closing Purchasing', 'dev@wepos.id', 'v.1.0.0', 'Closing Purchasing', 'audit_closing', 'closingPurchasing', 0, '8. Closing & Audit>Closing Purchasing', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '8. Closing & Audit>Closing Purchasing', 8102, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 14:47:56', 'administrator', '2019-03-07 14:51:27', 1, 0),(82, 'Backup Master Data', 'dev@wepos.id', 'v.1.0.0', 'Syncronize Master Data Store', 'sync_backup', 'syncData', 0, '9. Sync, Backup, Generate>Syncronize Master Data Store', 1, 'icon-sync', 'icon-sync', '', '', 1, 0, 1, 0, '9. Sync, Backup, Generate>Syncronize Master Data Store', 9201, 'icon-sync', '', 0, 'icon-sync', '', 1, 'icon-sync', '', 1, 'icon-sync', '', 'administrator', '2019-02-25 05:14:44', 'administrator', '2019-02-26 14:05:47', 1, 0),(83, 'Backup Data Transaksi', 'dev@wepos.id', 'v.1.0.0', 'Backup Transaksi Store', 'sync_backup', 'backupTrx', 0, '9. Sync, Backup, Generate>Backup Transaksi Store', 1, 'icon-backup', 'icon-backup', '', '', 1, 0, 1, 0, '9. Sync, Backup, Generate>Backup Transaksi Store', 9202, 'icon-backup', '', 0, 'icon-backup', '', 1, 'icon-backup', '', 1, 'icon-backup', '', 'administrator', '2019-02-25 05:17:26', 'administrator', '2019-02-26 14:06:01', 1, 0),(86, 'Sales Report', 'dev@wepos.id', 'v.1.0', 'Sales Report', 'billing', 'reportSales', 0, '6. Reports>Sales (Billing)>Sales Report', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Billing)>Sales Report', 6101, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 18:28:24', 'administrator', '2019-03-07 10:01:16', 1, 0),(89, 'Sales Report (Recap)', 'dev@wepos.id', 'v.1.0.0', '', 'billing', 'reportSalesRecap', 0, '6. Reports>Sales (Billing)>Sales Report (Recap)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Billing)>Sales Report (Recap)', 6104, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 09:30:29', 'administrator', '2019-03-07 09:38:02', 1, 0),(90, 'Sales By Discount', 'dev@wepos.id', 'v.1.0.0', 'Sales By Discount', 'billing', 'salesByDiscount', 0, '6. Reports>Sales (Billing)>Sales By Discount', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Billing)>Sales By Discount', 6105, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 13:43:42', 'administrator', '2019-03-07 13:43:42', 1, 0),(92, 'Sales Summary Report (SSR)', 'dev@wepos.id', 'v.1.0.0', 'Sales Summary Report (SSR)', 'billing', 'salesSummaryReport', 0, '6. Reports>Sales (Billing)>Sales Summary Reports (SSR)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Billing)>Sales Summary Reports (SSR)', 6108, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 13:43:42', 'administrator', '2019-03-07 13:43:42', 1, 0),(99, 'Cancel Billing Report', 'dev@wepos.id', 'v.1.0.0', '', 'billing', 'reportCancelBill', 0, '6. Reports>Sales (Billing)>Report Cancel Billing', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Billing)>Report Cancel Billing', 6113, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-02-19 02:45:34', 'administrator', '2019-03-07 09:26:54', 1, 0),(102, 'Sales By Menu', 'dev@wepos.id', 'v.1.0.0', 'Sales By Menu', 'billing', 'reportSalesByMenu', 0, '6. Reports>Sales (Menu)>Sales By Menu', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Menu)>Sales By Menu', 6120, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-02-08 22:51:55', 'administrator', '2019-03-07 10:47:33', 1, 0),(106, 'Sales Profit Report', 'dev@wepos.id', 'v.1.0.0', '', 'billing', 'reportSalesProfit', 0, '6. Reports>Sales (Profit)>Sales Profit Report', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Profit)>Sales Profit Report', 6131, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 09:46:57', 'administrator', '2019-03-07 10:21:51', 1, 0),(109, 'Sales Profit Report (Recap)', 'dev@wepos.id', 'v.1.0.0', '', 'billing', 'reportSalesProfitRecap', 0, '6. Reports>Sales (Profit)>Sales Profit Report (Recap)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Profit)>Sales Profit Report (Recap)', 6134, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 09:58:17', 'administrator', '2019-03-07 10:23:59', 1, 0),(110, 'Sales Profit By Menu', 'dev@wepos.id', 'v.1.0.0', 'Sales Profit By Menu', 'billing', 'reportSalesProfitByMenu', 0, '6. Reports>Sales (Profit)>Sales Profit By Menu', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Profit)>Sales Profit By Menu', 6135, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 09:53:21', 'administrator', '2019-03-07 12:38:07', 1, 0),(119, 'Bagi Hasil', 'dev@wepos.id', 'v.1.0.0', 'Bagi Hasil Detail', 'billing', 'reportSalesBagiHasil', 0, '6. Reports>Sales (Bagi Hasil)>Bagi Hasil', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Bagi Hasil)>Bagi Hasil', 6301, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-02-14 23:43:42', 'administrator', '2019-02-14 23:43:42', 1, 0),(120, 'Bagi Hasil (Recap)', 'dev@wepos.id', 'v.1.0.0', 'Bagi Hasil (Recap)', 'billing', 'reportSalesBagiHasilRecap', 0, '6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Sales (Bagi Hasil)>Bagi Hasil (Recap)', 6302, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-02-14 23:43:42', 'administrator', '2019-02-14 23:43:42', 1, 0),(125, 'Purchase Report', 'dev@wepos.id', 'v.1.0.0', 'Purchase Report', 'purchase', 'reportPurchase', 0, '6. Reports>Purchase/Pembelian>Purchase Report', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Purchase/Pembelian>Purchase Report', 6401, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-02-16 14:28:58', 'administrator', '2019-03-07 12:08:45', 1, 0),(127, 'Purchase Report (Recap)', 'dev@wepos.id', 'v.1.0.0', 'Purchase Report (Recap)', 'purchase', 'reportPurchaseRecap', 0, '6. Reports>Purchase/Pembelian>Purchase Report (Recap)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Purchase/Pembelian>Purchase Report (Recap)', 6403, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 06:23:40', 'administrator', '2019-03-07 12:08:25', 1, 0),(128, 'Last Purchase Price', 'dev@wepos.id', 'v.1.0.0', 'Last Purchase Price', 'purchase', 'reportLastPurchasePrice', 0, '6. Reports>Purchase/Pembelian>Last Purchase Price', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Purchase/Pembelian>Last Purchase Price', 6404, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 06:23:40', 'administrator', '2019-03-07 12:08:25', 1, 0),(129, 'Receiving Report', 'dev@wepos.id', 'v.1.0.0', 'Receiving Report', 'inventory', 'reportReceiving', 0, '6. Reports>Receiving (In)>Receiving Report', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Receiving (In)>Receiving Report', 6501, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 0, 'icon-grid', '', 'administrator', '2019-03-07 06:31:50', 'administrator', '2019-03-07 12:00:32', 1, 0),(132, 'Receiving Report (Recap)', 'dev@wepos.id', 'v.1.0.0', 'Receiving Report (Recap)', 'inventory', 'reportReceivingRecap', 0, '6. Reports>Receiving (In)>Receiving Report (Recap)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Receiving (In)>Receiving Report (Recap)', 6504, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 08:57:19', 'administrator', '2019-03-07 12:01:16', 1, 0),(145, 'Monitoring Stock (Actual)', 'dev@wepos.id', 'v.1.0.0', 'Monitoring Stock (Actual)', 'inventory', 'reportMonitoringStock', 0, '6. Reports>Warehouse>Monitoring Stock (Actual)', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Warehouse>Monitoring Stock (Actual)', 6642, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-02-11 16:44:12', 'administrator', '2019-03-07 17:45:36', 1, 0),(146, 'Kartu Stok', 'dev@wepos.id', 'v.1.0.0', 'Kartu Stok', 'inventory', 'kartuStok', 0, '6. Reports>Warehouse>Kartu Stock', 1, 'icon-grid', 'icon-grid', '', '', 1, 0, 1, 0, '6. Reports>Warehouse>Kartu Stock', 6643, 'icon-grid', '', 0, 'icon-grid', '', 1, 'icon-grid', '', 1, 'icon-grid', '', 'administrator', '2019-03-07 23:43:42', 'administrator', '2019-03-07 17:46:03', 1, 0),(169,'Pembayaran PPOB','dev@wepos.id','v.1.0.0','Pembayaran PPOB','cashier','ppob',0,'3. Cashier & Reservation>Pembayaran PPOB',1,'icon-grid','icon-grid','','',1,0,1,0,'3. Cashier & Reservation>Pembayaran PPOB',3401,'icon-grid','',0,'icon-grid','',1,'icon-grid','',1,'icon-grid','','administrator','2019-04-09 08:25:57','administrator','2019-04-09 17:49:57',1,0);");
					
					$this->db->delete($this->prefix.'options',"option_var LIKE 'mlog_%'");
				
					//copy module
					if (empty($get_opt['is_cloud'])) {
						
						$minjs_path = BASE_PATH.'/apps.min/modules'; 
						delete_files($minjs_path, TRUE);
						$zip = new ZipArchive;
						
						$apps_default = BASE_PATH.'/apps.min/core/modules.default';
						if($zip->open($apps_default) === TRUE) 
						{
							if (!is_dir($minjs_path)) {
								@mkdir($minjs_path, 0777, TRUE);
							}

							$zip->extractTo($minjs_path);
							$zip->close();
							
						}
				
						$appmod_path = APPPATH.'/modules'; 
						delete_files($appmod_path, TRUE);
						
						$zip = new ZipArchive;
						$file_default = APPPATH.'/core/modules.default';
						if($zip->open($file_default) === TRUE) 
						{
							if (!is_dir($appmod_path)) {
								@mkdir($appmod_path, 0777, TRUE);
							}

							$zip->extractTo($appmod_path);
							$zip->close();
							
						} 
						
					}
					
				}else{
					doresetapp();
				}
			}
			
		}
		
	}
} 