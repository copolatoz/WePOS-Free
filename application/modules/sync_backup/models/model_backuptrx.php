<?php
class Model_backuptrx extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients';
	}

} 