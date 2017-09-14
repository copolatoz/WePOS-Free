<?php
class Model_mastersupplier extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'supplier';
	}

} 