<?php
require_once('inc/defines.php');

?>

<html>
	<head>
		<title>PerfectMapGenerator server</title>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<script type="text/javascript" src="js/jquery.js"></script> 
		<script type="text/javascript">
			
			var timeout;
			
			function get_new_svg($gid)
			{
				clearTimeout(timeout);
				$("#page_loading").show();
				$.get("get_svg.php?ready=0&gid="+$gid,
					function(res){
						$("#page_content").html(res);
						timeout = setTimeout("get_new_svg("+$gid+")", 30000);
						$("#page_loading").hide();
					});
			};
			
			$(document).ready(function() {
<?php
			$gid = '1';
			if(isset($_REQUEST['gid']))
			{
				$gid = $_REQUEST['gid'];
			}
			
			echo "get_new_svg($gid);";
?>
			});
		</script>
	</head>
	<body>
	<div id="page_loading" style="display:none;position:absolute;right:50px;top:50px;"> <img src="img/loading.gif" alt="loading"/></div>
		<div id="page_content">
		</div>

	</body>
</html>