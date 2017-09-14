<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class lib_number{

	var $CI;

	function lib_number() 
	{
		$this->CI = get_instance();
	}
	
	function get_number($d, $c=false)
	{
		$id_branch	= $d['id_client'];
		$count		= $this->get_counter($d);
		$id_client	= str_repeat('0', (2-strlen($id_branch))).$id_branch;
		$counter	= str_repeat('0', (6-strlen($count))).$count;
		
		if($c){
			$number 	= $d['code'].$id_branch.$counter;
		}else{
			$number 	= $id_branch.$counter;
		}
		
		return $number;
	}
	
	function get_counter($d)
	{
		$this->CI->db->select('counter_count')
		->where('client_id', id_clean($d['client_id']))
		->order_by('id_counter', 'DESC')
		->where('counter_type', db_clean($d['code']))
		->limit(1);
		$query = $this->CI->db->get(config_item('db_prefix').'counter');
		log_message('INFO', $this->CI->db->last_query());
		if($query->num_rows() > 0){
			$row = _free_result($query, 'single');//$query->row();
			$data = $row->counter_count + 1;
		}else{
			$data = 1;
		}
		log_message('INFO', 'nilai data :'.$data);
		return $data;
	}
	
	function save_counter($d, $c=false)
	{	
		$data2 = array(
			'id_counter'		=>	null,
			'counter_year'		=>	date('Y'),
			'counter_month'		=>  date('m'),
			'counter_day'		=>  date('d'),
			'client_id'			=>	id_clean($d['client_id']),
			'counter_count'		=>	$this->get_counter($d),
			'counter_type'		=>	db_clean($d['code']),
			'created'			=>	date('Y-m-d H:i:s'),
			'createdby'			=>	id_clean($d['id_user'])
		);
		$this->CI->db->insert(config_item('db_prefix').'counter', $data2);
		log_message('INFO', 'save_counter($d, $c=false): ' . $this->CI->db->last_query());
		
		$id_branch	= $d['id_client'];
		$count		= $data2['counter_count'];//$this->get_counter($d);
		$id_branch	= str_repeat('0', (2-strlen($id_branch))).$id_branch;
		$counter	= str_repeat('0', (6-strlen($count))).$count;
		
		$this->delete_old_counter($d, $count);
		
		if($c){
			$number 	= $d['code'].$id_branch.$counter;
		}else{
			$number 	= $id_branch.$counter;
		}
		
		return $number;
	}
	
	function delete_old_counter($d, $counter)
	{
		$this->CI->db->where('counter_count < ', $counter);
		$this->CI->db->where('client_id', id_clean($d['client_id']));
		$this->CI->db->where('counter_type', db_clean($d['code']));
		$this->CI->db->delete(config_item('db_prefix').'counter');
		log_message('INFO', 'delete_old_counter($d, $counter): '.$this->CI->db->last_query());
	}
	
}