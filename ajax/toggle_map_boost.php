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

if(isset($_REQUEST['id']))
{
	if(db_toggle_boost($link,intval($_REQUEST['id'])))
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