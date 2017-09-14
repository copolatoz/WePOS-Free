<?php
if( ! function_exists('set_params')){

	function set_params($params)
	{
		//sample params:
		//set_params(), set_params('*'), set_params('field1, field2'), set_params(array('fields' => '*', 'show_branch' => true))
		$fields = '*';
		$show_branch = true;
		
		if(is_array($params)){
			if(!empty($params['fields'])){
				$exp_param = explode(',',trim($params['fields']));
				if(count($exp_param) <= 1){
					$fields = $exp_param[0];
				}else{
					$fields = $exp_param;
				}
			}
		}else{
		
			if(!empty($params)){
				$exp_param = explode(',', trim($params));
				if(count($exp_param) <= 1){
					$fields = $exp_param[0];
				}else{
					$fields = $exp_param;
				}
			}
			
		}

		//fields trim
		/*if(is_array($fields)){
			$newfields = array();
			foreach($fields as $dt){
				$newfields[] = trim($dt);
			}
			$fields = $newfields;
		}*/
		

		//default		
		$search = array(
			'scope'		=> '',
			'table'		=> '',
			'query'		=> '',
			'use_SQL_CALC_FOUND_ROWS' => true,
			'fields'	=> '*',
			'start'		=> 0,
			'limit'		=> 20,
			'order'		=> '',
			'sort_alias'=> '',
			'where'		=> '',
			'like'		=> '',
			'or_like'	=> '',
			'like_group'=> '',
			'join'		=> '',
			'single'	=> false,
			'output'	=> 'array'
		);
				
		//PRIMARY KEY
		$use_field = '';		
		if(!empty($params['primary_key'])){
			$use_field = trim($params['primary_key']);
		}else{
			if(is_array($fields)){
				$use_field = trim($fields[0]);
			}else{
				$use_field = trim($fields);
			}
		}
		$search['primary_key'] = $use_field;
		
		$search['order'] = array($search['primary_key'] => 'DESC');
		
		//scope
		if(!empty($params['scope'])){
			$search['scope'] = $params['scope'];		
		}else{
			$CI =& get_instance();  
			$search['scope'] = $CI;
		}		
		
		//set table : string
		if(!empty($params['table'])){
			$search['table'] = $params['table'];		
		}
		
		//set join : string | array
		if(!empty($params['join'])){
			$search['join'] = $params['join'];
		}
		
		//set use_SQL_CALC_FOUND_ROWS
		if(!empty($params['use_SQL_CALC_FOUND_ROWS'])){
			$search['use_SQL_CALC_FOUND_ROWS'] = $use_SQL_CALC_FOUND_ROWS;			
		}else{
			$search['use_SQL_CALC_FOUND_ROWS'] = true;
		}
		
		//set field
		if(!empty($fields)){
			$search['fields'] = $fields;			
		}else{
			$search['fields'] = '*';
		}
		
		//log_message('info','SQL Query fields: ' .$search['fields']);
		
		//set query
		if(!empty($params['query'])){
			$search['query'] = $params['query'];
		}
		
		//set start
		if(!empty($params['start'])){
			$search['start'] = $params['start'];
		}else{
			if(!empty($_POST['start'])){
				$search['start'] = $_POST['start'];
			}
		}
		
		//set limit
		if(!empty($params['limit'])){
			$search['limit'] = $params['limit'];
		}else{
			if(!empty($_POST['limit'])){
				$search['limit'] = $_POST['limit'];
			}
		}
		
		//set order :  array('fieldname' => 'direction')
		if(!empty($params['order'])){
			$search['order'] = $params['order'];
		}else{
			if(!empty($_POST['order'])){
				$search['order'] = $_POST['order'];
			}
		}
		
		//SORT OVERRULED - EXTJS
		if(!empty($_POST['sort'])){
			
			$dtSort = json_decode($_POST['sort'],true);
			$search_order = array();
			if(!empty($dtSort)){
				foreach($dtSort as $kS => $dtS){
					if(!empty($dtSort[$kS]['property'])){
						if(!empty($params['sort_alias'])){
							foreach($params['sort_alias'] as $keyS => $valS){
								if($keyS == $dtSort[$kS]['property']){
									$dtSort[$kS]['property'] = $valS;
								}
							}
						}
					}
					
					if(empty($dtSort[$kS]['direction'])){
						$dtSort[$kS]['direction'] = 'ASC';
					}
					
					$search_order[$dtSort[$kS]['property']] = $dtSort[$kS]['direction'];
				}
			}
			
			if(!empty($search_order)){
				$search['order'] = $search_order;
			}
			
		}
						
		//set where
		if(!empty($params['where'])){
			$search['where'] = $params['where'];
		}
		
		//set like
		if(!empty($params['like'])){
			$search['like'] = $params['like'];
		}
		
		//set or_like
		if(!empty($params['or_like'])){
			$search['or_like'] = $params['or_like'];
		}
		
		//set or_like
		if(!empty($params['like_group'])){
			$search['like_group'] = $params['like_group'];
		}		
		
		//Branch - session
		//branch
		if(!empty($params['show_branch'])){
			$show_branch = $params['show_branch'];
		}
		if($show_branch){
			$search['client_id'] = $search['scope']->session->userdata('client_id');
		}
		
		//extend filter -------------------------------
  		//search type
		if(!empty($params['searchtype'])){
			$search['searchtype'] = $params['searchtype'];
		}else{
			if(!empty($_POST['searchtype'])){
				$search['searchtype'] = $_POST['searchtype'];
			}
		}		
		
		//queryfield
		if(!empty($params['queryfield'])){
			$search['queryfield'] = $params['queryfield'];
		}else{
			if(!empty($_POST['queryfield'])){
				$search['queryfield'] = $_POST['queryfield'];
			}
		}		
		
  		//set output
		$default_return = array('array','object','json');
		if(!empty($params['output'])){
			if(in_array($params['output'], $default_return)){
				$search['output'] = $params['output'];
			}
		}else{
			if(in_array($_POST['output'], $default_return)){
				$search['output'] = $_POST['output'];
			}
		}
		
		//is single output
		if(!empty($params['single'])){
			$search['single'] = $params['single'];
		}else{
			$search['single'] = false;
		}		
		
		//unset
		unset($search['scope']);
		
		return $search;
	}

}

