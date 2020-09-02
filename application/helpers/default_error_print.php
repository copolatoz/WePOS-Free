<?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); 

$objCI =& get_instance();

$order_apps = $objCI->input->get_post('order_apps', true);	
if(!empty($order_apps)){
	$r = array('success' => false, 'info' => $error, 'print' => array());
	echo json_encode($r); 
	//die();
}

if(empty($error)){
	//die();
}else{
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'apps.min/helper/reports/report.css'; ?>"/>	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url().'apps.min/helper/reports/report.css'; ?>" media="print"/>
	</head>
<body>
<div class="report_area" style="padding:0px; margin:0px auto; text-align:left; border:0px solid #ccc;">
<?php 
echo $error; 
$error = str_replace("<br/>","'\n+'",$error);
$error = str_replace("<br>","'\n+'",$error);
?>
</div>
<script type="text/javascript">
	alert('<?php echo $error;?>');
</script>
</body>
</html>
<?php
}
?>