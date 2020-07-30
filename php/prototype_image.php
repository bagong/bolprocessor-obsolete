<?php
header("Content-type: image/png");
// require_once("_basic_tasks.php");

if(isset($_GET['Duration'])) {
	$Duration = $_GET['Duration'];
	}
	
if(isset($_GET['object_name'])) $object_name = $_GET['object_name'];
else $object_name = "some name";

// $text = clean_up_encoding(TRUE,$text);

$im = @imagecreatetruecolor(800,600)
      or die('Cannot Initialize new GD image stream');
$red = imagecolorallocate($im, 233, 14, 91);
$white = imagecolorallocate($im, 255, 255, 255);
$orange = imagecolorallocate($im, 220, 210, 60);
$black = imagecolorallocate($im, 0, 0,0);


imagefilledrectangle($im,0,0,800,600,$white);

imagestring($im,10,60,10,"This will be a display of the sound-object",$red);

$text = "This sound-object \"".$object_name."\" has duration ".$Duration." ms";
imagestring($im,10,50,50,$text,$black);

imagestring($im,10,50,100,"So, GD library is working! :-)",$black);

imagefilledrectangle($im, 17, 15, 35, 60, $orange);
imagefilledrectangle($im, 4, 4, 50, 25, $red);

// imagepng($im,"pict/test.png");
imagepng($im);
imagedestroy($im);
?>