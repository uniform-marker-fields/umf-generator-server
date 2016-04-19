<?php
require_once('inc/defines.php');
require_once('inc/database.php');
require_once('inc/common.php');
require_once('inc/xml.php');

header("Content-type: application/xml");

$link = db_connect();

$result = db_get_weighted($link, (isset($_GET['v']) ? intval($_GET['v']) : 1 ), isset($_GET['test']));


if($result['success'])
{
	echo get_xml($result);
}
else
{
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
	echo "<markers>\n";
	echo "</markers>\n";
}

/*echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
echo "<markers>\n";

if($result['success'])
{
	echo "<marker>\n";
	echo "<gid>{$result['gid']}</gid>\n";
	echo "<width>{$result['width']}</width>\n";
	echo "<height>{$result['height']}</height>\n";
	echo "<kernel>{$result['kernel']}</kernel>\n";
	echo "<data>{$result['data']}</data>\n";
	echo "<module_type>".$result['module_type']."</module_type>\n";
	echo "<type>".$result['type']."</type>\n";
	echo "<colors>".$result['colors']."</colors>\n";
	echo "<img_id>".$result['img_id']."</img_id>\n";
	echo "<img_alg>".$result['img_alg']."</img_alg>\n";
	echo "<img_server>".$_SERVER['SERVER_NAME']."</img_server>\n";
	echo "<img_path>".($result['img_id'] > 0 ? DOC_ROOT . getImagePath($result['img_id'],$result['img_extension']) : "")."</img_path>\n";
	echo "<runtime>".$result['runtime']."</runtime>\n";
	echo "<kernel_type>".$result['kernel_type']."</kernel_type>\n";
	echo "<threshold_equal>".$result['threshold_equal']."</threshold_equal>\n";
	echo "<cost_neighbors>".$result['cost_neighbors']."</cost_neighbors>\n";
	echo "<cost_similarity>".$result['cost_similarity']."</cost_similarity>\n";
	echo "<img_conv>".$result['img_conv']."</img_conv>\n";
	echo "</marker>\n";
} else {
	echo "";
}*/

db_close($link);
?>
