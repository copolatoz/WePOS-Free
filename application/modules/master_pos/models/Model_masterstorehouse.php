<?php
class Model_masterstorehouse extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'storehouse';
	}

} 