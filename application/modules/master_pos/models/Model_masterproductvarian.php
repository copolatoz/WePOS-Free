<?php
class Model_MasterProductVarian extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'product_varian';
	}

} 