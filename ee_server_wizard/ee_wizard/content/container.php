<html><?php if ( ! defined('SERVER_WIZ')) exit('No direct script access allowed');?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo $title; ?></title>

<link rel="stylesheet" href="./content/wizard.css" type="text/css" media="all" title="wizard css" charset="utf-8">


</head>
<body>

	<div id="outer">
	
		<div id="header">
		
			<img src="./content/logo.gif" width="241" height="88" border="0" alt="ExpressionEngine Server Compatibility Wizard" />
		
		</div>
	
		<div id="inner">		
		
			<h1><?php echo $heading; ?></h1>
			
			<div id="content">
			
				<?php echo $content; ?>
			
			</div>
			
			<div id="footer">
				
				ExpressionEngine - &#169; 2002&ndash;<?php echo date('Y') ?> EllisLab, Inc.
				
			</div>

		</div>
				
	</div>

</body>
</html>