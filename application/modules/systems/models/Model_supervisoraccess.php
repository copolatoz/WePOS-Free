<?php
class Model_supervisoraccess extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'supervisor_access';
	}

} 