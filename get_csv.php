<?php

require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/csv.php');

header("Content-type: text/plain");

$link = db_connect();

$TYPE_READY = 1;
$TYPE_QUEUE = 0;


$id = $_REQUEST["id"];
$type = intval($_REQUEST["ready"]);

$width = 0;
$height = 0;
$tile_size = 0;
$data = '';
$query = "";
if($type == $TYPE_READY)
{
    $query="SELECT `type`,`width`,`height`,`kernel`,`data`,`colors`,`kernel_type`,`module_type`,`threshold_equal` FROM `markers_ready` WHERE `id`=$id";
} else {
    $query="SELECT `type`,`width`,`height`,`kernel`,`data`,`colors`,`kernel_type`,`module_type`,`queue_data`.`threshold_equal` FROM `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id` WHERE `process_queue`.`id`=$id";
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
$threshold_equal = $row['threshold_equal'];

$csv_str = get_csv($type, $width, $height, $tile_size, $data, $colors, $kernel_type, $module_type, $threshold_equal);
echo $csv_str;

db_close($link);

?>
