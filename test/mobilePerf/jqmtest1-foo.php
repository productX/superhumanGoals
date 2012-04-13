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
		<script type="text/javascript">
			jQuery('div').live('pagehide', function(event, ui){
			  var page = jQuery(event.target);

			  if(page.attr('data-cache') == 'never'){
				page.remove();
			  };
			});
		</script>
	</head>
	<body> 

		<!-- Start of first page -->
		<div data-role="page" id="foo" data-cache="never">

			<div data-role="header">
				<h1>Foo</h1>
			</div><!-- /header -->

			<div data-role="content">	
				<h2>Foo</h2>
				<p>I'm first in the source order so I'm shown as the page. <?php echo rand()*100;?></p>		
				<p>View internal page called <a href="jqmtest1-bar.php" data-transition="none">bar</a></p>	
				<p>View internal page called <a href="jqmtest1-baz.php" data-transition="none">baz</a> as a dialog.</p>
			</div><!-- /content -->
			
			<div data-role="footer">
				<h4>Page Footer</h4>
			</div><!-- /footer -->
		</div><!-- /page -->

	</body>
</html>
