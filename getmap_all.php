<?php
require_once('inc/defines.php');
require_once('inc/database.php');

header("Content-type: text/plain");

$link = db_connect();
$query = "SELECT `gid` from `marker_gid` WHERE `state`=0";
$result = $link->query($query);
while($row = $result->fetch_row())
{
	echo db_remove_gid($link, $row[0]);
}
$query = "DELETE FROM `marker_gid` WHERE `state`=0";
$link->query($query);
/*
$query = "SELECT DISTINCT `width`, `height`, `kernel`, `data` from `process_queue` LEFT JOIN `queue_data` ON `process_queue`.`id`=`queue_data`.`id`";
$result = $link->query($query);
$index = 0;
while($row = $result->fetch_assoc())
{
	echo $index.";".$row['width'].";".$row['height'].";".$row['kernel'].";".$row['data']."\n";
	$index++;
}
/*
for($i = 0; $i < 805; $i++)
{
	$query = "INSERT INTO `pmgen`.`marker_gid` (`gid`, `short_name`, `state`) VALUES ($i, 'tile_$i', '1')";
	$link->query($query);
}
*/
db_close($link);

?>
