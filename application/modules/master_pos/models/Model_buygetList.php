<?php
class Model_BuygetList extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'discount_buyget';
	}
	
	

} 