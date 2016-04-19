<?php
require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/checkmap.php');
require_once('inc/checkcost.php');

ob_start();
?>
<html>
<head>
<title>post result</title>
</head>
<body>
<h1>results</h1>
<?php
echo "<b>ID</b>".$_GET["id"]."<br />\n";
echo "<b>Size (w,h):</b>".$_GET["w"].", ".$_GET["h"]."<br />\n";
echo "<b>Type (t): </b>".$_GET["t"]." <br />\n";
echo "<b>Unique size (k):</b> ".$_GET["k"]." <br />\n";
echo "<b>Colors (c):</b> ".$_REQUEST["colors"]."<br />\n";
echo "<b>Number of conlficts (c):</b> ".$_REQUEST["c"]."<br />\n";
echo "<b>Data:</b><br />\n";
?>
<p style="font-family: monospace">
<?php


$id = $_GET["id"];
$type = $_GET["t"];
$width = intval($_GET["w"]);
$height = intval($_GET["h"]);
$kernel = intval($_GET["k"]);
$data = trim($_REQUEST["data"]);
$colors = trim($_REQUEST["colors"]);
$runtime = doubleval($_REQUEST["rt"]);
$kernel_type = intval($_GET["k_t"]);
$img_id = intval($_REQUEST["img_id"]);
$img_alg = trim($_REQUEST["img_alg"]);
$cost = floatval($_REQUEST["c"]);
$module_type = intval($_REQUEST["m_t"]);
$threshold_equal = intval($_REQUEST["t_e"]);
$cost_neighbors = trim($_REQUEST["c_n"]);
$cost_similarity = floatval($_REQUEST["c_s"]);
$img_conv = trim($_REQUEST["img_conv"]);

$success = False;


if($width > 0 && $height > 0 && $kernel > 0 && strlen($data) == $width*$height && checkColors($type,$colors))
{
	$selfconflicts = array();
	$conlficts_arr = checkMap($type, $width, $height, $kernel, $kernel_type, $module_type, $data, $colors, true, $selfconflicts);
	
	$conflicts = count($conlficts_arr);
	
	echo "Checking the map returned ".$conflicts." conflicts\n";
	$plusConflicts = 0;
	//$cost = ($img_id > 0 ? $cost : checkCost($type, $width, $height, $kernel, $module_type, $data, $colors, $conlficts_arr, $selfconflicts, $plusConflicts));
	$conflicts += $plusConflicts;
	
	$link = db_connect();

	if($conflicts == 0)
	{
		if (db_get_gid_force_continue($link, $id))
		{
			db_queue_push($link, $id, $type, $width, $height, $kernel, $kernel_type, $module_type, $conflicts, $data, $cost, $colors, $img_id, $img_alg, $threshold_equal, $cost_neighbors, $cost_similarity, $img_conv, $runtime, true);
			db_clean_gid_non_zero_conflict($link, $id);
		} else {
			db_ready_push($link, $id, $type, $width, $height, $kernel, $kernel_type, $module_type, $data, $colors, $img_id, $img_alg, $threshold_equal, $cost_neighbors, $cost_similarity, $img_conv, $runtime);
			if(db_remove_gid($link, $id) > 0)
			{
				db_enable_next($link);
			}
		}
		
		echo "Storing without conflicts\n";
	} else {
		db_queue_push($link, $id, $type, $width, $height, $kernel, $kernel_type, $module_type, $conflicts, $data, $cost, $colors, $img_id, $img_alg, $threshold_equal, $cost_neighbors, $cost_similarity, $img_conv, $runtime, db_get_gid_force_continue($link, $id));
		
		echo "Storing with conflicts\n";
	}
	
	db_close($link);
	
	$success = True;
}

/*for($i = 0; $i < $height; $i++)
{
	for($j = 0; $j < $width; $j++)
	{
		echo $_REQUEST["data"][$j + $i*$width];
	}
	echo "<br />";
}*/
?>
</p>
<h2>
<?php
if($success)
{
	echo "Everything went smoothly";
} else {
	echo "TROLOLOLLOLOLOLLLOLOLOOO";
}
?>
</h2>
</body>
</html>
<?php
ob_end_flush();
?>
