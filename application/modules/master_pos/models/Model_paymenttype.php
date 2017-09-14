<?php
class Model_PaymentType extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'payment_type';
	}
	
	

} 