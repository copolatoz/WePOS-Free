<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Error404 extends MX_Controller {
	
	function __construct(){
		parent::__construct();
	}

	public function index()
	{
		echo 'Page Not Found!';
	}
	
}