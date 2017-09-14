<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ModuleManager extends MY_Controller {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('model_ModuleManager', 'm');
	}

	public function gridData()
	{
		$prefix = $this->prefix;
		$this->table = $this->prefix.'modules';
		
		//voa_65_text, voa_12_text, is_active_text
		$sortAlias = array(
			'is_active_text' 	=> 'is_active',
			'show_on_start_menu_text'	=> 'show_on_start_menu',
			'running_background_text'	=> 'running_background'
		);		
		
		// Default Parameter
		$params = array(
			'fields'		=> '*',
			'primary_key'	=> 'id',
			'table'			=> $this->table,
			'where'			=> array('is_deleted' => 0),
			'order'			=> array('id' => 'DESC'),
			'sort_alias'	=> $sortAlias,
			'single'		=> false,
			'output'		=> 'array' //array, object, json
		);
		$keywords = $this->input->post('keywords');

		if(!empty($keywords)){
			$params['where'][] = "(module_name LIKE '%".$keywords."%' OR module_folder LIKE '%".$keywords."%' OR module_controller LIKE '%".$keywords."%')";
		}
		
		//get data -> data, totalCount
		$get_data = $this->m->find_all($params);
		  		
  		$newData = array();		
		if(!empty($get_data['data'])){
			foreach ($get_data['data'] as $s){
				$s['is_active_text'] = ($s['is_active'] == '1') ? '<span style="color:green;">Active</span>':'<span style="color:red;">Inactive</span>';
				$s['show_on_start_menu_text'] = ($s['show_on_start_menu'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				$s['running_background_text'] = ($s['running_background'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				$s['show_on_right_start_menu_text'] = ($s['show_on_right_start_menu'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				
				$s['show_on_start_menu_text'] = ($s['show_on_start_menu'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				$s['show_on_shorcut_desktop_text'] = ($s['show_on_shorcut_desktop'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				$s['show_on_context_menu_text'] = ($s['show_on_context_menu'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				$s['show_on_preference_text'] = ($s['show_on_preference'] == '1') ? '<span style="color:green;">Yes</span>':'<span>No</span>';
				
				array_push($newData, $s);
			}
		}
			
		$get_data['data'] = $newData;
		
      	die(json_encode($get_data));
	}
	
	/*SERVICES*/
	public function save()
	{
		$this->table = $this->prefix.'modules';				
		$session_user = $this->session->userdata('user_username');
		
		//module info
		$module_name = $this->input->post('module_name');
		$module_author = $this->input->post('module_author');
		$module_version = $this->input->post('module_version');
		$module_description = $this->input->post('module_description');
		$is_active = $this->input->post('is_active');	
		
		if(empty($is_active)){
			$is_active = 0;
		}
		
		//module init		
		$module_folder = $this->input->post('module_folder');
		$module_controller = $this->input->post('module_controller');
		$module_icon = $this->input->post('module_icon');
		$module_shortcut_icon = $this->input->post('module_shortcut_icon');
		$module_glyph_font = $this->input->post('module_glyph_font');
		$module_glyph_icon = $this->input->post('module_glyph_icon');
		$running_background = $this->input->post('running_background');
		$show_on_right_start_menu = $this->input->post('show_on_right_start_menu');
		
		if(empty($running_background)){
			$running_background = 0;
		}
		if(empty($show_on_right_start_menu)){
			$show_on_right_start_menu = 0;
		}
		
		//menu setting - start menu
		$show_on_start_menu = $this->input->post('show_on_start_menu');	
		$start_menu_path = $this->input->post('start_menu_path');				
		$start_menu_order = $this->input->post('start_menu_order');				
		$start_menu_icon = $this->input->post('start_menu_icon');				
		$module_glyph_font = $this->input->post('module_glyph_font');					
		$start_menu_glyph = $this->input->post('start_menu_glyph');
				
		//$show_on_start_menu = 1;
		if(empty($show_on_start_menu)){
			$show_on_start_menu = 0;
		}		
				
		$module_order = 1;
		if(empty($start_menu_order)){
			$module_order = 0;
			$start_menu_order = 0;
		}
		
		$module_breadcrumb = '';
		if(!empty($start_menu_path)){
			$module_breadcrumb = $start_menu_path;
		}
		
		//menu setting - desktop shortcut
		$show_on_shorcut_desktop = $this->input->post('show_on_shorcut_desktop');	
		$desktop_shortcut_icon = $this->input->post('desktop_shortcut_icon');				
		$desktop_shortcut_glyph = $this->input->post('desktop_shortcut_glyph');	
		
		if(empty($show_on_shorcut_desktop)){
			$show_on_shorcut_desktop = 0;
		}
		
		//menu setting - context menu
		$show_on_context_menu = $this->input->post('show_on_context_menu');	
		$context_menu_icon = $this->input->post('context_menu_icon');				
		$context_menu_glyph = $this->input->post('context_menu_glyph');	
		
		if(empty($show_on_context_menu)){
			$show_on_context_menu = 0;
		}
		
		//menu setting - Preferences menu
		$show_on_preference = $this->input->post('show_on_preference');	
		$preference_icon = $this->input->post('preference_icon');				
		$preference_glyph = $this->input->post('preference_glyph');	
		
		if(empty($show_on_preference)){
			$show_on_preference = 0;
		}
		
		if(empty($module_name) OR empty($module_folder) OR empty($module_controller)){
			$r = array('success' => false);
			die(json_encode($r));
		}		
		
		if(!empty($show_on_start_menu) AND empty($start_menu_path)){
			$r = array('success' => false, 'info' => "Please set Module Path, example: \"Master Data>My Module\"");
			die(json_encode($r));
		}
		
			
		$r = '';
		if($this->input->post('form_type_ModuleManager', true) == 'add')
		{			
		
			//privilege
			$all_modules_method = array();						
			$module_method_create = $this->input->post('module_method_create');	
			$module_method_read = $this->input->post('module_method_read');	
			$module_method_update = $this->input->post('module_method_update');	
			$module_method_delete = $this->input->post('module_method_delete');	
			
			$module_method_1 = $this->input->post('module_method_1');	
			$module_method_2 = $this->input->post('module_method_2');	
			$module_method_3 = $this->input->post('module_method_3');	
			
			if(!empty($module_method_create)){
				$all_modules_method[] = array('method_function' => 'doCreate');
			}
			if(!empty($module_method_read)){
				$all_modules_method[] = array('method_function' => 'doRead');
			}
			if(!empty($module_method_update)){
				$all_modules_method[] = array('method_function' => 'doUpdate');
			}
			if(!empty($module_method_delete)){
				$all_modules_method[] = array('method_function' => 'doDelete');
			}
			
			if(!empty($module_method_1)){
				$all_modules_method[] = array('method_function' => $module_method_1);
			}
			
			if(!empty($module_method_2)){
				$all_modules_method[] = array('method_function' => $module_method_2);
			}
			
			if(!empty($module_method_3)){
				$all_modules_method[] = array('method_function' => $module_method_3);
			}
			
			//preloader
			$all_modules_preloader = array();	
			$module_preloader_folderpath_1 = $this->input->post('module_preloader_folderpath_1');	
			$module_preloader_folderpath_2 = $this->input->post('module_preloader_folderpath_2');	
			$module_preloader_folderpath_3 = $this->input->post('module_preloader_folderpath_3');	
			$module_preloader_filename_1 = $this->input->post('module_preloader_filename_1');	
			$module_preloader_filename_2 = $this->input->post('module_preloader_filename_2');
			$module_preloader_filename_3 = $this->input->post('module_preloader_filename_3');
			
			if(!empty($module_preloader_folderpath_1) OR !empty($module_preloader_filename_1)){
			
				if(!empty($module_preloader_folderpath_1) AND !empty($module_preloader_filename_1)){
					$all_modules_preloader[] = array(
						'preload_filename' => $module_preloader_filename_1,
						'preload_folderpath' => $module_preloader_folderpath_1
					);
				}else{
					$r = array('success' => false, 'info' => "Folder Path 1 OR File Name 1 cannot Empty!");
					die(json_encode($r));
				}
			}
			
			if(!empty($module_preloader_folderpath_2) OR !empty($module_preloader_filename_2)){
			
				if(!empty($module_preloader_folderpath_2) AND !empty($module_preloader_filename_2)){
					$all_modules_preloader[] = array(
						'preload_filename' => $module_preloader_filename_2,
						'preload_folderpath' => $module_preloader_folderpath_2
					);
				}else{
					$r = array('success' => false, 'info' => "Folder Path 2 OR File Name 2 cannot Empty!");
					die(json_encode($r));
				}
			}
			
			if(!empty($module_preloader_folderpath_3) OR !empty($module_preloader_filename_2)){
			
				if(!empty($module_preloader_folderpath_3) AND !empty($module_preloader_filename_3)){
					$all_modules_preloader[] = array(
						'preload_filename' => $module_preloader_filename_3,
						'preload_folderpath' => $module_preloader_folderpath_3
					);
				}else{
					$r = array('success' => false, 'info' => "Folder Path 3 OR File Name 3 cannot Empty!");
					die(json_encode($r));
				}
			}
			
			$var = array(
				'fields'	=>	array(
				
					//info
				    'module_name'  		=> 	$module_name,
				    'module_author'  	=> 	$module_author,
				    'module_version'  	=> 	$module_version,
				    'module_description'=> 	$module_description,
				    'module_description'=> 	$module_description,
					'is_active'			=>	$is_active,
					
					//init
					'module_folder'			=>	$module_folder,
					'module_controller'		=>	$module_controller,
					'module_icon'			=>	$module_icon,
					'module_shortcut_icon'	=>	$module_shortcut_icon,
					'module_glyph_font'		=>	$module_glyph_font,
					'module_glyph_icon'		=>	$module_glyph_icon,
					'running_background'		=>	$running_background,
					'show_on_right_start_menu'		=>	$show_on_right_start_menu,
					//init-old
					'show_on_start_menu'		=>	$show_on_start_menu,
					'module_breadcrumb'		=>	$module_breadcrumb,
					'module_order'			=>	$module_order,
					
					//menu setting - start menu
					'show_on_start_menu'		=>	$show_on_start_menu,
					'start_menu_path'			=>	$start_menu_path,
					'start_menu_order'			=>	$start_menu_order,
					'start_menu_icon'			=>	$start_menu_icon,
					'start_menu_glyph'			=>	$start_menu_glyph,
					
					'show_on_shorcut_desktop'	=>	$show_on_shorcut_desktop,
					'desktop_shortcut_icon'		=>	$desktop_shortcut_icon,
					'desktop_shortcut_glyph'	=>	$desktop_shortcut_glyph,
					
					'show_on_context_menu'		=>	$show_on_context_menu,
					'context_menu_icon'			=>	$context_menu_icon,
					'context_menu_glyph'		=>	$context_menu_glyph,
					
					'show_on_preference'		=>	$show_on_preference,
					'preference_icon'			=>	$preference_icon,
					'preference_glyph'			=>	$preference_glyph,
					
					'created'		=>	date('Y-m-d H:i:s'),
					'createdby'		=>	$session_user,
					'updated'		=>	date('Y-m-d H:i:s'),
					'updatedby'		=>	$session_user
				),
				'table'		=>  $this->table
			);	
			
			//SAVE
			$insert_id = false;
			$this->lib_trans->begin();
				$q = $this->m->add($var);
				$insert_id = $this->m->get_insert_id();
				
				//add method and preload file
				if(!empty($all_modules_method)){
					$this->m->add_modules_method($all_modules_method, $insert_id);
				}
				
				if(!empty($all_modules_preloader)){
					$this->m->add_modules_preload($all_modules_preloader, $insert_id);
				}
				
			$this->lib_trans->commit();			
			if($q)
			{  
				$r = array('success' => true, 'id' => $insert_id); 
			}  
			else
			{  
				$r = array('success' => false);
			}
      		
		}else
		if($this->input->post('form_type_ModuleManager', true) == 'edit'){
			$var = array('fields'	=>	array(
				    
					//info
				    'module_name'  		=> 	$module_name,
				    'module_author'  	=> 	$module_author,
				    'module_version'  	=> 	$module_version,
				    'module_description'=> 	$module_description,
				    'module_description'=> 	$module_description,
					'is_active'			=>	$is_active,
					
					//init
					'module_folder'			=>	$module_folder,
					'module_controller'		=>	$module_controller,
					'module_icon'			=>	$module_icon,
					'module_shortcut_icon'	=>	$module_shortcut_icon,
					'module_glyph_font'		=>	$module_glyph_font,
					'module_glyph_icon'		=>	$module_glyph_icon,
					'running_background'		=>	$running_background,
					'show_on_right_start_menu'		=>	$show_on_right_start_menu,
					//init-old
					'show_on_start_menu'		=>	$show_on_start_menu,
					'module_breadcrumb'		=>	$module_breadcrumb,
					'module_order'			=>	$module_order,
					
					//menu setting - start menu
					'show_on_start_menu'		=>	$show_on_start_menu,
					'start_menu_path'			=>	$start_menu_path,
					'start_menu_order'			=>	$start_menu_order,
					'start_menu_icon'			=>	$start_menu_icon,
					'start_menu_glyph'			=>	$start_menu_glyph,
					
					'show_on_shorcut_desktop'	=>	$show_on_shorcut_desktop,
					'desktop_shortcut_icon'		=>	$desktop_shortcut_icon,
					'desktop_shortcut_glyph'	=>	$desktop_shortcut_glyph,
					
					'show_on_context_menu'		=>	$show_on_context_menu,
					'context_menu_icon'			=>	$context_menu_icon,
					'context_menu_glyph'		=>	$context_menu_glyph,
					
					'show_on_preference'		=>	$show_on_preference,
					'preference_icon'			=>	$preference_icon,
					'preference_glyph'			=>	$preference_glyph,
					
					'updated'			=>	date('Y-m-d H:i:s'),
					'updatedby'			=>	$session_user
				),
				'table'			=>  $this->table,
				'primary_key'	=>  'id'
			);
			
			//UPDATE
			$id = $this->input->post('id', true);
			$this->lib_trans->begin();
				$update = $this->m->save($var, $id);
			$this->lib_trans->commit();
			
			if($update)
			{  
				$r = array('success' => true, 'id' => $id);
			}  
			else
			{  
				$r = array('success' => false);
			}
		}
		
		die(json_encode(($r==null or $r=='')? array('success'=>false) : $r));
	}
	
	public function delete()
	{
		$prefix = $this->prefix;
		$this->table = $prefix.'modules';
		
		$get_id = $this->input->post('id', true);		
		$id = json_decode($get_id, true);
		//old data id
		$sql_Id = $id;
		if(is_array($id)){
			$sql_Id = implode(',', $id);
		}
		
		//Delete
		$this->db->where("id IN (".$sql_Id.")");
		$q = $this->db->delete($this->table);
		
		$r = '';
		if($q)  
        {  
            $r = array('success' => true);			
			//modules_method
			$this->db->where("module_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."modules_method");	
			
			//modules_preload
			$this->db->where("module_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."modules_preload");
			
			//roles_module
			$this->db->where("module_id IN (".$sql_Id.")");
			$q = $this->db->delete($prefix."roles_module");
        }  
        else
        {  
            $r = array('success' => false, 'info' => 'Delete Module Failed!'); 
        }
		die(json_encode($r));
	}
	
}