<!-- *************************************SETTINGS********************************* -->
<?php
require_once('../inc/defines.php');
require_once('../inc/database.php');

session_start();

if(!isset($_SESSION['logged_in']))
{
	$_SESSION['logged_in'] = False;
}

?>
<div id="settings">
<h2>Login</h2>

<?php
if(!$_SESSION['logged_in'])
{
?>
<form onsubmit="post_login(); return false;">
<table>
	<tr>
	   <td>Username:</td>
	   <td><input type="text" name="username" id="username" value=""/></td>
	 </tr>
	 <tr>
	   <td>Password:</td>
	   <td><input type="password" name="password" id="password" value=""/></td>
	 </tr>
	<tr>
		<td colspan="2">
			<input type="submit" value="Login"/>
		</td>
	</tr>
</table>
</form>
<br />
</div>

<?php
} else {
?>
	<div><p> You are logged in. </p> <br /> <input type="button" value="Logout" onclick="post_logout()"/> </div>
	
<?php
}
?>

<div id="login_result"></div>
</div>
