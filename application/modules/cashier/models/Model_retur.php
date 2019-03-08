<?php
class Model_retur extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'retur';
	}

} 