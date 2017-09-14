<?php
class Model_usagewaste extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'usagewaste';
	}

} 