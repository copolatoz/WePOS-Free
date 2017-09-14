<?php
class Model_DiscountPlanner extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'discount';
	}
	
	

} 