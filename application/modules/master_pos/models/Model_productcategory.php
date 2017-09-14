<?php
class Model_ProductCategory extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'product_category';
	}
	
	

} 