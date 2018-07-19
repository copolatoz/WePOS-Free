<?php
class Model_MasterRoom extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'room';
	}
	
	

} 