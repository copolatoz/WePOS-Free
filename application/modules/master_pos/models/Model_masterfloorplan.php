<?php
class Model_MasterFloorplan extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'floorplan';
	}
	
	

} 