<!DOCTYPE html>
<html>
<head>
	<?php
		$opt_var = array(
			'produk_nama',
			'hide_tanya_wepos'
		);
		$get_opt = get_option_value($opt_var);
		
		if(empty($get_opt['produk_nama'])){
			$get_opt['produk_nama'] = config_item('program_name');
		}
		
	?>
	<title><?php echo 'WePOS '.$get_opt['produk_nama']; ?> &mdash; <?php echo $this->session->userdata('client_name').' / '.$this->session->userdata('client_phone').' / '.$this->session->userdata('client_address'); ?></title> 
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">

    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/themes/frontend/images/favicon.ico" />
	<link rel="stylesheet" href="<?php echo base_url().'assets/desktop/css/loading.css'; ?>" />
	<script src="<?php echo base_url().'backend/config?v='.time(); ?>" type="text/javascript" charset="utf-8"></script>
	
</head>
<body>
	<?php $update_v = strtotime("26-08-2018 22:33:00"); ?>
	<div id="loading-mask"></div>
	<div id="loading">
		<img src="<?php echo BASE_URL; ?>apps.min/helper/login/loader.gif" width="120" height="20" alt="Loading..." style="margin-bottom:25px;"/>
		<div id="msg">Silahkan Tunggu: Persiapan Loading File...</div>
	</div>
	
	<div>	
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Silahkan Tunggu: Inisialisasi Aplikasi...';</script> 	
		<script src="<?php echo $apps_js.'?wup='.$update_v; ?>" type="text/javascript" charset="utf-8"></script>
		
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Silahkan Tunggu: Loading Layout...';</script>		
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/extjs.4.2/theme/css/ext-all-neptune.css" />	
		<link rel="stylesheet" href="<?php echo $apps_css; ?>" />
	
		<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/ext-modules.css" />	
		
		<script type="text/javascript">document.getElementById('msg').innerHTML = 'Memulai Aplikasi...';</script> 
	
	</div>
	
	
	<?php
	if(empty($get_opt['hide_tanya_wepos'])){
		?>
		<!--Start of Zendesk Chat Script-->
		<script type="text/javascript">
		window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
		d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
		_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
		$.src="https://v2.zopim.com/?3rmcPc13QzDajPqfCVOSZBlvA97Hixyj";z.t=+new Date;$.
		type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
		</script>
		<!--End of Zendesk Chat Script-->
		<?php
	}
	?>
	
</body>
</html>