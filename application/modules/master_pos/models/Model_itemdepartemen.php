<?php
class Model_ItemDepartemen extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'item_departemen';
	}
	
	

} 