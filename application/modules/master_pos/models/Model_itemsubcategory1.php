<?php
class Model_ItemSubCategory1 extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'item_subcategory1';
	}
	
	

} 