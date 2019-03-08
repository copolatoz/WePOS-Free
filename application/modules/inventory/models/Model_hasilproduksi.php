<?php
class Model_hasilproduksi extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'production';
	}

} 