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
	<div id="queue_markers">
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
<div id="queue_markers">
	<h2>In-Queue Markers - Details</h2>
<?php
	
	$failed = False;
	$info = array();
	$id = -1;
	$gid = -1;
	
	if(isset($_REQUEST["gid"]))
	{
		$gid = $_REQUEST['gid'];
		$id = db_get_smallest_conflict_gid($link, intval($_REQUEST["gid"]));
		$marker_info = db_get_marker_info($link, $_REQUEST['gid']);
		
		$info = db_get_instance_info($link, $id);
		
		if($info["success"] == False || $marker_info["success"] == False)
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
			Unable to find given marker or it's best instance!
		</div>
<?php
	} else {
?>
<div class="marker_info">

<h3>
<input type="text" maxlength="64" value="<?php echo $info['short_name']; ?>" onChange="marker_change_name(this, '<?php echo $info['gid']; ?>')"/>
<br />
<?php echo "({$info['gid']})"; ?></h3>

<table class="info">

	<tr>
		<td>Last modified:</td>
		<td><?php echo $marker_info['last_modified'] ?></td>
	</tr>
	<tr>
		<td>Last assigned:</td>
		<td><?php echo $marker_info['last_assigned'] ?></td>
	</tr>

	
	<tr>
		<td>Enabled:</td>
		<td><?php echo "<input type=\"checkbox\" name=\"state_{$marker_info['gid']}\"".($marker_info['state'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_state({$marker_info['gid']})\" />" ?>
		</td>
	</tr>
	
	<tr>
		<td>Testing:</td>
		<td><?php echo "<input type=\"checkbox\" name=\"testing_{$marker_info['gid']}\"".($marker_info['testing'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_testing({$marker_info['gid']})\" />"; ?></td>
	</tr>
	
	<tr>
		<td>Force continue:</td>
		<td><?php echo "<input type=\"checkbox\" name=\"force_continue_{$marker_info['gid']}\"".($marker_info['force_continue'] != 0 ? " checked=\"true\"": "")." onclick=\"toggle_gid_force_continue({$marker_info['gid']}, false)\" />"; ?></td>
	</tr>

	<tr>
		<td>Live stuff:</td>
		<td>
<?php
		echo "  <a href=\"get_live_svg.php?gid={$gid}&ready=0\">livesvg</a>\n";
		echo "  <a href=\"get_live_svg_cost.php?gid={$gid}&ready=0\">livecost</a>\n";
?>
		</td>
	</tr>
</table>

<hr />

<table class="info">
	 <tr>
	   <td><b>UPDATE</b></td>
	   <td><div class="note">click the input field for info</div><input type="hidden" name="update_gid" id="update_gid" value="<?php echo $info['gid'] ?>"/></td>
	 </tr>
	 <tr>
	   <td>New name:</td>
	   <td><input type="text" name="update_name" id="update_name" value="<?php echo $info['short_name'] ?>_new"/></td>
	 </tr>
	 <tr>
	   <td>Cost for neighbors:</td>
	   <td>
		   <input type="text" name="update_c_neighbors" id="update_c_neighbors" value="<?php echo $info['cost_neighbors'] ?>" onfocus="show_note('cost-neigh')" onblur="hide_note('cost-neigh')"/>
			<div class="note" id="note-cost-neigh" style="display: none; padding-top: 5px;">
				CL<sup>1</sup>,CR2<sup>1</sup>:T<sup>1</sup>;CL<sup>2</sup>,CR<sup>2</sup>:T<sup>2</sup>;...;CL<sup>max</sup>,CR2<sup>max</sup>:T<sup>max</sup><br/>
				CL<sup>n</sup> is the left cost, CR<sup>n</sup> is the right cost<br/>
				T<sup>n</sup> is the end of the interval, T<sup>1</sup> &ge; 0, T<sup>max</sup> &le; 255,<br/>
				cost for gradient <i>x<i> will be, if T<sup>n-1</sup> &lt; <i>x<i> &le; T<sup>n</sup> then:<br/>
				(CR<sup>n</sup>-CL<sup>n</sup>)(<i>x</i>-T<sup>n-1</sup>)/(T<sup>n</sup>-T<sup>n-1</sup>) + CL<sup>n</sup><br/>
				if <i>x<i> &gt; T<sup>max</sup>, then the cost is 0
			</div>
		</td>
	 </tr>
	 <tr">
	   <td>Cost for similarity:</td>
	   <td>
		   <input type="text" name="update_c_similarity" id="update_c_similarity" value="<?php echo $info['cost_similarity'] ?>" onfocus="show_note('cost-sim')" onblur="hide_note('cost-sim')"/>
			<div class="note" id="note-cost-sim" style="display: none; padding-top: 5px;">
				This value is used to multiply the difference in similarity with the image<br/>
				The difference is a value from 0 to 255 for a module
			</div>
		</td>
	 </tr>
	 <tr>
	   <td>Image matrix:</td>
	   <td>
		   <input type="text" name="update_img_conv" id="update_img_conv" value="<?php echo $info['img_conv'] ?>" onfocus="show_note('img-conv')" onblur="hide_note('img-conv')"/>
			<div class="note" id="note-img-conv" style="display: none; padding-top: 5px;">
				V<sup>1</sup>;V<sup>2</sup>;V<sup>3</sup>;...;V<sup>(2k+1)^2</sup><br/>
				This convolution matrix is applied on the marker before comparison with the image<br/>
				Always an odd sided matrix is used, missing values are replaced with zeros.
			</div>
		</td>
	 </tr>
	 <tr>
	   <td>Marker field count:</td>
	   <td><input type="text" name="update_count" id="update_count" value="5"/></td>
	 </tr>
	 <tr>
	   <td>Keep this GID, too:</td>
	   <td><input type="checkbox" name="update_keep" id="update_keep" value="yes" checked="checked"/></td>
	 </tr>
	 <tr>
	   <td></td>
	   <td><input type="button" id="update_img_conv" value="Add new MF with updated properties!" onclick="marker_update_properties()"/></td>
	 </tr>
</table>

<hr />
<h4>Best field:</h4>
	<?php show_info($info); ?>
</div>

<?php
	} //end successfully found
?>
