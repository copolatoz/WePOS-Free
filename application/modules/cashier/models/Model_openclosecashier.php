<?php
class Model_OpenCloseCashier extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'open_close_shift';
	}
	
	

} 