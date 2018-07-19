<?php
class Model_OooMenu extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'ooo_menu';
	}
	
	

} 