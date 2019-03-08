<?php
class Model_closinginventory extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'closing';
	}

} 