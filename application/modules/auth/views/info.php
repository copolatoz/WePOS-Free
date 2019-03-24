<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="target-densitydpi=device-dpi, width=device-width, initial-scale=1.0, maximum-scale=1">
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="author" content="<?php echo $meta_author; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">

    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>assets/themes/frontend/images/favicon.ico" />
	<link href="<?php echo BASE_URL; ?>assets/themes/frontend/css/modern.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/themes/frontend/css/site-red.css" rel="stylesheet" type="text/css">
	
	<script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/jquery-1.9.0.min.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/buttonset.js"></script>
    <script type="text/javascript" src="<?php echo BASE_URL; ?>assets/themes/frontend/js/input-control.js"></script>
	
	<script>
		var appUrl 		= "<?php echo BASE_URL; ?>";
		var programName	= "<?php echo config_item('program_name'); ?>";
		var copyright	= "<?php echo config_item('copyright'); ?>";
	</script>
    <title><?php echo $title; ?></title>
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


</body>
</html>