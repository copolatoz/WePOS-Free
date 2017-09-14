<?php
class Model_BillingDetail extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'billing_detail';
	}
	
	

} 