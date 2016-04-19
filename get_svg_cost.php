<?php

require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/checkcost.php');
require_once('inc/checkmap.php');
require_once('inc/svg_cost.php');

$link = db_connect();


$TYPE_READY = 1;
$TYPE_QUEUE = 0;


$id = 0;
$live = false;
if(isset($_REQUEST["gid"]))
{
	$id = db_get_smallest_conflict_gid($link, intval($_REQUEST["gid"]));
	$_REQUEST["ready"] = 0;
	$live = true;
} else {
	$id = $_REQUEST["id"];
}

if($live){
	header("Content-Type: text/plain");
} else {
	header("Content-Type: image/svg+xml");
}

$type = intval($_REQUEST["ready"]);

$width = 1;
$height = 1;
$tile_size = 1;
$data = '';
$query = "";


// TODO make checking map from C code..
if($type == $TYPE_READY)
{
    $query="SELECT `type`,`width`,`height`,`kernel`,`data`,`colors`,`kernel_type`,`module_type` FROM `markers_ready` WHERE `id`=$id";
} else {
    $query="SELECT `type`,`width`,`height`,`kernel`,`data`,`colors`,`kernel_type`,`module_type` FROM `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` WHERE `process_queue`.`id`=$id";
}

$result = $link->query($query);
$row = $result->fetch_assoc();
$type = $row['type'];
$width = $row['width'];
$height = $row['height'];
$tile_size = $row['kernel'];
$data = $row['data'];
$colors = $row['colors'];
$kernel_type = $row['kernel_type'];
$module_type = $row['module_type'];

$selfcollisions = array();
$collisions = checkmap($type, $width, $height, $tile_size, $kernel_type, $module_type, $data, $colors, true, $selfcollisions);
$costs = checkcost($type, $width, $height, $tile_size, $module_type, $data, $colors, $collisions, $selfcollisions, $x, true);

$svg_str = get_svg_cost($type, $width, $height, $tile_size, $module_type, $costs);

echo $svg_str;

?>
