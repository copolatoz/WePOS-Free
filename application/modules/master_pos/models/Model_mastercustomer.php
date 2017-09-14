<?php
class Model_mastercustomer extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'customer';
	}

} 