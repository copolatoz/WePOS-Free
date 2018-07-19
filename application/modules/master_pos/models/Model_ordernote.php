<?php
class Model_OrderNote extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'order_note';
	}
	
	

} 