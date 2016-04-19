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
	<div id="ready">
		<h2>Queue</h2>
		<div class="error">
			Please log in to view details!
		</div>
	</div>
<?php
	exit(0);
}


$link = db_connect();

?>

<!-- *************************************QUEUE********************************* -->
<div id="ready">
	<h2>Ready - Details</h2>
<?php
	
	$failed = False;
	$info = array();
	$id = -1;
	$gid = -1;
	
	if(isset($_REQUEST["id"]))
	{
		$id = $_REQUEST['id'];
		$info = db_get_ready_info($link, $id);
		
		if($info["success"] == False )
		{
			$failed = True;
		}
	
	} else {
		$failed = True;
	}



	db_close($link);
	

	if($failed)
	{
?>
		<div class="error">
			Unable to find given solved marker!
		</div>
<?php
	} else {
?>
<div class="marker_info">

<h3>
<input type="text" maxlength="64" value="<?php echo $info['short_name']; ?>" onChange="ready_update_name(this, '<?php echo $info['id']; ?>')"/>
<br />
<?php echo "({$info['gid']}, {$info['id']})"; ?></h3>

	<?php show_info($info, 1); ?>
</div>

<?php
	} //end successfully found
?>
