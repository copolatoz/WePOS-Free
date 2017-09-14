<?php
class Model_receivinglist extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'receiving';
	}

} 