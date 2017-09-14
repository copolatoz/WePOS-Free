<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ModuleLoader extends MY_Controller {
	
	function __construct()
	{
		parent::__construct();
		
	}
	
	function index()
	{
		echo "ModuleLoader";
		die();
	}

	public function loadStoreModel()
	{
		$this->load->library('Minifier');
		
		$use_gzip = USE_GZIP_MODE;
		$gzip_suffix_file = '';
		if($use_gzip == true){
			$gzip_suffix_file = '.php';
		}
		
		$module_id = '';
		$data_store = '';
		$data_model = '';
		
		if(!empty($_POST)){
			extract($_POST);
		}
		
		if(!empty($module_id)){
			$module_id = json_decode($module_id, true);
			$module_info = explode(".",$module_id);
		}
		
		if(empty($module_info)){
			//0: module
			//1: folder
			//2: module file
			echo "Load Module Failed!";
			die();
		}
		
		if(!empty($data_store)){
			if($data_store == 'null'){
				$data_store = '';
			}
		}
		
		if(!empty($data_model)){
			if($data_model == 'null'){
				$data_model = '';
			}
		}
		
		if(empty($data_model) AND empty($data_store) AND empty($module_info)){
			echo "Load Module Failed!";
			die();
		}
		
		//echo '<pre>';
		//print_r($_POST);
		//die();

		$comp_module = true;
		if(ONE_COMP_CORE || ENVIRONMENT == 'production'){
			$comp_module = false;
			$get_JS = 'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js';
			$get_JS_min = 'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file;
			//if(!file_exists(BASE_PATH.$get_JS) OR !file_exists(BASE_PATH.$get_JS_min)){
			//	$comp_module = true;
			//}
		}
		
		if(ENVIRONMENT != 'production'){
			$comp_module = true;
		}
		
		if($comp_module){

			if(file_exists(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file)){
					
				/*if(ENVIRONMENT == 'production'){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file);
					//echo "var statL = 'file ".$module_info[1].$module_info[2].".min.js is exist';";
					//echo "alert('file ".$module_info[1].$module_info[2].".min.js is exist');";
					die();
				}else{*/
					@unlink(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js');
					@unlink(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file);
				//}
					
			}else
			if(file_exists(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js')){
			
				/*if(ENVIRONMENT == 'production'){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js');
					//echo "var statL = 'file ".$module_info[1].$module_info[2].".min.js is exist';";
					//echo "alert('file ".$module_info[1].$module_info[2].".min.js is exist');";
					die();
				}else{*/
					@unlink(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js');
				//}
					
			}
			
			
			$all_file_store_model = array();
			
			//collecting model
			$allModel = array();
			if(!empty($data_model)){
				$data_model = json_decode($data_model, true);
				//echo '<pre>';
				//print_r($data_model);
				//die();
				foreach($data_model as $dtM_name => $dtM_folder){
					if(!empty($dtM_folder)){
						foreach($dtM_folder as $dtM_file){
							//echo '<pre>';
							//print_r($dtM_file);
							if(!in_array($dtM_name.'_'.$dtM_file['model_name'], $allModel)){
								if(file_exists(BASE_PATH.'apps/modules/'.$dtM_name.'/model/'.$dtM_file['model_name'].'.js')){
									$allModel[] = $dtM_name.'_'.$dtM_file['model_name'];
									$all_file_store_model[] = base_url().'apps/modules/'.$dtM_name.'/model/'.$dtM_file['model_name'].'.js';
								}
							}
						}
					}
				}
			}
			
			//collecting store
			$allStore = array();
			if(!empty($data_store)){
				$data_store = json_decode($data_store, true);
				//echo '<pre>';
				//print_r($data_store);
				//die();
				foreach($data_store as $dtS_name => $dtS_folder){
					if(!empty($dtS_folder)){
						foreach($dtS_folder as $dtS_file){
							//echo '<pre>';
							//print_r($dtS_file);
							if(!in_array($dtS_name.'_'.$dtS_file['store_name'], $allStore)){
								if(file_exists(BASE_PATH.'apps/modules/'.$dtS_name.'/store/'.$dtS_file['store_name'].'.js')){
									$allStore[] = $dtS_name.'_'.$dtS_file['store_name'];
									$all_file_store_model[] = base_url().'apps/modules/'.$dtS_name.'/store/'.$dtS_file['store_name'].'.js';
								}
							}
						}
					}
				}
			}
			
			
			$vars = array( 
				'echo' => false,
				'encode' => false, 
				'gzip' => false, 
				'timer'	=> true
			);
			
			$this->minifier->initialize($vars);
			
			$merge_JS = '';
			if(!empty($module_info)){
				
				
				//VIEW				
				if(file_exists(BASE_PATH.'apps/modules/'.$module_info[1].'/view/'.$module_info[2].'.js')){
					$all_file_store_model[] = base_url().'apps/modules/'.$module_info[1].'/view/'.$module_info[2].'.js';
				}
				
				//STORE WITH MODEL
				if(!empty($all_file_store_model)){
					$merge_JS = $this->minifier->merge( 'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js', 'apps/modules/'.$module_info[1], $all_file_store_model);
				}else{
					//no view and store
					die();
				}
			
			}
			
			if(!empty($merge_JS)){
				
				//gzip
				$vars = array(
						'echo'	=> false,
						'encode' => false,
						'gzip' => $use_gzip,
						'timer' => true
				);
				$this->minifier->initialize($vars);
				$minifiy_module = $this->minifier->minify( $merge_JS, 'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js', config_item('program_version') );
			
				@unlink(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.js');
				
				if(!empty($minifiy_module)){
					//LOAD FILE
					if(file_exists(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file)){
						header('Content-Type: application/javascript');
						include(BASE_PATH.'apps.min/modules/'.$module_info[1].'.'.$module_info[2].'.min.js'.$gzip_suffix_file);
						die();
					}
				}
			
			}
			
		}else{
			
			//if(ONE_COMP_CORE || ENVIRONMENT == 'production'){

				//USE FILE - MIN
				if(file_exists(BASE_PATH.$get_JS_min)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS_min);
					die();
				}
				
			/*}else{
				
				//USE FILE
				if(file_exists(BASE_PATH.$get_JS)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS);
					die();
				}
				
			}*/
			
		}
		

		echo "Load Module Failed!";
		die();
		
	}

	public function loadHelper()
	{
		$this->load->library('Minifier');
		
		$use_gzip = USE_GZIP_MODE;
		$gzip_suffix_file = '';
		if($use_gzip == true){
			$gzip_suffix_file = '.php';
		}
		
		$module_id = ''; //module name
		$data_helper = '';
		
		if(!empty($_POST)){
			extract($_POST);
		}
		
		if(empty($module_id)){
			echo "Load Helper Failed!";
			die();
		}
				
		if(!empty($data_helper)){
			if($data_helper == 'null'){
				$data_helper = '';
			}
		}
		
		if(empty($data_helper)){
			echo "Load Helper Failed!";
			die();
		}		


		$comp_module = true;
		if(ONE_COMP_CORE || ENVIRONMENT == 'production'){
			$comp_module = false;
			$get_JS = 'apps.min/helper/'.$module_id.'_helper.js';
			$get_JS_min = 'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file;
			//if(!file_exists(BASE_PATH.$get_JS) OR !file_exists(BASE_PATH.$get_JS_min)){
			//	$comp_module = true;
			//}
		}
		
		if(ENVIRONMENT != 'production'){
			$comp_module = true;
		}
		
		if($comp_module){

			if(file_exists(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file)){					
				/*if(ENVIRONMENT == 'production'){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file);
					//echo "var statL = 'file ".$module_id."_helper.min.js is exist';";
					//echo "alert('file ".$module_id."_helper.min.js is exist');";
					die();
				}else{*/
					@unlink(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.js');
					@unlink(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file);
				//}
			}
			
			//collecting store
			$allHelper = array();
			$all_file_helper = array();
			if(!empty($data_helper)){
				$data_helper = json_decode($data_helper, true);
				//echo '<pre>';
				//print_r($data_helper);
				//die();
				foreach($data_helper as $helper_name => $helper_dt){
					//echo '<pre>';
					//print_r($helper_dt);
			
					$helper_exp = explode(".",$helper_dt);
					//ExtApp.helper.helperFile
			
					if(!empty($helper_exp[2])){
						if(!in_array($helper_exp[2], $allHelper)){
							if(file_exists(BASE_PATH.'apps/helper/'.$helper_exp[2].'.js')){
								$allHelper[] = $helper_exp[2];
								$all_file_helper[] = base_url().'apps/helper/'.$helper_exp[2].'.js';
							}
						}
					}
				}
			}
			
			$vars = array(
					'echo'	=> false,
					'encode' => false,
					'gzip' => false,
					'timer' => true
			);
			
			$this->minifier->initialize($vars);
			
			$merge_JS = '';
			if(!empty($all_file_helper)){
				$merge_JS = $this->minifier->merge( 'apps.min/helper/'.$module_id.'_helper.js', 'apps/helper/', $all_file_helper );
			}
			
			
			if(!empty($merge_JS)){
			
				
				//gzip
				$vars = array(
						'echo'	=> false,
						'encode' => false,
						'gzip' => $use_gzip,
						'timer' => true
				);
				$this->minifier->initialize($vars);
				$minifiy_helper = $this->minifier->minify( $merge_JS, 'apps.min/helper/'.$module_id.'_helper.min.js', config_item('program_version') );
			
				@unlink(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.js');
				
				if(file_exists(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/helper/'.$module_id.'_helper.min.js'.$gzip_suffix_file);
					die();
				}
					
			}
			
		}else{
			
			//if(ONE_COMP_CORE || ENVIRONMENT == 'production'){

				//USE FILE - MIN
				if(file_exists(BASE_PATH.$get_JS_min)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS_min);
					die();
				}
				
			/*}else{
				
				//USE FILE
				if(file_exists(BASE_PATH.$get_JS)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS);
					die();
				}
				
			}*/
			
		}		
		
		echo "Load Helper Failed!";
		die();
		
	}

	public function loadController()
	{
		$this->load->library('Minifier');
		
		$use_gzip = USE_GZIP_MODE;
		$gzip_suffix_file = '';
		if($use_gzip == true){
			$gzip_suffix_file = '.php';
		}
		
		$module_id = ''; //module name
		$data_helper = '';
		
		if(!empty($_POST)){
			extract($_POST);
		}
		
		if(!empty($module_id)){
			$module_id = json_decode($module_id, true);
			$module_info = explode(".",$module_id);
		}
		
		if(empty($module_info)){
			//0: ExtApp
			//1: modules
			//2: module_folder
			//3: controller
			//4: module_file
			echo "Load Controller Failed!";
			die();
		}
		
		$controller_file = $module_info[2].'.controller.'.$module_info[4].'.js';
		$controller_file_min = $module_info[2].'.controller.'.$module_info[4].'.min.js';

		$comp_module = true;
		if(ONE_COMP_CORE || ENVIRONMENT == 'production'){
			$comp_module = false;
			$get_JS = 'apps.min/modules/'.$controller_file;
			$get_JS_min = 'apps.min/modules/'.$controller_file_min.$gzip_suffix_file;
			//if(!file_exists(BASE_PATH.$get_JS) OR !file_exists(BASE_PATH.$get_JS_min)){
			//	$comp_module = true;
			//}
		}
		
		if(ENVIRONMENT != 'production'){
			$comp_module = true;
		}
		
		if($comp_module){

			if(file_exists(BASE_PATH.'apps.min/modules/'.$controller_file_min)){					
				//if(ENVIRONMENT == 'production'){
				//	header('Content-Type: application/javascript');
				//	include(BASE_PATH.'apps.min/modules/'.$controller_file_min.$gzip_suffix_file);
				//	die();
				//}else{
					@unlink(BASE_PATH.'apps.min/modules/'.$controller_file);
					@unlink(BASE_PATH.'apps.min/modules/'.$controller_file_min.$gzip_suffix_file);
				//}
			}
			
			$all_file_controller = array();
			if(file_exists(BASE_PATH.'apps/modules/'.$module_info[2].'/controller/'.$module_info[4].'.js')){
				$all_file_controller[] = base_url().'apps/modules/'.$module_info[2].'/controller/'.$module_info[4].'.js';
			}
			
			$vars = array(
					'echo'	=> false,
					'encode' => false,
					'gzip' => false,
					'timer' => true
			);
			$this->minifier->initialize($vars);
			
			$merge_JS = '';
			if(!empty($all_file_controller)){
				$merge_JS = $this->minifier->merge( 'apps.min/modules/'.$controller_file, 'apps/modules/', $all_file_controller );
			}

			if(!empty($merge_JS)){
			
				//gzip
				$vars = array(
						'echo'	=> false,
						'encode' => false,
						'gzip' => $use_gzip,
						'timer' => true
				);
				$this->minifier->initialize($vars);
				$minifiy_helper = $this->minifier->minify( $merge_JS, 'apps.min/modules/'.$controller_file_min, config_item('program_version') );
			
				@unlink(BASE_PATH.'apps.min/modules/'.$controller_file);
				
				if(file_exists(BASE_PATH.'apps.min/modules/'.$controller_file_min.$gzip_suffix_file)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/modules/'.$controller_file_min.$gzip_suffix_file);
					die();
				}
					
			}
			
		}else{
			
			//if(ONE_COMP_CORE || ENVIRONMENT == 'production'){

				//USE FILE - MIN
				if(file_exists(BASE_PATH.$get_JS_min)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS_min);
					die();
				}
				
			/*}else{
				
				//USE FILE
				if(file_exists(BASE_PATH.$get_JS)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS);
					die();
				}
				
			}*/
			
		}
		
		echo "Load Controller Failed!";
		die();
		
	}

	public function loadWidget()
	{
		$minify = $this->load->library('Minifier');
		
		$use_gzip = USE_GZIP_MODE;
		$gzip_suffix_file = '';
		if($use_gzip == true){
			$gzip_suffix_file = '.php';
		}
		
		$widget = ''; //module name
		$data_helper = '';
		
		if(!empty($_POST)){
			extract($_POST);
		}
		
		if(empty($widget)){
			//echo "Load Widget Failed!";
			die();
		}else{
			$widget = json_decode($widget, true);
		}
		
		//always generate and delete on-the-fly			
		$id_user = $this->session->userdata('id_user');
		$timer = time();
		$widget_file = 'widget-'.$id_user.'-'.$timer.'.js';
		$widget_file_min = 'widget-'.$id_user.'-'.$timer.'.min.js';

		$comp_module = true;
		if(ONE_COMP_CORE || ENVIRONMENT == 'production'){
			$comp_module = false;
			$get_JS = 'apps.min/widgets/'.$widget_file;
			$get_JS_min = 'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file;
			//if(!file_exists(BASE_PATH.$get_JS) OR !file_exists(BASE_PATH.$get_JS_min)){
			//	$comp_module = true;
			//}
		}
		
		if(ENVIRONMENT != 'production'){
			$comp_module = true;
		}
		
		if($comp_module){

			if(file_exists(BASE_PATH.'apps.min/widgets/'.$widget_file_min)){					
				if(ENVIRONMENT == 'production'){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file);
					die();
				}else{
					@unlink(BASE_PATH.'apps.min/widgets/'.$widget_file);
					@unlink(BASE_PATH.'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file);
				}
			}
			
			$all_file_widget = array();
			$all_file_widget_exists = array();
			if(!empty($widget)){
				foreach($widget as $dt){
					if(file_exists(BASE_PATH.'apps/widgets/'.$dt.'.js')){
						$all_file_widget[] = base_url().'apps/widgets/'.$dt.'.js';
						$all_file_widget_exists[] = $dt;
					}
				}
			}
			
			$vars = array(
					'echo'	=> false,
					'encode' => false,
					'gzip' => false,
					'timer' => true
			);
			$this->minifier->initialize($vars);
			
			$merge_JS = '';
			if(!empty($all_file_widget)){
				$merge_JS = $this->minifier->merge( 'apps.min/widgets/'.$widget_file, 'apps/widgets/', $all_file_widget );
			}
			
			
			if(!empty($merge_JS)){
			
				@unlink(BASE_PATH.'apps.min/widgets/'.$widget_file);
				
				//gzip
				$vars = array(
						'echo'	=> false,
						'encode' => false,
						'gzip' => $use_gzip,
						'timer' => true
				);
				$this->minifier->initialize($vars);
				$minifiy_helper = $this->minifier->minify( $merge_JS, 'apps.min/widgets/'.$widget_file_min, config_item('program_version') );
			
				if(file_exists(BASE_PATH.'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file);
					
					//make variable exists
					echo 'var widget_exists = [];';
					
					$no = 1;
					if(!empty($all_file_widget_exists)){
						foreach($all_file_widget_exists as $dt){
							echo 'widget_exists['.$no.'] = "'.$dt.'";';
							$no ++;
						}
					}
					
					//delete temp file
					@unlink(BASE_PATH.'apps.min/widgets/'.$widget_file);
					@unlink(BASE_PATH.'apps.min/widgets/'.$widget_file_min.$gzip_suffix_file);
					
					die();
				}
					
			}
			
		}else{
			
			//if(ONE_COMP_CORE || ENVIRONMENT == 'production'){

				//USE FILE - MIN
				if(file_exists(BASE_PATH.$get_JS_min)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS_min);
					die();
				}
				
			/*}else{
				
				//USE FILE
				if(file_exists(BASE_PATH.$get_JS)){
					header('Content-Type: application/javascript');
					include(BASE_PATH.$get_JS);
					die();
				}
				
			}*/
			
		}
		
		//echo "Load Widget Failed!";
		die();
		
	}
	
}
