<?php
class Model_distributionitem extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'distribution';
	}

} 