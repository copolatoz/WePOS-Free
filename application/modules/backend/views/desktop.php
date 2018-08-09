<!DOCTYPE html>
<html>
<head>
	<title><?php echo config_item('program_name'); ?> &mdash; <?php echo $this->session->userdata('client_name').' / '.$this->session->userdata('client_phone').' / '.$this->session->userdata('client_address'); ?></title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">

    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/themes/frontend/images/favicon.ico" />
	<link rel="stylesheet" href="<?php echo base_url().'assets/desktop/css/loading.css'; ?>" />
	<script src="<?php echo base_url().'backend/config?v='.time(); ?>" type="text/javascript" charset="utf-8"></script>
	
</head>
<body>
	
	<div id="loading-mask"></div>
	<div id="loading">
		<img src="<?php echo base_url(); ?>assets/desktop/images/loader.gif" width="86" height="86" alt="Loading..." style="margin-bottom:25px;"/>
		<div id="msg">Please wait: Preparing Load Files...</div>
	</div>
	
	<div>	
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Please wait: Initializing Application...';</script> 	
		<script src="<?php echo $apps_js; ?>" type="text/javascript" charset="utf-8"></script>		
		
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Please wait: Loading Theme...';</script>		
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/extjs.4.2/theme/css/ext-all-neptune.css" />	
		<link rel="stylesheet" href="<?php echo $apps_css; ?>" />
	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/ext-modules.css" />	
		
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Starting Application...';</script> 
	
	</div>
	
	
	
	<!--Start of Zendesk Chat Script-->
	<script type="text/javascript">
	window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
	d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
	_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
	$.src="https://v2.zopim.com/?3rmcPc13QzDajPqfCVOSZBlvA97Hixyj";z.t=+new Date;$.
	type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
	</script>
	<!--End of Zendesk Chat Script-->
</body>
</html>