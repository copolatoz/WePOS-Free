<?php
class Model_setupaplikasi extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'options';
	}

} 