if( ! function_exists('search_filter')){

	function search_filter($objCI)
	{
		if(empty($objCI)){
			$objCI =& get_instance();
		}		
		
		//query: string
		//searchtype: field | field_and | groupfield_and | groupfield | field_or | groupfield_or | where
		//queryfield: array | string
        extract($_POST);
        
        if(!empty($query) AND !empty($searchtype)){
        	if(!empty($queryfield)){
        		$get_queryfield = json_decode($queryfield, true);
        		if(!empty($get_queryfield)){
        			$queryfield = $get_queryfield;
        		}        
        		
        	}
        	switch($searchtype){        		
        		case 'field':
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname){
        					$objCI->db->like($fieldname, $query);
        				}        				
        			}else{
        				$objCI->db->like($queryfield, $query);
        			}
        			break;
        		case 'field_and':
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname){
        					$objCI->db->like($fieldname, $query);
        				}        				
        			}else{
        				$objCI->db->like($queryfield, $query);
        			}
        			break;
        		case 'groupfield_and':
        			$sql = '';
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname){
        					if(empty($sql)){
        						$sql = $fieldname." LIKE '%".$query."%'";
        					}else{
        						$sql .= " AND ".$fieldname." LIKE '%".$query."%'";
        					}	
        				}        				
        			}else{
        				$sql = $queryfield." LIKE '%".$query."%'";
        			}
        			
        			$objCI->db->where("(".$sql.")");
        			return $sql;
        			break;
        		case 'groupfield':
        			$sql = '';
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname){
        					if(empty($sql)){
        						$sql = $fieldname." LIKE '%".$query."%'";
        					}else{
        						$sql .= " AND ".$fieldname." LIKE '%".$query."%'";
        					}	
        				}        				
        			}else{
        				$sql = $queryfield." LIKE '%".$query."%'";
        			}
        			
        			$objCI->db->where("(".$sql.")");
        			return $sql;
        			break;
        		case 'field_or':
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname ){
        					$objCI->db->or_like($fieldname, $query);
        				}        				
        			}else{
        				$objCI->db->or_like($queryfield, $query);
        			}
        			break;
        		case 'groupfield_or':
        			$sql = '';
        			if(is_array($queryfield)){
        				foreach($queryfield as $fieldname){
        					if(empty($sql)){
        						$sql = $fieldname." LIKE '%".$query."%'";
        					}else{
        						$sql .= " OR ".$fieldname." LIKE '%".$query."%'";
        					}	
        				}        				
        			}else{
        				$sql = $queryfield." LIKE '%".$query."%'";
        			}
        			
        			$objCI->db->where("(".$sql.")");
        			return $sql;
        		case 'where':	
        			$objCI->db->where($query);
					return $query;
        			break;
        	}
        }
        

	}

}


if( ! function_exists('_free_result')){
	
	function _free_result($sendParams){
		
		//params: query, single, output
		$query = '';
		$single = false;
		$output = 'object';
		
		if(empty($sendParams) or empty($sendParams['query'])){
			return '';
		}else{
			$query = $sendParams['query'];
		}
		
		if($sendParams['single']){
			$single = $sendParams['single'];
		}
		
		if(!empty($sendParams['output'])){
			$output = trim(strtolower($sendParams['output']));
		}
		
		
		$data = array();
		
		if( ! empty( $query )){
			
			if($output == 'object'){
				if($single){					
					if($query->num_rows() > 0){						
						$data = $query->row();						
					}					
				}else
				{					
					if($query->num_rows() > 0){						
						$data = $query->result();						
					}					
				}
			}
			
			if($output == 'array'){
				if($single){					
					if($query->num_rows() > 0){						
						$data = $query->row_array();						
					}					
				}else
				{					
					if($query->num_rows() > 0){						
						$data = $query->result_array();						
					}					
				}
			}
			
			if($output == 'json'){
				if($single){					
					if($query->num_rows() > 0){						
						$data = $query->row();						
					}					
				}else
				{					
					if($query->num_rows() > 0){						
						$data = $query->result();						
					}					
				}
				
				$data = json_encode($data);
			}
		}
		
		return $data;
		
	}
	
}


