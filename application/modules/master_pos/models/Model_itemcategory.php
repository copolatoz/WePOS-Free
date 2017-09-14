<?php
class Model_ItemCategory extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'item_category';
	}
	
	

} 