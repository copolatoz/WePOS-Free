<?php
class Model_masterprinter extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'printer';
	}

} 