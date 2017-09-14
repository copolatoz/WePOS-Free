<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class DB_Model extends CI_Model 
{
	
	public $prefix;
	public $table;	
	public $primary_key = 'ID';
	public $fields = array();
	
	private $__insert_id = null;
	private $__num_rows = null;
	private $__all_num_rows = null;
	
	public $last_query = '';
	public $_parent_name = '';

	/**
	 * Class Constructor
	 */
	function __construct()
	{
		parent::__construct();
		log_message('info', "My Model Class Initialized");
		
		$this->load->helper('db');
		$this->load->library('lib_trans');
		$this->prefix = config_item('db_prefix');
		$this->_parent_name = ucfirst(get_class($this));
		$this->_assign_libraries( (method_exists($this, '__get') OR method_exists($this, '__set')) ? FALSE : TRUE );
	}
	
	/**
	* Assign Libraries
	*
	* Creates local references to all currently instantiated objects
	* so that any syntax that can be legally used in a controller
	* can be used within models.  
	*
	* @access private
	*/    
	public function _assign_libraries($use_reference = TRUE)
	{
		$CI =& get_instance();                
		foreach (array_keys(get_object_vars($CI)) as $key)
		{
			if ( ! isset($this->$key) AND $key != $this->_parent_name)
			{            
				if ($use_reference == TRUE)
				{
				   $this->$key = '';
				   $this->$key =& $CI->$key;
				}
				else
				{
				   $this->$key = $CI->$key;
				}
			}
		}        
	}
	
	/**
	* Load the associated database table.
	*
	* @access public
	*/    

	public function load_table($table="", $config = 'default')
	{
	    log_message('info', "Loading model table: $table");
		$CI = &get_instance();
	    $CI->load->database($config);
	    $this->table	= $table;
		$this->fields	= $CI->db->list_fields($table);
	    log_message('info', "Successfull Loaded model table: $table");
	    
	}
	
	public function load_database($config='default')
	{
		log_message('info', "Loading model database");
	    $CI = &get_instance();
	    $CI->load->database($config);
		log_message('info', "Successfull Loaded model database");		
	}

	
	/**
	* Returns a resultset array with specified fields from database matching given conditions.
	*
	* @return query result either in array or in object based on model config
	* @access public
	*/    
	
	function find_all($params)
	{
		//INIT PARAMS
		$get_params = set_params($params);
		extract($get_params);
		/*
			'scope'		=> '',
			'table'		=> '',
			'query'		=> '',
			'fields'	=> '*',
			'start'		=> 0,
			'limit'		=> 20,
			'order'		=> '',
			'sort_alias'=> '',
			'where'		=> '',
			'like'		=> '',
			'or_like'	=> '',
			'like_group'	=> '',
			'join'		=> '',
			'single'	=> false,
			'output'	=> 'array'
		*/
		
		//SEARCH FILTER
		$ret_filter = search_filter($this);

		//PROCESSING
		$this->db->from($table);
		
		if (!empty($fields))
		{
			if(is_array($fields)){
				$fields_txt = implode(',', $fields);
			}else{
				$fields_txt = $fields;
			}
			
			$this->db->select($fields_txt, FALSE);
			//$this->db->select($fields_txt);
		}
		
		
		if (!empty($where))
		{
			if(is_array($where)){
				foreach($where as $dt_w => $dt_v){
					if(is_numeric($dt_w)){
						$this->db->where($dt_v);
					}else{
						$this->db->where($dt_w, $dt_v);
					}
				}
			}else{
				$this->db->where($where);	
			}
		}
		
		if (!empty($like))
		{
			if(is_array($like)){
				foreach($like as $dt_l => $dt_v){
					$this->db->like($dt_l, $dt_v);
				}
			}else{
				$this->db->like($like);	
			}
		}
		
		if (!empty($or_like))
		{
			if(is_array($or_like)){
				foreach($or_like as $dt_ol => $dt_v){
					$this->db->or_like($dt_ol, $dt_v);
				}
			}else{
				$this->db->or_like($or_like);	
			}
		}
		
		if(!empty($like_group))
		{
			if(is_array($like_group)){
				$new_like_group = '';
				foreach($like_group as $dt_ol => $dt_v){
					if(empty($new_like_group)){
						$new_like_group = $dt_ol." LIKE '".$dt_v."'";
					}else{
						$new_like_group .= " OR ".$dt_ol." LIKE '".$dt_v."'";
					}
				}
				
				if(!empty($new_like_group)){
					$this->db->where('('.$new_like_group.')');
				}
				
			}else{
				$this->db->where($like_group);	
			}
		}
		
		if(!empty($use_SQL_CALC_FOUND_ROWS)){
			$this->db->SQL_CALC_FOUND_ROWS(); //command if using SQLSERVER
		}
		
		if (!empty($order))
		{
			if(is_array($order)){
				foreach($order as $k=>$v){
					$this->db->order_by($k,$v);
				}
			}else{	
				$this->db->order_by($order);
			}
		}
		
		if (!empty($limit))
		{
			$this->db->limit($limit, $start);
		}
		
		//join = array('table' => '', 'cond' => '', 'tipe' => '') | array('table', 'cond', 'tipe')
		//join = array('single | many' => array( array('table' => '', 'cond' => '', 'tipe' => ''), array('table', 'cond', 'tipe')) )
		if (!empty($join))
		{
			//single | many
			if(is_array($join)){
				//check if single or many
				if(!empty($join[0])){
					
					if($join[0] == 'single' OR $join[0] == 'many'){
						if(!empty($join[1])){
							if(is_array($join[1])){
								$dtJoin = $join[1];
								foreach($dtJoin as $dt_j){
									if(!empty($dt_j['table']) AND !empty($dt_j['cond']) AND !empty($dt_j['tipe'])){
										$this->db->join($dt_j['table'], $dt_j['cond'], $dt_j['tipe']);
									}else{
										if(!empty($dt_j[0]) AND !empty($dt_j[1]) AND !empty($dt_j[2])){
											$this->db->join($dt_j[0], $dt_j[1], $dt_j[2]);
										}
									}
								}
							}
						}
					}else{
						//single 1
						if(!empty($join['table']) AND !empty($join['cond']) AND !empty($join['tipe'])){
							$this->db->join($join['table'], $join['cond'], $join['tipe']);
						}else{
							if(!empty($join[0]) AND !empty($join[1]) AND !empty($join[2])){
								$this->db->join($join[0], $join[1], $join[2]);
							}
						}
					}
					
				}
			}
			
		}
				
		$dt_query = $this->db->get();
		
		$this->last_query = $this->db->last_query();
		log_message('info','SQL Query: '. $this->last_query);
		
		$data_output = '';
		if(empty($output)){
			$output = 'array';
		}
		if(empty($single)){
			$single = false;
		}
		
		//OUTPUT
		$sendParams = array(
			'query'		=> $dt_query,
			'single'	=> $single,
			'output'	=> $output
		);
		$data_output = _free_result($sendParams);
			
		$this->__num_rows = $dt_query->num_rows();
		$this->__all_num_rows = $this->find_all_count();
			
		$data_return = array(
			'data' 			=> $data_output,
			'totalCount' 	=> $this->__all_num_rows
		);
		
		return $data_return;
	}

	function find_all_count(){
		$count = $this->db->query('SELECT FOUND_ROWS() as ROW_COUNT');
		
		//$count = $this->db->query('SELECT COUNT (*) OVER () as ROW_COUNT'); -- SQLSERVER
		$count = $count->row();
		
		// Write log
		log_message('info','SQL Query: ' . $this->db->last_query());
		
		return $count->ROW_COUNT;
	}

	
	/**
	 * Returns number of rows matching given SQL condition.
	 *	 
	 * @return integer the number of records returned by the condition
	 * @access public
	 */    
		
	/**
	 * Inserts a new record in the database.
	 *
	 * @access public
	 */    
	
	function add($_data = null)
	{
		if (empty($_data['fields']) AND empty($_data['table'])){
		    return FALSE;
		}
		
		$data = array();
		foreach($_data['fields'] as $k => $d)
		{
			$data[$k] = $d;
		}
		
		$ret =	$this->db->insert($_data['table'], $data);
	
		
		log_message('info','SQL Query: ' . $this->db->last_query());
	
		$this->__insert_id = $this->db->insert_id();
		return $ret;
	} 
	   
	/**
	 * Update model data to the database.
	 *
	 * @return boolean success
	 * @access public
	 */    

	public function update ($data,$id)
	{
		return $this->save($data,$id);
	}

	/**
	 * Saves model data to the database.
	 *
	 * @return boolean success
	 * @access public
	 */    

	function save($_data = null, $id = null)
	{
		if (empty($_data['fields']) AND empty($_data['table'])){
		    return FALSE;
		}
			
		if ($id !== null && $id !== false)
		{    
			$data = array();
			foreach($_data['fields'] as $k => $d){
				$data[$k] = $d;
			}
			$this->db->where($_data['primary_key'], $id);
		    $res = $this->db->update($_data['table'], $data);

		    
		    log_message('info','Update SQL Query: ' . $this->db->last_query());
		    
		    $this->__affected_rows = $this->db->affected_rows();
		    return $res;            
		}
		else
		{
		    return $this->add($_data);
		}
	}

	/**
	* Removes record for given id. If no id is given, the current id is used. Returns true on success.
	*
	* @return boolean True on success
	* @access public
	*/    
	function delete($_data, $id= null)
	{
		if($_data['real_delete']){
			return $this->remove($_data, $id);
		}else{
			return $this->invisible_remove($_data, $id);
		}
		
	}

	function remove($_data, $id = null)
	{
		if (is_array($id))
		{
			$this->db->where_in($_data['primary_key'], $id);
			$this->db->delete($_data['table']);
			
		   	log_message('info','SQL Query: ' . $this->db->last_query());
			return;
		}
				
		if ($id !== null && $id !== false)
		{    
			if ($this->db->delete($_data['table'], array($_data['primary_key'] => $id)))
			{
				
		   		log_message('info','SQL Query: ' . $this->db->last_query());
		   		return true;    
		   	}
		   	else
		   	{    
		   		return false;    
		   	}
		}
		else
		{
		   return false;     
		}
	}    

	function invisible_delete($_data, $id = null)
	{
		return $this->invisible_remove($_data, $id);
	}
	
	function invisible_remove($_data, $id = null)
	{
		if (is_array($id))
		{
			$this->db->where_in($_data['primary_key'], $id);
			$this->db->update($_data['table'], array('is_deleted' => 1));
			
		   	log_message('info','SQL DELETE Query: ' . $this->db->last_query());
			return true;
		}
		else if ($id !== null && $id !== false)
		{    
			$this->db->where($_data['primary_key'], $id);
			$this->db->update($_data['table'], array('is_deleted' => 1));
			log_message('info','SQL DELETE Query: ' . $this->db->last_query());
			return true;
		}
		else
		{
		   return false;     
		}
	}
	
	/**
	* Returns a resultset for given SQL statement. Generic SQL queries should be made with this method.
	*
	* @return array Resultset
	* @access public
	*/    
	
	function query($sql)
	{
		$ret = $this->db->query($sql);
		log_message('info','SQL Query: ' . $this->db->last_query());
		return $ret;
	}

	/**
	* Returns the last query that was run (the query string, not the result).
	*
	* @return string SQL statement
	* @access public
	*/    
	
	function last_query()
	{
		return $this->db->last_query();
	}

	
	/**
	* Returns the ID of the last record this Model inserted.
	*
	
	* @return int
	* @access public
	*/    
	
	function get_insert_id()
	{
		return $this->__insert_id;
	}

	/**
	* Returns the number of rows returned from the last query.
	*	
	* @return int
	* @access public
	*/    
	
	function get_count()
	{
		return $this->__num_rows;
	}
	
	function get_all_count()
	{
		return $this->__all_num_rows;
	}
		
	
	
}

class MY_Model extends CI_Model{}
// END  MY_Model class

/* End of file My_Model.php */
/* Location: /application/core/My_Model.php  */
