<?php
class Model_generateproductprice extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'product';
	}

} 