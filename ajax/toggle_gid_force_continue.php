<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');


//==================================================================
//SESSION
session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];

if(!$logged_in)
{
	echo "FAIL_LOGIN";
	return;
}
//==================================================================



$link = db_connect();

if(isset($_REQUEST['gid']))
{
	$gid = intval($_REQUEST['gid']);
	if (db_get_gid_force_continue($link,$gid))
	{
		$query = "SELECT PQ.`id`,PQ.`gid`,PQ.`type`,PQ.`width`,PQ.`height`,PQ.`kernel`,PQ.`kernel_type`,QD.`data`,PQ.`colors`,PQ.`img_id`,PQ.`img_alg`,PQ.`module_type`,QD.`runtime`,QD.`threshold_equal`,QD.`cost_neighbors`,QD.`cost_similarity`,QD.`img_conv`
					FROM `process_queue` PQ
					LEFT JOIN `queue_data` QD
						ON QD.`id`=PQ.`id`
					WHERE `conflicts`=0 AND `gid`=".$gid."
					ORDER BY `cost` ASC";
		$db_res = $link->query($query);
		if($db_res)
		{
			if ($row = $db_res->fetch_assoc())
			{
				db_ready_push($link, $row['gid'], $row['type'], $row['width'], $row['height'], $row['kernel'], $row['kernel_type'], $row['module_type'], $row['data'], $row['colors'], $row['img_id'], $row['img_alg'], $row['threshold_equal'], $row['cost_neighbors'], $row['cost_similarity'], $row['img_conv'], $row['runtime']);
				db_remove_gid($link, $row['gid']);
			}
		}
	}
	if(db_toggle_gid_force_continue($link,$gid))
	{
		echo "OK";
	} else {
		echo "FAIL";
	}
} else {
	echo "FAIL2";
}

db_close($link);


?>
