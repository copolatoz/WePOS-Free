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
	<script type="text/javascript">
	
		$(document).ready(function(e)
		{
			
			
			$('#store_data').change(function(){
				
				// Target url
				var target = appUrl + "login";
				if (!target || target == '')
				{
					// Page url without hash
					target = document.location.href.match(/^([^#]+)/)[1];
				}
				
				// Request
				var data = {
					view_multiple_store: $('#view_multiple_store').val(),
					store_data: $('#store_data').val(),
					type_login: 'store'
				};
				
				
				$('#login-message').attr('class','');
				$('#login-message').addClass('fg-color-white');	
				
				
				// Send
				$.ajax({
					url: target,
					dataType: 'json',
					type: 'POST',
					data: data,
					success: function(data, textStatus, XMLHttpRequest)
					{
						//alert(data.errors.reason);
						if (data.success)
						{
							$('#login-message').fadeOut();
							$('#login-message').html(' DB Connected..');
							$('#login-message').removeClass('bg-color-blue');
							$('#login-message').addClass('bg-color-green');
							$('#login-message').addClass('icon-checkmark');
							$('#login-message').fadeIn();
						}
						else
						{
							// Message
							//$('#login-message').html(data.errors.reason);
							$('#login-message').html(' Connect DB Failed!');
							$('#login-message').removeClass('bg-color-blue');
							$('#login-message').addClass('bg-color-red');
							$('#login-message').addClass('icon-warning');
							$('#login-message').fadeIn();
							$('#loadBox').hide();
							$('#loginBox').fadeIn();
							
						}
					},
					error: function(XMLHttpRequest, textStatus, errorThrown)
					{
						// Message
						//$('#login-message').html(data.errors.reason);
						$('#login-message').html(' Connect DB Failed!');
						$('#login-message').removeClass('bg-color-blue');
						$('#login-message').addClass('bg-color-red');
						$('#login-message').addClass('icon-warning');	
						$('#login-message').fadeIn();
						$('#loadBox').hide();
						$('#loginBox').fadeIn();
					}
				});
					
			});
			
			$('#button_option_menu').click(function(event){
				event.preventDefault();
				document.location.href = appUrl;
			});
			
			$('#login-form').submit(function(){
				$('#button_submit').trigger('click');
				return false;
			});
			
			$('.helper.loginUsername').click(function(){
				$('#loginUsername').val('');
				return false;
			});
			
			$('.helper.loginPassword').click(function(){
				$('#loginPassword').val('');
				return false;
			});
			
			$('#button_submit').click(function(event)
			{
				$('#login-message').fadeOut();
				
				//Stop full page load
				event.preventDefault();
				
				$('#login-message').attr('class','');
				$('#login-message').addClass('fg-color-white');	
				
				// Check fields
				var loginUsername = $('#loginUsername').val();
				var loginPassword = $('#loginPassword').val();
				var view_multiple_store = $('#view_multiple_store').val();
				var store_data = $('#store_data').val();
				var mkey = $('#mkey').val();
				
				if (!loginUsername || loginUsername.length == 0)
				{
					$('#login-message').html(' Username Empty!');
					//change style input
					$('#login-message').addClass('bg-color-red');
					$('#login-message').addClass('icon-warning');
					$('#login-message').fadeIn();
				}
				else if (!loginPassword || loginPassword.length == 0)
				{
					$('#login-message').html(' Password Empty!');
					//change style input
					$('#login-message').addClass('bg-color-red');
					$('#login-message').addClass('icon-warning');
					$('#login-message').fadeIn();
				}
				else
				{
					
					// Target url
					var target = appUrl + "login";
					if (!target || target == '')
					{
						// Page url without hash
						target = document.location.href.match(/^([^#]+)/)[1];
					}
					
					// Request
					var data = {
						a: $('#a').val(),
						loginUsername: loginUsername,
						loginPassword: loginPassword,
						view_multiple_store: view_multiple_store,
						store_data: store_data,
						mkey:mkey
					};
					
					var redirect 	= appUrl+'backend';
									
					// Start timer
					var sendTimer = new Date().getTime();
					
					//message loading
					$('#loadBox').show();
					$('#loginBox').hide();
					$('#login-message').html(' Process, Please Wait...');
					$('#login-message').addClass('bg-color-blue');
					$('#login-message').addClass('icon-busy');
					$('#login-message').fadeIn();
					
					// Send
					$.ajax({
						url: target,
						dataType: 'json',
						type: 'POST',
						data: data,
						success: function(data, textStatus, XMLHttpRequest)
						{
							//alert(data.errors.reason);
							if (data.success)
							{
								$('#login-message').fadeOut();
								$('#login-message').html(' Redirecting, Please Wait..');
								$('#login-message').removeClass('bg-color-blue');
								$('#login-message').addClass('bg-color-green');
								$('#login-message').addClass('icon-checkmark');
								$('#login-message').fadeIn();
								
								// Small timer to allow the 'cheking login' message to show when server is too fast
								var receiveTimer = new Date().getTime();
								if (receiveTimer-sendTimer < 500)
								{
									setTimeout(function()
									{
										document.location.href = redirect;
										
									}, 500-(receiveTimer-sendTimer));
								}
								else
								{
									document.location.href = redirect;
								}
							}
							else
							{
								// Message
								//$('#login-message').html(data.errors.reason);
								$('#login-message').html(' Login Failed!');
								$('#login-message').removeClass('bg-color-blue');
								$('#login-message').addClass('bg-color-red');
								$('#login-message').addClass('icon-warning');
								$('#login-message').fadeIn();
								$('#loadBox').hide();
								$('#loginBox').fadeIn();
								
							}
						},
						error: function(XMLHttpRequest, textStatus, errorThrown)
						{
							// Message
							//$('#login-message').html(data.errors.reason);
							$('#login-message').html(' Login Failed!');
							$('#login-message').removeClass('bg-color-blue');
							$('#login-message').addClass('bg-color-red');
							$('#login-message').addClass('icon-warning');	
							$('#login-message').fadeIn();
							$('#loadBox').hide();
							$('#loginBox').fadeIn();
						}
					});
					
				}
			});
		});
	
	</script>
    <title><?php echo $title; ?></title>
</head>
<body class="modern-ui login_bg" >
	
<div class="page" id="page-login">
    <div class="page-region">
        <div class="page-region-content">
            
            <div class="grid" style="">
                <div class="row">
                    <div class="span4">
                        
						<?php
						if(!empty($cloud_data)){
							echo '<h2 class="icon-user-3 fg-color-darken" > LOGIN MERCHANT</h2>';
						}else{
							echo '<h1 class="icon-user-3 fg-color-darken" > LOGIN</h1>';
						}
						?>
						
                    </div>
                </div>               
            </div>
			<div class="grid">
                <div class="row">
                    <div class="span4">                        
						<form id="login-form" method="post" action="">						
							<input type="hidden" name="a" id="a" value="send" />
							<p class="fg-color-white" id="login-message" style="display:none; padding:6px 10px;"></p>
							
							<div id="loginBox">
								<?php
								if(!empty($view_multiple_store) AND !empty($data_multiple_store)){
									?>
									<div class="input-control">
										<select id="store_data" name="store_data">
										<?php
										foreach($data_multiple_store as $dt){
											 echo '<option value="'.$dt['id'].'">'.$dt['client_name'].'</option>';
										}
										?>
										</select>
									</div>
									<?php
									echo '<input type="hidden" name="view_multiple_store" id="view_multiple_store" value="1" />';
								}else{
									echo '<input type="hidden" name="store_data" id="store_data" value="" />';
									echo '<input type="hidden" name="view_multiple_store" id="view_multiple_store" value="0" />';
								}
								?>
								<input type="hidden" name="mkey" id="mkey" value="<?php echo $mkey; ?>" />
								
								<div class="input-control text loginUsername">
									<input type="text" id="loginUsername" name="loginUsername" class="with-helper" tabindex="0" placeholder="Username"/>
									<a href="javascript:void(0);" class="helper loginUsername"></a>
								</div>
								
								<div class="input-control password">
									<input type="password" id="loginPassword" name="loginPassword" class="with-helper" tabindex="1" placeholder="Password" />
									<a href="javascript:void(0);" class="helper loginPassword"></a>
								</div>
								
								<button class="bg-color-green fg-color-white" id="button_submit" style="float:right; margin-right:0px;">LOGIN <i class="icon-enter"></i></button>
								<!--<button class="bg-color-darken fg-color-white" id="button_option_menu" style="float:left; margin-right:0px;">OPTION <i class="icon-list"></i></button>
								-->
							</div>
							<div class="clearfix"></div>
							<div id="loadBox" class="padding20" style="display:none; text-align:center;">
								<img src="<?php echo BASE_URL; ?>assets/themes/frontend/images/loader.gif" width="64"/>
							</div>
							
						</form>
						<div class="clearfix"></div>
						<div class="footer_login">
							
							<?php
							if(!empty($cloud_data)){
								echo '<p>'.$cloud_data['merchant_nama'].'</p>';
							}else{
								echo '<p>'.config_item('program_name').'</p>';
							}
							
							echo config_item('copyright'); 
							?>
							<br/>
							
						</div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</div>


</body>
</html>