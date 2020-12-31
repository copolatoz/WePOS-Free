<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<meta name="theme-color" content="#00afef" />
	<meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="author" content="<?php echo $meta_author; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">

	<link rel="manifest" href="<?php echo base_url(); ?>manifest.json">
	<link rel="shortcut icon" href="<?php echo base_url(); ?>apps.min/helper/login/icons-192.png" />
	<link rel="icon" type="image/png" href="<?php echo base_url(); ?>apps.min/helper/login/icons-192.png">
	<link rel="apple-touch-icon" sizes="192x192" href="<?php echo base_url(); ?>apps.min/helper/login/icons-192.png">
	
	<link href="<?php echo BASE_URL; ?>assets/themes/frontend/css/modern.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/themes/frontend/css/site-red.css" rel="stylesheet" type="text/css">
	
	<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/buttonset.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/input-control.js"></script>
	
	<script>
		var appUrl 		= "<?php echo BASE_URL; ?>";
		var programName	= "<?php echo config_item('program_name'); ?>";
		var copyright	= "<?php echo $copyright; ?>";
	</script>
</head>
<body class="modern-ui login_bg" >
	
<div class="page" id="page-login">
    <div class="page-region">
        <div class="page-region-content">
            
            <div class="grid" style="">
                <div class="row">
                    
					<?php 
					if(!empty($error)){
						echo '<div class="span4"><h3 class="icon-warning fg-color-red"> <b>Terjadi Kesalahan</b></h3>';
					}else{
						echo '<div class="span4"><h3 class="icon-info fg-color-darken"> <b>Info</b></h3>';
					}
					?>
                    </div>
                </div>               
            </div>
			<div class="grid">
                <div class="row">
                    <div class="span4">                        
						
						<p id="login-message" style="padding:6px 10px; color:ccc; font-weight:bold;">
							<?php 
							if(!empty($error)){
								echo $error;
							}else{
								echo 'Silahkan Cek Merchant Key Store/Cafe/Resto Anda di WePOS.id<br/>';
							}
							?>
							
							<br/>
							Klik <a href="https://wepos.id/login" target="_blank"><b>https://wepos.id/login</b></a>
						</p>
						<div class="clearfix"></div>
						<div class="footer_login">
							<?php echo config_item('copyright'); ?><br/>
						</div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>

<!-- Start of wepos Zendesk Widget script -->
<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=070b419f-4ff0-414d-9bee-29eb623a28b5"> </script>
<!-- End of wepos Zendesk Widget script -->

</body>
</html>