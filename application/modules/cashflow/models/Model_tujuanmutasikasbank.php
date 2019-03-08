<?php
class Model_tujuanmutasikasbank extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'tujuan_cashflow';
	}
	
	

} 