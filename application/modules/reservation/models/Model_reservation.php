<?php
class Model_reservation extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		
		$this->prefix = config_item('db_prefix2');
		$this->table = $this->prefix.'reservation';
		$this->table_detail = $this->prefix.'reservation_detail';
		//$this->table_account_receivable = $this->prefix.'account_receivable';
		
	}
	
	
	

} 