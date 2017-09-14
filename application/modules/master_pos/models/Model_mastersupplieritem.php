<?php
class Model_MasterSupplierItem extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'supplier_item';
	}

} 