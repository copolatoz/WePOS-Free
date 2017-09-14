<?php
class Model_stockopnamedetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'stock_opname_detail';
	}

} 