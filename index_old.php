<?php
require_once('inc/defines.php');
require_once('inc/database.php');

?>

<html>
	<head>
		<title>PerfectMapGenerator server</title>
		<script type="text/javascript" src="js/jquery.js"></script> 
		<script type="text/javascript">
			//add new type into the database
			function post_generate_new()
			{
				$("#gen_loading").show();
				$("#gen_result").hide();
				$("#gen_submit").hide();
				var data = {w: $("#gen_width")[0].value,
						h: $("#gen_height")[0].value, k: $("#gen_kernel")[0].value,
						c: $("#gen_count")[0].value};
				$.post("ajax/generate_new.php", data,
					function(res) {
						$("#gen_loading").hide();
						if(res.success)
						{
							$("#gen_result").html(
								"Successfully generated " +
								res.count +
								" with size" + res.width + "x" + res.height + "<br />"+
								"Check the queue..."
								);
						} else {
							$("#gen_result span")[0].html("Generation failed");
						}
						$("#gen_result").show();
						$("#gen_submit").show();
					}, "json");
					
			};
		</script>
	</head>
	<body>
		<h1>PerfectMapGenerator status page</h1>
<?php

$link = db_connect();

?>


<!-- *************************************QUEUE********************************* -->
<div id="queue">
	<h2>Queue</h2>
<table>
	<tr>
		<th>Short name (GID)</th>
		<th>Width</th>
		<th>Height</th>
		<th>Kernel size</th>
		<th>Conflict count</th>
		<th>Data,img</th>
	</tr>
<?php
	$results = db_get_queue($link);
	foreach($results as $row)
	{
		echo "<tr>\n";
		echo "<td>{$row['short_name']} ({$row['gid']})</td>\n";
		echo "<td>{$row['width']}</td>\n";
		echo "<td>{$row['height']}</td>\n";
		echo "<td>{$row['kernel']}</td>\n";
		echo "<td>{$row['conflicts']}</td>\n";
		echo "<td><a href=\"get_csv.php?id={$row['id']}&ready=0\">csv</a> <a href=\"get_svg.php?id={$row['id']}&ready=0\">svg</a></td>\n";
		echo "</tr>\n";
	}
?>
</table>
	
</div>


<!-- *************************************READY********************************* -->

<div id="ready">
	<h2>Ready</h2>
<table>
	<tr>
		<th>Short_name (GID)</th>
		<th>Width</th>
		<th>Height</th>
		<th>Kernel size</th>
		<th>Data,img</th>
	</tr>
<?php
	$results = db_get_ready($link, 0, 300);
	foreach($results as $row)
	{
		echo "<tr>\n";
		echo "<td>{$row['short_name']} ({$row['gid']})</td>\n";
		echo "<td>{$row['width']}</td>\n";
		echo "<td>{$row['height']}</td>\n";
		echo "<td>{$row['kernel']}</td>\n";
		echo "<td><a href=\"get_csv.php?id={$row['id']}&ready=1\">csv</a> <a href=\"get_svg.php?id={$row['id']}&ready=1\">svg</a></td>\n";
		echo "</tr>\n";
	}
?>
</table>
	
</div>

<!-- *************************************NEW********************************* -->
<div id="generate_new">
	<span>Generate new:</span>
	<ul>
		<li>Width: <input type="text" name="gen_width" id="gen_width" value="50"/> </li>
		<li>Height: <input type="text" name="gen_height" id="gen_height" value="50"/> </li>
		<li>Kernel size: <input type="text" name="gen_kernel" id="gen_kernel" value="4"/> </li>
		<li>Count: <input type="text" name="gen_count" id="gen_count" value="10"/> </li>
	</ul>
	<div id="gen_submit"><input type="button" value="Generate" onclick="post_generate_new()"/></div>
	<div id="gen_loading" style="display:none"> <img src="img/loading.gif" alt="loading"/></div>
	<div id="gen_result">
		...	
	</div>
</div>



	</body>
<?php
db_close($link);
?>

</html>
