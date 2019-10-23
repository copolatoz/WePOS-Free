<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="author" content="<?php echo $meta_author; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">

    <link rel="shortcut icon" href="<?php echo BASE_URL; ?>apps.min/helper/login/favicon.ico" />
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/extjs.4.2/theme/css/ext-all<?php echo $theme; ?>.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/modules.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/ext-modules.css" />	
	<script>
		var appUrl 		= "<?php echo BASE_URL; ?>";
		var programName	= "<?php echo config_item('program_name'); ?>";
		var copyright	= "<?php echo config_item('copyright'); ?>";
	</script>
	<style>
		.button-login .x-btn-inner {
			font-weight:bold; font-size:14px; color:<?php echo $button_color; ?>; padding-bottom:5px;
		}
	</style>
</head>
<body style="background:#83aac0 url(<?php echo BASE_URL; ?>apps.min/helper/login/background.jpg) center top no-repeat;">
	<div style="width:400px; margin:90px auto 0px;"><img src="<?php echo BASE_URL; ?>apps.min/helper/login/logo.png"></div>
	<script src="<?php echo BASE_URL; ?>assets/js/extjs.4.2/ext-all.js" type="text/javascript" charset="utf-8"></script>	
	<?php 
	$from_apps_text = '';
	if(!empty($from_apps)){
		$from_apps_text = ' APPS / CASHIER ';
	}
	$login_title = 'LOGIN'.$from_apps_text;
	if(!empty($cloud_data)){
		$login_title = 'LOGIN '.$from_apps_text.'&mdash; MERCHANT';
	}

	if(!empty($view_multiple_store) AND !empty($data_multiple_store)){
		
	}
	?>
	
	<script type="text/javascript" charset="utf-8">
	
	var win = new Ext.Window ({
		title: '<?php echo $login_title; ?>',
		width:300,
		height:230,
		iconCls: 'btn-lock',
		animCollapse:false,
		constrainHeader:true,
		resizable:false,
		minimizable: false,
		maximizable: false,
		closable: false,
		draggable: false,
		layout: 'fit',
		border: 0,
		items: [
			{
				xtype: 'form',
				id: 'form_loginAplikasi',
				//margin: '0 20 0 0',
				defaults:{
					labelWidth: 100,
				},
				bodyPadding: 10,
				border: 0,
				items: [
					{
						xtype: 'hidden', 
						id : 'a',
						name: 'a',
						value: 'send'
					},
					{
						xtype: 'hidden', 
						id : 'from_apps',
						name: 'from_apps',
						value: <?php echo $from_apps; ?>
					},
					{
						xtype: 'hidden', 
						id : 'view_multiple_store',
						name: 'view_multiple_store',
						value: 0
					},
					{
						xtype: 'hidden', 
						id : 'store_data',
						name: 'store_data',
						value: ''
					},
					{
						xtype: 'hidden', 
						id : 'mkey',
						name: 'mkey',
						value: '<?php echo $mkey; ?>'
					},
					{
					  xtype : 'textfield',
					  name : 'loginUsername',
					  id : 'loginUsername',
					  labelSeparator: '',
					  fieldLabel: 'Username',
					  height: 30,
					  anchor: '100%',
					  fieldStyle: 'font-weight:bold; font-size:14px; text-align:left; color:#666;',
					  labelStyle: 'font-weight:bold; font-size:14px; text-align:left; color:#666; padding-top:3px;',
					  allowBlank: false,
					  listeners: {
						specialkey: function(field, e){
							if (e.getKey() == e.ENTER) {
								doLogin();
							}
						}
					  }
					},{
					  xtype : 'textfield',
					  name : 'loginPassword',
					  id : 'loginPassword',
					  labelSeparator: '',
					  fieldLabel: 'Password',
					  inputType: 'password',
					  height: 30,
					  anchor: '100%',
					  margin: '0 0 15 0',
					  fieldStyle: 'font-weight:bold; font-size:14px; text-align:left; color:#666;',
					  labelStyle: 'font-weight:bold; font-size:14px; text-align:left; color:#666; padding-top:3px;',
					  allowBlank: false,
					  listeners: {
						specialkey: function(field, e){
							if (e.getKey() == e.ENTER) {
								doLogin();
							}
						}
					  }
				   },
				   {
						xtype: 'fieldcontainer',
						layout: {
							type: 'column'
						},
						width: 390,
						fieldLabel: '',
						items: [
							{
								xtype : 'displayfield',
								id : 'login-message',
								value: '',
								margin: '0 10 0 0',
								width: 160
							},
							{
								xtype: 'button',
								text : 'Login',
								id : 'btnSave_Login',
								iconCls:'btn-lock-open',
								iconAlign: 'top',
								cls: 'button-login',
								width: 100,
								height: 45,
								handler : function() {
									doLogin();
								}
							}
						]
					}
				]
			}
		],
		dockedItems: [
		{
			xtype: 'toolbar',
			dock: 'bottom',
			items: [
				{
					xtype: 'displayfield',
					width: 270,
					value: copyright,
					fieldStyle: 'text-align:center;',
				}
			]
		}]
		
	});

	function doLogin(){
		
		Ext.getCmp('login-message').setValue('');
		
		var form = Ext.getCmp('form_loginAplikasi').getForm();
		if (form.isValid()) {
			
			Ext.getCmp('login-message').setValue('<font color=green><b>Harap Menunggu...</b></font>');
			Ext.getCmp('btnSave_Login').setDisabled(true);
			
			var redirect 	= appUrl+'backend';
			var sendTimer = new Date().getTime();
						
			form.submit({
				url : appUrl + "login",												
				method: 'POST',
				params:{
					
				},
				waitMsg : 'Login...',
				success : function(mainObj, formObj) {
					var rsp = Ext.decode(formObj.response.responseText);
					Ext.getCmp('btnSave_Login').setDisabled(false);
					
					if(rsp.success == false){
						if(!rsp.info){
							rsp.info = '';
						}
						Ext.getCmp('login-message').setValue(rsp.info);
						Ext.getCmp('loginUsername').focus();
						//ExtApp.Msg.error(rsp.info);
						return;
					}else{
					
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
					
				},
				failure : function(mainObj, formObj) {
					var rsp = Ext.decode(formObj.response.responseText);
					Ext.getCmp('btnSave_Login').setDisabled(false);
						
					if(!rsp.info){
						rsp.info = '';
					}
					Ext.getCmp('login-message').setValue(rsp.info);
					Ext.getCmp('loginUsername').focus();
					//ExtApp.Msg.error(rsp.info);
				}
			});
		}
		
	}

	Ext.onReady(function() {
		win.show();
	});
	</script>	
	<!-- Start of wepos Zendesk Widget script -->
	<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=070b419f-4ff0-414d-9bee-29eb623a28b5"> </script>
	<!-- End of wepos Zendesk Widget script -->
</body>
</html>