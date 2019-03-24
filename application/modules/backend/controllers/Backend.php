<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Backend extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('auth/mdl_config', 'mdl_config');
		$this->load->model('mdl_backend', 'mdl_backend');
	}

	public function index()
	{
		if($this->session->userdata('id_user') == '' && $this->session->userdata('client_id') == ''){ redirect('login'); }
		
		$use_gzip = USE_GZIP_MODE;
		$gzip_suffix_file = '';
		if($use_gzip == true){
			$gzip_suffix_file = '.php';
		}
		
		$comp_assets = true;
		if(ONE_COMP_SYS || ENVIRONMENT == 'production'){		
			$comp_assets = false;
			$apps_css = 'assets/desktop/css/alldesktop.min.css'.$gzip_suffix_file;
			$apps_js = 'apps.min/core/application.min.js'.$gzip_suffix_file;
			if(!file_exists(BASE_PATH.'assets/desktop/css/alldesktop.min.css'.$gzip_suffix_file) OR !file_exists(BASE_PATH.'apps.min/core/application.min.js'.$gzip_suffix_file)){
				$comp_assets = true;
			}
		}
		
		if($comp_assets){
			
			
			$this->load->library('Minifier');
			
			//MERGE STYLE - DESKTOP	
			//if(ENVIRONMENT != 'production'){
				@unlink(BASE_PATH.'assets/desktop/css/alldesktop.css');
				@unlink(BASE_PATH.'assets/desktop/css/alldesktop.min.css'.$gzip_suffix_file);
				@unlink(BASE_PATH.'apps.min/core/application.js');
				@unlink(BASE_PATH.'apps.min/core/application.min.js'.$gzip_suffix_file);
			//}
			
			$included_styles = array(        
				BASE_URL.'assets/desktop/css/desktop.css', 
				BASE_URL.'assets/desktop/css/icons.css', 
				BASE_URL.'assets/desktop/icons/awesome/font-awesome.css', 
				//BASE_URL.'assets/desktop/css/extapp.css' , 
				BASE_URL.'assets/desktop/css/modules.css'    
			);	
			
			
			//JS-APPS MERGE
			$included_js = array(        
				BASE_URL.'assets/js/extjs.4.2/ext-all.js', 
				BASE_URL.'apps/core/Constants.js', 
				BASE_URL.'apps/core/Module.js',
				BASE_URL.'apps/core/Widget.js',
				BASE_URL.'apps/core/LoadingBox.js',
				BASE_URL.'apps/core/MessageBox.js',
				BASE_URL.'apps/core/Application.js',
				BASE_URL.'apps/desktop/Desktop.js',
				BASE_URL.'apps/desktop/TaskBar.js',
				BASE_URL.'apps/desktop/TrayClock.js',
				BASE_URL.'apps/desktop/Wallpaper.js',
				BASE_URL.'apps/desktop/WallpaperModal.js',
				BASE_URL.'apps/desktop/StartMenu.js',
				BASE_URL.'apps/desktop/AboutModal.js',
				BASE_URL.'apps/desktop/model/ShortcutModel.js' ,
				BASE_URL.'apps/desktop/model/WallpaperModel.js',
				BASE_URL.'apps/Application.js',
				BASE_URL.'apps/startup/boot.js' 
			);		
			
			$vars = array( 
				'echo' => false,
				'encode' => false, 
				'gzip' => false, 
				'timer'	=> true
			);
			
			$this->minifier->initialize($vars);
			
			$merge_app_css = $this->minifier->merge( 'assets/desktop/css/alldesktop.css', 'assets/desktop/css', $included_styles );
			$merge_apps_js = $this->minifier->merge( 'apps.min/core/application.js', 'apps.min/core', $included_js );
			
			$vars2 = array( 
				'echo' => false,
				'encode' => false, 
				'gzip' => $gzip_suffix_file, 
				'timer'	=> true
			);
			$this->minifier->initialize($vars);
			
			//MINIFY STYLE - DESKTOP		
			$apps_css = $this->minifier->minify( $merge_app_css, 'assets/desktop/css/alldesktop.min.css', config_item('program_version') );
			
			
			//MINIFY-APPS
			$apps_js = $this->minifier->minify( $merge_apps_js, 'apps.min/core/application.min.js', config_item('program_version') );
		}
		
		$opt_var = array('hide_tanya_wepos');
		$get_opt = get_option_value($opt_var);
		
		$data_post = array(
			'get_opt'	=> $get_opt,
			'apps_css'	=> $apps_css,
			'apps_js'	=> $apps_js
		);
		
		$this->load->view('desktop', $data_post);
		
	}
		
	public function config(){
		
		header('Content-Type: application/javascript');
		
		//ENVIRONTMENT JS
		echo '
		var ExtApp = {
			version		: "'.config_item('program_version').'"	
		};
		ExtApp.BASE_PATH = "'.BASE_URL.'";	
		var serviceUrl      = "'.BASE_URL.'backend/services";
		var reportServiceUrl      = "'.BASE_URL.'backend/reportServices?";
		var appUrl      = "'.BASE_URL.'";
        var id_client	= '.$this->session->userdata('client_id').';
        var client_structure_id	= '.$this->session->userdata('client_structure_id').';
        var id_client_unit	= '.$this->session->userdata('client_unit_id').';
		var role_id		= '.$this->session->userdata('role_id').';
		var id_user		= '.$this->session->userdata('id_user').';
		var client_name	= "'.$this->session->userdata('client_name').'";
		var client_address	= "'.$this->session->userdata('client_address').'";
		var client_phone	= "'.$this->session->userdata('client_phone').'";
		var client_fax	= "'.$this->session->userdata('client_fax').'";
		var client_email	= "'.$this->session->userdata('client_email').'";
		var client_unit_name	= "'.$this->session->userdata('client_unit_name').'";
		var user_fullname	=  "'.$this->session->userdata('user_fullname').'";
        var programName = "'.config_item('program_name_short').'";
        var programVersion = "v'.config_item('program_version').'";
        var programRelease = "'.config_item('program_release').'";
        var client_name_app = "'.config_item('client_name').'";
        var copyright   = "'.config_item('copyright').'";
        var one_day_unix= '.ONE_DAY_UNIX.';
        var date_today  = "'.date('d/m/Y').'";	
		';
		
		$opt_var = array('merchant_tipe','merchant_key','produk_nama','produk_key','produk_expired','wepos_version',
		'include_tax','include_service',
		'default_tax_percentage','default_service_percentage',
		'takeaway_no_tax','takeaway_no_service','role_id_kasir',
		'auto_logout_time','show_multiple_print_qc', 'show_multiple_print_billing',
		'account_payable_non_accounting','account_receivable_non_accounting','cashflow_non_accounting',
		'spv_access_active','receiving_select_warehouse',
		'print_qc_then_order','supervisor_pin_mode','default_discount_payment','print_qc_order_when_payment',
		'use_item_sku','reservation_cashier','salesorder_cashier','autohold_create_billing',
		'hide_button_invoice','hide_button_halfpayment','hide_button_mergebill','hide_button_splitbill',
		'hide_button_logoutaplikasi','min_noncash','autobackup_on_settlement','no_hold_billing',
		'print_preview_billing','opsi_no_print_when_payment','must_choose_customer');
		
		$get_opt = get_option_value($opt_var);
		
		$update_var = array();
		if(!empty($get_opt)){
			
			//delete soon - update for v.3.42.17 to v.3.42.20
			if(empty($get_opt['merchant_tipe'])){
				$get_opt['merchant_tipe'] = 'cafe';
				$update_var['merchant_tipe'] = 'cafe';
			}
			if(empty($get_opt['produk_nama'])){
				$get_opt['produk_nama'] = '';
				$update_var['produk_nama'] = '';
			}
			if(empty($get_opt['merchant_key'])){
				$get_opt['merchant_key'] = '';
				$update_var['merchant_key'] = '';
			}
			if(empty($get_opt['produk_key'])){
				$get_opt['produk_key'] = '';
				$update_var['produk_key'] = '';
			}
			if(empty($get_opt['produk_expired'])){
				$get_opt['produk_expired'] = '';
				$update_var['produk_expired'] = '';
			}
			if(empty($get_opt['wepos_version'])){
				$get_opt['wepos_version'] = '';
				$update_var['wepos_version'] = '3.42.19';
			}
			
			foreach($get_opt as $key => $dt){
				
				if($key == 'auto_logout_time'){
					$dt = $dt*1000;
				}
				
				if($key == 'merchant_tipe'){
					$dt = strtoupper($dt);
				}
				
				if($key == 'spv_access_active'){
					$expl_dt = explode(",", trim($dt));
					
					$expl_dt_trim = array();
					foreach($expl_dt as $dtx){
						$expl_dt_trim[] = trim($dtx);
					}
					echo "var opt_".$key." = [\"".implode('","', $expl_dt_trim)."\"]; \n";
					
				}else{
					echo "var opt_".$key." = '".$dt."'; \n";
				}
				
			}
		}
		
		if(!empty($update_var)){
			update_option($update_var);
		}
		
		//AS CASHIER
		$asCashier = 0;
		if(!empty($this->session->userdata('role_id'))){
			if($this->session->userdata('role_id') == 1){
				$asCashier = 1;
			}
			
			if(!empty($get_opt['role_id_kasir'])){
				//if($this->session->userdata('role_id') == $get_opt['role_id_kasir']){
				//	$asCashier = 1;
				//}
				$role_id_kasir = explode(",", $get_opt['role_id_kasir']);
				if(!empty($role_id_kasir)){
					if(in_array($this->session->userdata('role_id'), $role_id_kasir)){
						$asCashier = 1;
					}
				}
				
			}
			
		}
		
		//REPORT PATH
		echo '
		ExtApp.asCashier = '.$asCashier.';
        var ReportViewerURL   =   "'.BASE_URL.'reports/core/index.php?stimulsoft_client_key=ViewerFx";
        var ReportDesignerURL =   "'.BASE_URL.'reports/core/index.php?stimulsoft_client_key=DesignerFx";
		';
		
		//MODULES-MENU INIT
		$dataModules		= $this->mdl_config->getMenuModules($this->session->userdata('role_id'));
        $shortcutModules	= $this->mdl_config->getShortcutModules($this->session->userdata('id_user'));
        $quickModules		= $this->mdl_config->getQuickModules($this->session->userdata('id_user'));
        $getBackgroundModules		= $this->mdl_config->getBackgroundModules($this->session->userdata('role_id'));
        $widgetModules		= $this->mdl_config->getWidgetModules($this->session->userdata('id_user'));
        $desktopConfig		= $this->mdl_config->desktopConfig($this->session->userdata('id_user'));
        $userData			= $this->mdl_config->userData($this->session->userdata('id_user'));
		
		//WIDGET
		$dataWidget = array();
		
		if(empty($userData->avatar)){
			$userData->avatar = "default.png";
		}
		
		$user = array(
			"userid"	=> $this->session->userdata('id_user'),
			"roleid"	=> $this->session->userdata('role_id'),
			"username"	=> $userData->user_username,
			"user_pin"	=> $userData->user_pin,
			"email"		=> $userData->user_email,
			"fullname"	=> $userData->user_fullname,
			"firstname"	=> $userData->user_firstname,
			"lastname"	=> $userData->user_lastname,
			"avatar"	=> $userData->avatar,
			"phone"		=> $userData->user_phone,
			"mobile"	=> $userData->user_mobile,
			"address"	=> $userData->user_address
		);
		
		$data = array(
			'modules' 	=> $dataModules,
			'shortcut' 	=> $shortcutModules,
			'quick'		=> $quickModules,
			'bgprocess'		=> $getBackgroundModules,
			'widget'	=> $widgetModules,
			'desktop'	=> $desktopConfig,
			'user'		=> $user
		);
		
      	$user_config = (object)$data;
      	
      	$all_menu = array();
      	$all_menu_parent = array();
		$all_menu_dt = array();
		
		$right_start_menu_app = array();
		$context_menu_app = array();
		
		//SET DEFAULT ROOT
		$all_menu['root'] = array();
		
		$no_generate = 1;
		if(!empty($user_config->modules)){
			foreach($user_config->modules as $dt_module){
			
				//RIGHT START MENU
				if($dt_module->show_on_right_start_menu == 1){
					$right_start_menu_icon = 'icon-grid';
					if(!empty($dt_module->module_icon)){
						$right_start_menu_icon = $dt_module->module_icon;
					}			
						
					if(!empty($dt_module->show_on_shorcut_desktop)){
						if(!empty($dt_module->start_menu_icon)){
							$right_start_menu_icon = $dt_module->start_menu_icon;
						}
					}
					
					$data_right_start_menu =  array(
						'module'	=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
						'iconCls'	=> $right_start_menu_icon,
						'text'		=> $dt_module->module_name,
						'name'		=> $dt_module->module_name
					);
					
					$right_start_menu_app[] = $data_right_start_menu;
					
				}
				
				//CONTEXT MENU
				if($dt_module->show_on_context_menu == 1){
					$context_menu_icon = 'icon-grid';
					if(!empty($dt_module->module_icon)){
						$context_menu_icon = $dt_module->module_icon;
					}			
						
					if(!empty($dt_module->show_on_shorcut_desktop)){
						if(!empty($dt_module->context_menu_icon)){
							$context_menu_icon = $dt_module->context_menu_icon;
						}
					}
					
					$data_context_menu =  array(
						'module'	=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
						'iconCls'	=> $context_menu_icon,
						'text'		=> $dt_module->module_name,
						'name'		=> $dt_module->module_name
					);
					
					$context_menu_app[] = $data_context_menu;
					
				}
								
				$menu_names	= explode('>',$dt_module->start_menu_path);
				$count	= count($menu_names);
				for($i=0; $i < $count; $i++){
				
					$menu_var = strtolower(url_title($menu_names[$i], '_'));
					$menu_parent = 'root';
					if($i > 0){
						$menu_parent = strtolower(url_title($menu_names[($i-1)], '_'));
					}
					
					//default
					if(empty($dt_module->module_icon)){
						$dt_module->module_icon = 'icon-grid';
					}
					
					$module_show = false;
					if(!empty($dt_module->show_on_start_menu)){
						if(!empty($dt_module->start_menu_icon)){
							$dt_module->module_icon = $dt_module->start_menu_icon;
						}
					}else{
						$module_show = true;
					}
										
					if(($count-1) == $i){
						
						
						//set last/child menu (last or first)
						if(empty($all_menu[$menu_parent])){
							
							if(!in_array($menu_parent, $all_menu_parent)){
								$all_menu[$menu_parent] = array();
								//echo 'create parent2: '.$menu_parent.'<br/>';
								$all_menu_parent[] = strtolower($menu_parent);
														
								//echo 'add '.$menu_var.' to parent: '.$menu_parent.'<br/>';						
								$all_menu[$menu_parent][] = array(
									'appClass'		=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
									'iconCls'		=> $dt_module->module_icon,
									'text'			=> $dt_module->module_name,
									'name'			=> $dt_module->module_name,
									'description'	=> $dt_module->module_name,
									'moduleMenu'	=> $dt_module->start_menu_path,
									'moduleID'		=> $dt_module->id_module,
									'module'		=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
									'leaf'			=> true,
									'active'		=> 1,
									'menu'			=> '',
									'level'			=> $i,
									'parent'		=> $menu_parent,
									'menuVar'		=> $menu_var,
									'hidden'		=> $module_show
								);
								$all_menu_dt[] = strtolower($menu_var);
							}
							
						}else{
							
							if(!in_array($menu_var, $all_menu_dt)){
								//echo 'add '.$menu_var.' to parent2: '.$menu_parent.'<br/>';
								
								$all_menu[$menu_parent][] = array(
									'appClass'		=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
									'iconCls'		=> $dt_module->module_icon,
									'text'			=> $dt_module->module_name,
									'name'			=> $dt_module->module_name,
									'description'	=> $dt_module->module_name,
									'moduleMenu'	=> $dt_module->start_menu_path,
									'moduleID'		=> $dt_module->id_module,
									'module'		=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
									'leaf'			=> true,
									'active'		=> 1,
									'menu'			=> '',
									'level'			=> $i,
									'parent'		=> $menu_parent,
									'menuVar'		=> $menu_var,
									'hidden'		=> $module_show
								);
								$all_menu_dt[] = strtolower($menu_var);
							}
							
						}
						
						
						
					}else{
						
						//set parent menu
						if(empty($all_menu[$menu_parent])){
							
							if(!in_array($menu_parent, $all_menu_parent)){
								//echo 'create parent: '.$menu_parent.'<br/>';
								$all_menu_parent[] = strtolower($menu_parent);
								
								$icon = 'icon-grid';
								if(!empty($dt_module->module_icon)){
									$icon = $dt_module->module_icon;
								}
								
								//echo 'add '.$menu_var.' to parent: '.$menu_parent.'<br/>';
								$all_menu[$menu_parent] = array();							
								$all_menu[$menu_parent][] = array(
									'appClass'		=> '',
									'iconCls'		=> $icon,
									'text'			=> $menu_names[$i],
									'name'			=> $menu_names[$i],
									'description'	=> $menu_names[$i],
									'moduleMenu'	=> $menu_names[$i],
									'moduleID'		=> 'main-'.$no_generate,
									'module'		=> '',
									'leaf'			=> false,
									'active'		=> 1,
									'menu'			=> '',
									'level'			=> $i,
									'parent'		=> $menu_parent,
									'menuVar'		=> $menu_var,
									'hidden'		=> $module_show
									
								);							
								$all_menu_dt[] = strtolower($menu_var);
							}
						
						}else{
							
							if(!in_array($menu_var, $all_menu_dt)){
								//echo 'add '.$menu_var.' to parent: '.$menu_parent.'<br/>';
								
								$icon = 'icon-grid';
								if(!empty($dt_module->module_icon)){
									$icon = $dt_module->module_icon;
								}
								
								$all_menu[$menu_parent][] = array(
									'appClass'		=> '',
									'iconCls'		=> $icon,
									'text'			=> $menu_names[$i],
									'name'			=> $menu_names[$i],
									'description'	=> $menu_names[$i],
									'moduleMenu'	=> $menu_names[$i],
									'moduleID'		=> 'main-'.$no_generate,
									'module'		=> '',
									'leaf'			=> false,
									'active'		=> 1,
									'menu'			=> '',
									'level'			=> $i,
									'parent'		=> $menu_parent,
									'menuVar'		=> $menu_var,
									'hidden'		=> $module_show
									
								);
								$all_menu_dt[] = strtolower($menu_var);
							}
						}
						
					}
					
					$no_generate++;
				}
				
				
				
			}
		}
		
		
		//echo 'parent';
		//print_r($all_menu_parent);
		//echo '<br>';
		//echo 'all menu';
		
		//Main module
		//RE-ORDER ROOT
		$dtParent = $all_menu_parent;
		unset($dtParent[0]);
		asort($dtParent);
		$new_dtParent = array();
		$no = 0;
		
		if(!empty($dtParent)){
			foreach($dtParent as $dtRoot){
				$new_dtParent[$dtRoot] = $no;
				$no++;
			}
		}
		
		$newRoot = array();
		$totalRoot = count($new_dtParent) - 1;
		if(!empty($all_menu['root'] )){
			foreach($all_menu['root'] as $drRoot){
				if(isset($new_dtParent[$drRoot['menuVar']])){
					$noID = $new_dtParent[$drRoot['menuVar']];
					$newRoot[$noID] = $drRoot;
				}else{				
					$totalRoot++;
					$newRoot[$totalRoot] = $drRoot;
				}
			}
		}
		ksort($newRoot);
		$all_menu['root'] = $newRoot;
				
		$main_app = $all_menu;
		
		//check user data
		$user_data = $user_config->user;
		
		//check user desktop_config
		$desktop_config = $user_config->desktop;
		
		//check shortcut application per-user
		$shortcuts_percolumn = 5;
		$no_shortcut = 0;
		$shortcut_app = array();
		if(!empty($user_config->shortcut)){
			foreach($user_config->shortcut as $dt_module){
				
				$no_shortcut++;
				
				if(empty($dt_module->module_shortcut_icon)){
					$dt_module->module_shortcut_icon = 'icon-grid';
				}			
					
				if(!empty($dt_module->show_on_shorcut_desktop)){
					if(!empty($dt_module->desktop_shortcut_icon)){
						$dt_module->module_shortcut_icon = $dt_module->desktop_shortcut_icon;
					}
				}
				
				$data_shortcut =  array(
					'module'	=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
					'iconCls'	=> $dt_module->module_shortcut_icon,
					'text'		=> $dt_module->module_name,
					'name'		=> $dt_module->module_name,
					'opencol'	=> 0,
					'closecol'	=> 0
				);
				
				if($no_shortcut == 1){
					$data_shortcut['opencol'] = 1;
				}
				
				if($no_shortcut == $shortcuts_percolumn AND $no_shortcut != count($user_config->shortcut)){
					$data_shortcut['closecol'] = 1;
					$no_shortcut = 0;
				}
				
				if($no_shortcut == count($user_config->shortcut)){
					$data_shortcut['closecol'] = 1;
				}
				
				$shortcut_app[] = $data_shortcut;
				
				
			}
		}
		//check quick application per-user
		$quick_app = array();
		if(!empty($user_config->quick)){
			foreach($user_config->quick as $dt_module){
			
				if(empty($dt_module->module_icon)){
					$dt_module->module_icon = 'icon-grid';
				}			
					
				if(!empty($dt_module->show_on_start_menu)){
					if(!empty($dt_module->start_menu_icon)){
						$dt_module->module_icon = $dt_module->start_menu_icon;
					}
				}
				
				$quick_app[] = array(
					'module'	=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
					'iconCls'	=> $dt_module->module_icon,
					'text'		=> $dt_module->module_name,
					'name'		=> $dt_module->module_name
				);
			}
		}
		//check bgprocess application per-user
		$bgprocess_app = array();
		if(!empty($user_config->bgprocess)){
			foreach($user_config->bgprocess as $dt_module){
			
				if(empty($dt_module->module_icon)){
					$dt_module->module_icon = 'icon-grid';
				}			
					
				if(!empty($dt_module->show_on_start_menu)){
					if(!empty($dt_module->start_menu_icon)){
						$dt_module->module_icon = $dt_module->start_menu_icon;
					}
				}
				
				$bgprocess_app[] = array(
					'module'	=> 'ExtApp.modules.'.$dt_module->module_folder.'.controller.'.$dt_module->module_controller,
					'iconCls'	=> $dt_module->module_icon,
					'text'		=> $dt_module->module_name,
					'name'		=> $dt_module->module_name
				);
			}
		}
		
		//check widget
		$widget_app = array();
		if(!empty($user_config->widget)){
			foreach($user_config->widget as $dt_widget){
				$widget_app[] = array(
					'widget'	=> $dt_widget->widget_controller,
					'name'		=> $dt_widget->widget_name
				);
			}
		}
		
		ksort($main_app);		
		
		$dt_treemenu = $this->app_treemenu($main_app, 'root', 0);
		$app_treemenu = "";
		if(!empty($dt_treemenu)){
			foreach($dt_treemenu as $dt){
				$app_treemenu .= "
				$dt,
				";
			}
		}
		
		/*echo '<pre>';
		print_r($main_app);
		echo '<br>';
		die();*/
		
		
		$getVar = $this->mdl_backend->getVar();
		if(!empty($getVar)){
			foreach($getVar as $dt_k => $dt_v){
				if(is_numeric($dt_v)){
					echo "var opt_".$dt_k." = ".$dt_v.";
					";
				}else{
					echo "var opt_".$dt_k." = \"".$dt_v."\";
					";
				}
			}
		}
		
		$main_app = '';
		echo "
		var CurrMe = [];
		var ConfModule = {
				user : ".json_encode($user_data).",
				config : ".json_encode($desktop_config).",
				applications : {
					mainApp: [
						".json_encode($main_app)."
					],
					treeMenu: [
						".$app_treemenu."
					],
					shortcutApp: ".json_encode($shortcut_app).",
					quickApp: ".json_encode($quick_app).",
					bgprocess: ".json_encode($bgprocess_app).",
					widget: ".json_encode($widget_app).",
					rightStartMenu: ".json_encode($right_start_menu_app).",
					contextMenu: ".json_encode($context_menu_app)."
				},
				success: true
			};
		";
	}
	
	function app_treemenu($data, $parent = 'root', $level = 0){
		$currLvl = $level;
		$level++;
		
		if(!empty($data[$parent])){
			$get_all_child = array();
			
			//ksort($data[$parent]);
			foreach($data[$parent] as $dt_child){
				
				$menuVar = $dt_child['menuVar'];
				
				$modId = $dt_child['appClass'];
				if($modId == ''){
					$modId = $dt_child['moduleID'];
				}
				
				$is_hidden = 'hidden: false,';
				if($dt_child['hidden'] == 0 OR empty($dt_child['hidden'])){
					$is_hidden = 'hidden: false,';
				}else{
					$is_hidden = 'hidden: true,';
				}
				
				$get_child = $this->app_treemenu($data, $menuVar, $level);
				
				if(!empty($get_child)){
					
					$dt_child['menu'] = array();
					$dt_child['menu']['items'] = $get_child;
					
					$get_child_txt = "";
					foreach($get_child as $dt){
						$get_child_txt .= "
							$dt,
						";
					}
					
					$dt_child_txt = "{
						id: '".$modId."',
						text: '".$dt_child['text']."',
						iconCls: '".$dt_child['iconCls']."',
						parent: '".$dt_child['parent']."',
						menuVar: '".$dt_child['menuVar']."',
						".$is_hidden."
						leaf: false,
						menu: {
							items: [
								".$get_child_txt."
							]
						},
						listeners: {
							click: function(){
								return false;
							}
						}
					}";
					
					$get_all_child[] = $dt_child_txt;
					
				}else{
					$dt_child['leaf'] = true;	
					$dt_child_txt = "{
						id: '".$modId."',
						text: '".$dt_child['text']."',
						iconCls: '".$dt_child['iconCls']."',
						parent: '".$dt_child['parent']."',
						menuVar: '".$dt_child['menuVar']."',
						".$is_hidden."
						leaf: true,
						listeners: {
							click: function(){								
								var thisMod = this;
								if(thisMod.id != ''){
									CurrMe.loadController(thisMod.id);
								}else{
									return false;
								}
							}
						}
					}";
					
					//check if launcher exist								
					//if(!Ext.Array.contains( CurrMe.regModules , thisMod.id)){
					//	Ext.Array.push(CurrMe.regModules, thisMod.id);
						
						//CurrMe.loadController(thisMod.id);
						
						//var newModule = Ext.create(thisMod.id);
						//newModule.app = CurrMe;
						//Ext.getCmp(thisMod.id).on('click', Ext.bind(CurrMe.createWindow, CurrMe, [newModule]), CurrMe);									
						//CurrMe.createWindow(newModule);
					//}
					
					$get_all_child[] = $dt_child_txt;	
					
				
				}
				
			}
			
			return $get_all_child;
			
		}else{
			//child
			return '';		
		}
	}
					
	public function services($f = '',$mo = ''){
		
		//module, file & action
		extract($_POST);
		
		if(!isset($module)){
			$module = 'backend';
		}
		
		if(!isset($file)){
			$file = $f;
		}
				
		if(!empty($mo)){
			$module = $mo;
		}
		
		
				
		//check services
		if(!empty($module) AND !empty($file)){
			
			
			if(!empty($action)){
				
				$all_post = $this->input->post(NULL, TRUE);
				
				//USING REST
				$this->rest_server($module.'/'.$file);
				$r = $this->rest->post($action, $all_post);
				
				if(empty($r)){
					$r = array('success' => false, 'info' => 'module: '.$module.'/'.$file.' -> '.$action.' not found!'); 
				}
				
				echo $r;
				
			}else{
				$r = array('success' => false, 'info' => "Service <b>".$file." -> ".$action."()</b> not found!"); 
				die(json_encode($r));
			}
					
		}else{	
			$r = array('success' => false, 'info' => 'Service not found or not set'); 
			die(json_encode($r));
		}
	}
	
	public function reportServices(){
		
		//module, file & action
		extract($_GET);
		
		if(!empty($do)){
			if($do == 'loading'){
				die('loading...');
			}
		}
	
		die();
	}
	
}
