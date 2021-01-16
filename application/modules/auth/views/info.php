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
	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/js/extjs.4.2/theme/css/ext-all<?php echo $theme; ?>.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/modules.css" />	
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/desktop/css/ext-modules.css" />
	<script>
		var appUrl 		= "<?php echo BASE_URL; ?>";
		var programName	= "<?php echo config_item('program_name'); ?>";
		var copyright	= "<?php echo $copyright; ?>";
	</script>
	<style>
		.button-login .x-btn-inner {
			font-weight:bold; font-size:14px; color:<?php echo $button_color; ?>; padding-bottom:5px;
		}
	</style>
</head>
<body style="background:#83aac0 url(<?php echo BASE_URL; ?>apps.min/helper/login/background.jpg) center top no-repeat;">

	<?php
	if(!empty($view_multiple_store) AND !empty($data_multiple_store)){
		?>
		<div style="width:400px; margin:80px auto 0px;"><img src="<?php echo BASE_URL; ?>apps.min/helper/login/logo.png"></div>
		<?php
	}else{
		?>
		<div style="width:400px; margin:100px auto 0px;"><img src="<?php echo BASE_URL; ?>apps.min/helper/login/logo.png"></div>
		<?php
	}
	?>
	
	<script src="<?php echo BASE_URL; ?>assets/js/extjs.4.2/ext-all.js" type="text/javascript" charset="utf-8"></script>	
	
	<script type="text/javascript" charset="utf-8">
	var allowBlankMultiStore = true;
	var hiddenBlankMultiStore = true;
	var	heightFormLogin = 230;
	
	var win = new Ext.Window ({
		title: 'INPUT MERCHANT-KEY',
		width:300,
		height:heightFormLogin,
		//iconCls: 'btn-lock',
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
						id : 'is_cek',
						name: 'is_cek',
						value: 1
					},
					{
					  xtype : 'textfield',
					  name : 'mkey',
					  id : 'mkey',
					  value: '<?php if(!empty($mkey)){ echo $mkey; } ?>',
					  labelSeparator: '',
					  fieldLabel: '',
					  height: 30,
					  anchor: '100%',
					  margin: '0 0 8 0',
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
						width: 290,
						fieldLabel: '',
						items: [
							{
								xtype : 'displayfield',
								id : 'login-message',
								value: '<?php if(!empty($error)){ echo $error; } ?>',
								margin: '0 10 0 0',
								width: 160
							},
							{
								xtype: 'button',
								text : 'Cek<br/>Merchant',
								id : 'btnSave_Login',
								cls: 'button-login',
								width: 100,
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
					xtype: 'fieldcontainer',
					layout: {
						type: 'column'
					},
					width: 290,
					fieldLabel: '',
					items: [
						{
							xtype: 'button',
							text : 'Install Aplikasi ke Layar',
							id : 'btnSave_Install',
							//iconCls:'btn-save',
							//cls: 'button-login',
							width: 270,
							height: 30,
							handler : function() {
								doInstall();
							},
							hidden: true,
							margin: '0 0 10 0',
						},
						{
							xtype: 'button',
							text : 'Update Aplikasi',
							id : 'btnSave_Update',
							//iconCls:'btn-save',
							//cls: 'button-login',
							width: 270,
							height: 30,
							handler : function() {
								//doUpdate();
							},
							hidden: true,
							margin: '0 0 10 0',
						},
						{
							xtype: 'displayfield',
							width: 270,
							value: copyright,
							fieldStyle: 'text-align:center;',
						},
						
					]
				}
				
			]
		}],
		listeners : {
			show : function (window, eOpts) {
				window.alignTo(document.body, 't', [-150,220]);
			},
		}
		
	});

	function doLogin(){
		
		Ext.getCmp('login-message').setValue('');
		
		var form = Ext.getCmp('form_loginAplikasi').getForm();
		if (form.isValid()) {
			
			Ext.getCmp('login-message').setValue('<font color=blue><b>Harap Menunggu...</b></font>');
			Ext.getCmp('btnSave_Login').setDisabled(true);
			
			var redirect 	= appUrl+"login";
			var sendTimer = new Date().getTime();
						
			form.submit({
				url : appUrl + "login",												
				method: 'POST',
				params:{
					
				},
				waitMsg : 'Cek Merchant...',
				success : function(mainObj, formObj) {
					var rsp = Ext.decode(formObj.response.responseText);
					Ext.getCmp('btnSave_Login').setDisabled(false);
					
					if(rsp.success == false){
						if(!rsp.info){
							rsp.info = '';
						}
						Ext.getCmp('login-message').setValue(rsp.info);
						Ext.getCmp('mkey').focus();
						//ExtApp.Msg.error(rsp.info);
						return;
					}else{
					
						// Small timer to allow the 'cheking login' message to show when server is too fast
						var receiveTimer = new Date().getTime();
						if (receiveTimer-sendTimer < 500)
						{
							setTimeout(function()
							{
								document.location.href = 'm/'+rsp.merchant_key;
								
							}, 500-(receiveTimer-sendTimer));
						}
						else
						{
							document.location.href = 'm/'+rsp.merchant_key;
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
					Ext.getCmp('mkey').focus();
					//ExtApp.Msg.error(rsp.info);
				}
			});
		}
		
	}

	Ext.onReady(function() {
		win.show();
		
		var getOS = getMobileOperatingSystem();
		//alert(getOS+' = '+from_apps);
		if(getOS != 'general' && from_apps == 0){
			Ext.getCmp('from_apps').setValue(1);
			//window.location = appUrl+'login-apps';
		}
		
		//SW
		const options = {};
		new SW(options);
		
	});
	
	function getMobileOperatingSystem() {
	  var userAgent = navigator.userAgent || navigator.vendor || window.opera;

		  // Windows Phone must come first because its UA also contains "Android"
		if (/windows phone/i.test(userAgent)) {
			return "Windows Phone";
		}

		if (/android/i.test(userAgent)) {
			return "Android";
		}

		// iOS detection from: http://stackoverflow.com/a/9039885/177710
		if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
			return "iOS";
		}

		return "general";
	}
	
	
	const installBtn = Ext.getCmp('btnSave_Install');
	const updateBtn = Ext.getCmp('btnSave_Update');
	
	function SW(args) {
	  //this.button = Ext.getCmp(args.button);
	  //this.toast = Ext.getCmp(args.toast);

	  this.registerSW();
	};

	SW.prototype.registerSW = function() {
	  /*
	   *  Register SW dimulai disini
	   *  Copy script yang dicantumkan di artikel
	   */
	  if (!navigator.serviceWorker) return;

	  const that = this;

	  navigator.serviceWorker.register(appUrl+'sw-wepos.js')
		.then(function(reg) {
		  console.info('SW ok');

		  if (!navigator.serviceWorker.controller) return;

		  if (reg.waiting) {
			that.updateReady(reg.waiting);
			return;
		  }

		  if (reg.installing) {
			that.trackInstall(reg.installing);
			return;
		  }

		  reg.addEventListener('updatefound', function() {
			that.trackInstall(reg.installing);
		  });
		  
		  let refreshing;
		  navigator.serviceWorker.addEventListener('controllerchange', function() {
			if (refreshing) return;

			window.location = appUrl+'';
			refreshing = true;
		  });
		})
		.catch(function() {
		  console.error('SW failed!');
		});
	}

	SW.prototype.trackInstall = function(worker) {
	  const that = this;

	  worker.addEventListener('statechange', function() {
		if (worker.state === 'installed') {
		  that.updateReady(worker)
		}
	  })
	}

	const xwroker = null;
	SW.prototype.updateReady = function(worker) {
	  
	  //this.toast.show();
	  updateBtn.show();
	   
	  updateBtn.on('click', function(event) {
		//event.preventDefault();
		updateBtn.hide();
		window.location = appUrl+'';
		worker.postMessage({ action: 'skipWaiting' })      
	  });
	  
	}

	let deferredPrompt;
	//const addBtn = Ext.getCmp('btnSave_Install');
	installBtn.hide();
	updateBtn.hide();
	

	window.addEventListener('beforeinstallprompt', (e) => {
	  // Prevent Chrome 67 and earlier from automatically showing the prompt
	  e.preventDefault();
	  // Stash the event so it can be triggered later.
	  deferredPrompt = e;
	  // Update UI to notify the user they can add to home screen
	  installBtn.show();

	  /*
	  addBtn.addEventListener('click', (e) => {
		//doInstall
	  });
	  */
	  
	});
	
	
	function doInstall(){
		// hide our user interface that shows our A2HS button
		installBtn.hide();
		
		// Show the prompt
		deferredPrompt.prompt();
		// Wait for the user to respond to the prompt
		deferredPrompt.userChoice.then((choiceResult) => {
			if (choiceResult.outcome === 'accepted') {
			  console.log('User accepted the A2HS prompt');
			} else {
			  console.log('User dismissed the A2HS prompt');
			}
			deferredPrompt = null;
		  }); 
	}

	</script>		
	<!-- Start of wepos Zendesk Widget script -->
	<script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=070b419f-4ff0-414d-9bee-29eb623a28b5"> </script>
	<!-- End of wepos Zendesk Widget script -->