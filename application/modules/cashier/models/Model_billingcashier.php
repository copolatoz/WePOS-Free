<?php
class Model_BillingCashier extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'billing';
	}
	
	

} 