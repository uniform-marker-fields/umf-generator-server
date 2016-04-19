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
	
	$force_continue = db_get_gid_force_continue($link,$gid);
	
	$query = "SELECT PQ.`id`,PQ.`gid`,PQ.`type`,PQ.`width`,PQ.`height`,PQ.`kernel`,PQ.`kernel_type`,QD.`data`,PQ.`conflicts`,PQ.`colors`,PQ.`img_id`,PQ.`img_alg`,PQ.`module_type`,PQ.`cost`,QD.`runtime`,QD.`threshold_equal`
				FROM `process_queue` PQ
				LEFT JOIN `queue_data` QD
					ON QD.`id`=PQ.`id`
				WHERE `gid`=".$gid."
				ORDER BY `cost` ASC";
	$db_res = $link->query($query);
	
	$new_gid = 0;
	
	$count = intval($_REQUEST['count']);
	if($db_res)
	{
		if ($row = $db_res->fetch_assoc())
		{
			// TODO better cost..
			$new_gid = db_queue_push_new($link, $_REQUEST['name'], $row['type'], $row['width'], $row['height'], $row['kernel'], $row['kernel_type'], $row['module_type'], $row['conflicts'], $row['data'], $row['cost']+1000.0, $row['colors'], $row['img_id'], $row['img_alg'], $row['threshold_equal'], $_REQUEST['c_n'], $_REQUEST['c_s'], $_REQUEST['img_conv'], $row['runtime'], $force_continue);
			$i = 1;
			while ($row = $db_res->fetch_assoc())
			{
				$i++;
				if ($i > $count)
				{
					break;
				}
				db_queue_push($link, $new_gid, $row['type'], $row['width'], $row['height'], $row['kernel'], $row['kernel_type'], $row['module_type'], $row['conflicts'], $row['data'], $row['cost']+1000.0, $row['colors'], $row['img_id'], $row['img_alg'], $row['threshold_equal'], $_REQUEST['c_n'], $_REQUEST['c_s'], $_REQUEST['img_conv'], $row['runtime'], $force_continue);
			}
		}
	}
	
	if ($_REQUEST['keep'] == "false")
	{
		db_remove_gid($link, $gid);
	}
	
	echo "DONE";
} else {
	echo "FAIL2";
}

db_close($link);


?>
