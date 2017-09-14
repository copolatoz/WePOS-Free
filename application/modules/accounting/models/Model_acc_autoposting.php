<?php
class Model_acc_autoposting extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'autoposting';
	}

} 