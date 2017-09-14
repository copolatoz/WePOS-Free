<?php
class Model_VoucherList extends DB_Model {
	
	public $table;
	
	function __construct()
	{
		parent::__construct();	
		$this->table = $this->prefix.'discount_voucher';
	}
	
	

} 