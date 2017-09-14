<?php
class Model_warehouseaccess extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'storehouse_users';
	}

} 