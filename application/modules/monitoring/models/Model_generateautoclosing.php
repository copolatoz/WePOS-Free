<?php
class Model_generateautoclosing extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'closing';
	}

} 