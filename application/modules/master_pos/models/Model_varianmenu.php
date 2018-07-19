<?php
class Model_VarianMenu extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'varian';
	}
	
	

} 