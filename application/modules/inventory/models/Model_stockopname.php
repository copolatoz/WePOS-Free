<?php
class Model_stockopname extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'stock_opname';
	}

} 