<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReservationTipe extends MY_Controller {
	
	public $table;
		
	function __construct()
	{
		parent::__construct();
		$this->prefix = config_item('db_prefix2');
		//$this->load->model('model_productvarian', 'm');
	}

	public function gridData()
	{
		
		//DROPDOWN & SEARCHING
		$show_all_text = $this->input->post('show_all_text');
		$show_choose_text = $this->input->post('show_choose_text');
		
  		$newData = array();
		
		if(!empty($show_all_text)){
			$dt = array('id' => '-1', 'name' => 'Semua', 'val' => '');
			array_push($newData, $dt);
		}else{
			if(!empty($show_choose_text)){
				$dt = array('id' => '', 'name' => 'Pilih', 'val' => '');
				array_push($newData, $dt);
			}
		}
		
		$dt = array('id' => 1, 'name' => 'Dine In', 'val' => 'dinein');
		array_push($newData, $dt);
		
		$dt = array('id' => 2, 'name' => 'Take Away', 'val' => 'takeaway');
		array_push($newData, $dt);
		
		$dt = array('id' => 3, 'name' => 'Delivery', 'val' => 'delivery');
		array_push($newData, $dt);
		
		$get_data = array();
		$get_data['data'] = $newData;
		$get_data['totalCount'] = count($newData);
		
      	die(json_encode($get_data));
	}
	
	
}