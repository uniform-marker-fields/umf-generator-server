<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');
require_once('../inc/details.php');


//==================================================================
//SESSION
session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

$logged_in = $_SESSION['logged_in'];

//==================================================================


if($logged_in == FALSE)
{
?>
	<div id="queue">
		<h2>Queue</h2>
		<div class="error">
			Please log in to view details!
		</div>
	</div>
<?php
	exit(0);
}

?>

<!-- *************************************QUEUE********************************* -->
<div id="queue">
	<h2>Queue - Details</h2>
<?php
	
	$failed = False;
	$info = array();

	if(isset($_REQUEST['id']))
	{
		$link = db_connect();
	
		$info = db_get_instance_info($link, $_REQUEST['id']);

		if($info["success"] == False)
		{
			$failed = True;
		}
	
		db_close($link);
	} else {
		$failed = True;
	}

	if($failed)
	{
?>
		<div class="error">
			Unable to find given marker instance!
		</div>
<?php
	} else {
?>
<div class="marker_info">

<h3>
<a href="<?php echo $_SERVER['PHP_SELF']; ?>?p=marker_details&gid=<?php echo $info['gid']; ?>">
<?php echo "{$info['short_name']} ({$info['gid']})"; ?>
</a></h3>

	<?php show_info($info); ?>
</div>

<?php
	} //end successfully found
?>
