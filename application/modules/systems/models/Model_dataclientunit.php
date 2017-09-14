<?php
class Model_DataClientUnit extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients_unit';
	}
	
	

} 