<?php
class Model_DataClient extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'clients';
	}
	
	

} 