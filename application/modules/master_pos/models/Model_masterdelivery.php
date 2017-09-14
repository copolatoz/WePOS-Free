<?php
class Model_masterdelivery extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'delivery';
	}

} 