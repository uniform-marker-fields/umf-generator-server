<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');

session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$result = array('message' => "Login failed!", 'success' => False);

if(isset($_REQUEST['logout']))
{
	$_SESSION['logged_in'] = False;
	$result['message'] = "Logout successful.";
	$result['success'] = True;
	
} else {
	$logged_in = False;

	$user = $_REQUEST['u'];
	$pw = $_REQUEST['md5'];
    $result['message'] = $pw;

	if(isset($USERS[$user]) && $USERS[$user] == $pw)
	{
		$logged_in = True;
		$result['message'] = "Logged in successfully";
		$result['success'] = True;
		$_SESSION['logged_in'] = True;
	}
}

header("Content-type: application/json");
echo json_encode($result);

?>