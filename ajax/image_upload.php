<?php
require_once('../inc/defines.php');
require_once('../inc/common.php');
require_once('../inc/database.php');
require_once('../inc/resize-class.php');


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


$db = db_connect();

$result = array();
$result["success"] = true;

$allowedExts = array("image/gif" => "gif", "image/jpeg" => "jpg", "image/pjpeg" => "jpg", "image/png" => "png");

if (isset($allowedExts[$_FILES["image_upload"]["type"]])) {
	if ($_FILES["image_upload"]["error"] != 0) {
		$result["success"] = false;
		$result["error"] = "code " . $_FILES["image_upload"]["error"];
	} else {
		$new_id = db_image_add($db, $allowedExts[$_FILES["image_upload"]["type"]], $db->real_escape_string($_FILES["image_upload"]["name"]));
		
		$file_path = $_SERVER['DOCUMENT_ROOT'] . getImagePath($new_id, $allowedExts[$_FILES["image_upload"]["type"]]);
		$file_thumb_path = $_SERVER['DOCUMENT_ROOT'] . getImagePath($new_id, $allowedExts[$_FILES["image_upload"]["type"]], true);
		if (move_uploaded_file($_FILES["image_upload"]["tmp_name"], $file_path))
		{
			@chmod($file_path, 0777);
			
			// *** 1) Initialise / load image
			$resizeObj = new resize($file_path);

			// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
			$resizeObj->resizeImage(25, 25, 'auto');

			// *** 3) Save image
			$resizeObj->saveImage($file_thumb_path, 100);
			
			@chmod($file_thumb_path, 0777);
			
			$result["img_id"] = $new_id;
			$result["img_url"] = getImageURL($new_id, $allowedExts[$_FILES["image_upload"]["type"]], true);
			
		} else {
			db_image_delete($db, $new_id);
			$result["success"] = false;
			$result["error"] = "Error while saving the file!" . $file_path;
		}
		
	}
} else
{
	$result["success"] = false;
	$result["error"] = "Not supported file format, please try gif, jpeg or png!";
}

echo json_encode($result);

db_close($db);


?>
