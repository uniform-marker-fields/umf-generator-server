<?php
require_once('inc/defines.php');
require_once('inc/database.php');

session_start();

?>

<html>
	<head>
		<title>PerfectMapGenerator server</title>
		<link rel="icon" type="image/ico" href="img/favicon.ico">
		<link rel="stylesheet" media="screen" type="text/css" href="css/colorpicker.css" />
		<link rel="stylesheet" type="text/css" href="css/style.css" />
		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/jquery.cookie.js"></script>
		<script type="text/javascript" src="js/colorpicker.js"></script>
		<script type="text/javascript" src="js/jquery.simplemodal.js"></script>
		<script type="text/javascript" src="js/md5.js"></script>
		<script type="text/javascript" src="js/pmgen_globals.js"></script>
		<script type="text/javascript" src="js/pmgen_generate.js"></script>
		<script type="text/javascript" src="js/pmgen_queue.js"></script> 
		<script type="text/javascript" src="js/pmgen_markers.js"></script>
		<script type="text/javascript" src="js/pmgen_ready.js"></script> 
		<script type="text/javascript">

			
			var cpage=1;
			
			
			

			function settings_get_page()
			{
				$.get("ajax/page_settings.php",
					function(res){
						$("#page_content").html(res);
					});
			};




			
			//login
			function post_login()
			{
				var data = {'u': $("#username")[0].value,
						'md5': CryptoJS.MD5($("#password")[0].value).toString(CryptoJS.enc.Hex)};
				$.post("ajax/login.php", data,
					function(res) {
						//alert(res.message);
						$("#login_result").html(res.message);
						setTimeout("settings_get_page()", 3000);
					}
					, "json");
			};
			
			//login
			function post_logout()
			{
				var data = {'logout': 1};
				$.post("ajax/login.php", data,
					function(res) {
						//alert(res.message);
						$("#login_result").html(res.message);
						setTimeout("settings_get_page()", 3000);
					}
					, "json");
			};
			
			$(document).ready(function() {
<?php
			$cp = 'queue';
			if(isset($_REQUEST['p']))
			{
				$cp = $_REQUEST['p'];
			}
			
			if($cp == 'ready')
			{
				?>
				var page = 1;
				if(document.location.hash)
				{
					var phash = document.location.hash.replace('#','');
					page = phash.split('_')[0];
				}
				ready_get_page(page);
				<?php
			} elseif($cp == 'generate') {
				echo "generator_get_page();";
			} elseif($cp == 'settings') {
				echo "settings_get_page();";
			} elseif($cp == 'queue_markers') {
				echo "queue_markers_get_page(1, true);";
			} elseif($cp == 'queue_details') {
				echo "queue_details_get_page({$_REQUEST['id']});";
			} elseif($cp == 'marker_details') {
				echo "marker_details_get_page({$_REQUEST['gid']})";
			} elseif($cp == 'ready_details') {
				echo "ready_details_get_page({$_REQUEST['id']});";
			} else {
				echo "queue_get_page(1);";
			}
			
			$page_self = $_SERVER['PHP_SELF'];
?>
			});

		</script>
	</head>
	<body>
	<div class="top-panel">
		<input type="checkbox" id="reload_checkbox" checked="<?php echo isset($_COOKIE['auto_reload']); ?>" onChange="toggle_auto_reload(this)">auto reload</input>
	</div>
	  <div class="header">
  		<h1>PerfectMapGenerator status page</h1>
  		<div id="page_loading"> <img src="img/loading.gif" alt="loading"/></div>
		</div>
		
    <div class="navigation">
  		<ul>
  			<li class="pqueue"><a href="<?php echo $page_self ?>?p=queue">Queue</a></li>
  			<li class="pgid"><a href="<?php echo $page_self ?>?p=queue_markers">In-queue markers</a></li>
  			<li class="pready"><a href="<?php echo $page_self ?>?p=ready">Ready</a></li>
  			<li class="pgen"><a href="<?php echo $page_self ?>?p=generate">Generate</a></li>
  			<li class="psettings"><a href="<?php echo $page_self ?>?p=settings">Settings</a></li>
  		</ul>
		</div>
		
    <div class="cleaner" />
		
		<div id="page_content">
		</div>

	</body>
</html>
