<?php

require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/xml.php');

header("Content-type: application/xml");

$link = db_connect();

$TYPE_READY = 1;
$TYPE_QUEUE = 0;


$id = $_REQUEST["id"];
$type = intval($_REQUEST["ready"]);

$query = "";
if($type == $TYPE_READY)
{
    $query = "SELECT `mr`.`type`, `mr`.`width`, `mr`.`height`, `mr`.`kernel`, `mr`.`gid`, `mr`.`data`, 0 as `conflicts`
				, `mr`.`colors`, `mr`.`img_id`, `mr`.`img_alg`, `img`.`img_extension`, `mr`.`runtime`, `mr`.`kernel_type`, `mr`.`module_type`
				, `mr`.`threshold_equal`, `mr`.`cost_neighbors`, `mr`.`cost_similarity`, `mr`.`img_conv`
				FROM `markers_ready` as `mr`
				LEFT JOIN `images` as `img` ON `mr`.`img_id`=`img`.`img_id`
				WHERE `mr`.`id`=$id";
} else {
    $query = "SELECT `pq`.`type`, `pq`.`width`, `pq`.`height`, `pq`.`kernel`, `pq`.`gid`, `qd`.`data`, `pq`.`conflicts`
				, `pq`.`colors`, `pq`.`img_id`, `pq`.`img_alg`, `img`.`img_extension`, `qd`.`runtime`, `pq`.`kernel_type`, `pq`.`module_type`
				, `qd`.`threshold_equal`, `qd`.`cost_neighbors`, `qd`.`cost_similarity`, `qd`.`img_conv`
				FROM `process_queue` as `pq`
				LEFT JOIN `queue_data` as `qd` ON `pq`.`id`=`qd`.`id`
				LEFT JOIN `images` as `img` ON `pq`.`img_id`=`img`.`img_id`
				WHERE `pq`.`id`=$id";
}

$result = $link->query($query);

if ($result->num_rows > 0)
{
	$row = $result->fetch_assoc();
	echo get_xml($row);
}
else
{
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	echo "<markers>\n";
	echo "</markers>\n";
}

db_close($link);

?>
