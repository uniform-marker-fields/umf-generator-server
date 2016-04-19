<?php
require_once('../inc/defines.php');
require_once('../inc/common.php');
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

$start = intval($_REQUEST['start']);

$result = array();
$result["success"] = True;

$db = db_connect();

$query="SELECT * FROM `images` ORDER BY `img_id` DESC LIMIT $start, 8";
$db_res = $db->query($query);
if($db_res)
{
	$result["images"] = array();
	if ($row = $db_res->fetch_assoc())
	{
		do {
			$result["images"][] = array('id' => $row['img_id'], 'url' => getImageURL($row['img_id'], $row['img_extension'], true));
		} while ($row = $db_res->fetch_assoc());
		
		$result["left"] = ($start != 0);
				
		$query="SELECT * FROM `images`";
		$db_res = $db->query($query);
		$result["right"] = ($db_res->num_rows-9 > $start);
	} else {
		$result["success"] = False;
	}
	
} else {
	$result["success"] = False;
}

echo json_encode($result);

db_close($db);


?>
