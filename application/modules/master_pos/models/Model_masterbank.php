<?php
class Model_masterbank extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'bank';
	}
	
	

} 