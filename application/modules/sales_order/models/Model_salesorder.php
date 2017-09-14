<?php
class Model_salesorder extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'salesorder';
		$this->table_detail = $this->prefix.'salesorder_detail';
		//$this->table_account_receivable = $this->prefix.'account_receivable';
		
	}
	
	
	

} 