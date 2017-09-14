<?php
class Model_DiscountPlannerProduct extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'discount_product';
	}

} 