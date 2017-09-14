<?php
class Model_MasterSales extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'sales';
	}
	
	

} 