<?php
class Model_masterdivisi extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'divisi';
	}

} 