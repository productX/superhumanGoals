<!DOCTYPE html> 
<html> 
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1">
		<title>Page Title</title>
		<link rel="stylesheet"  href="http://code.jquery.com/mobile/1.0a4.1/jquery.mobile-1.0a4.1.min.css" />
		<link rel="stylesheet" href="../_assets/css/jqm-docs.css"/>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.min.js"></script>
		<script type="text/javascript" src="http://code.jquery.com/mobile/1.0a4.1/jquery.mobile-1.0a4.1.min.js"></script>
		<script type="text/javascript" src="docs/docs.js"></script>
	</head>
	<body> 

		<!-- Start of second page -->
		<div data-role="page" id="bar" data-cache="never">

			<div data-role="header">
				<h1>Bar</h1>
			</div><!-- /header -->

			<div data-role="content">	
				<h2>Bar</h2>
				<p>I'm the bar page. <?php echo rand()*100;?></p>		
				<p><a href="jqmtest1-foo.php" data-transition="none">Back to foo</a></p>	
			</div><!-- /content -->
			
			<div data-role="footer">
				<h4>Page Footer</h4>
			</div><!-- /footer -->
		</div><!-- /page -->

	</body>
</html>
