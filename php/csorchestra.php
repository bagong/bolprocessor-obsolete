<?php
require_once("_basic_tasks.php");

if(isset($_GET['file'])) $file = urldecode($_GET['file']);
else $file = '';
if($file == '') die();
$url_this_page = "csorchestra.php?file=".urlencode($file);
$table = explode(SLASH,$file);
$filename = end($table);
// $this_file = "..".SLASH.$file;
$dir = str_replace($filename,'',$file);

require_once("_header.php");
echo "<p>Current directory = ".$dir."</p>";
echo link_to_help();
	
echo "<h2>Csound orchestra file <big>“<font color=\"green\">".$filename."</font>”</big></h2>";

if(isset($_POST['savethisfile'])) {
	echo "<p id=\"timespan\" style=\"color:red;\">Saved file…</p>";
	$content = $_POST['thistext'];
	$handle = fopen($file,"w");
	fwrite($handle,$content);
	fclose($handle);
	}

try_create_new_file($file,$filename);
$content = @file_get_contents($file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisfile\" value=\"SAVE ‘".$filename."’\"></p>";
echo "<textarea name=\"thistext\" rows=\"40\" style=\"width:700px; background-color:Cornsilk;\">".$content."</textarea>";
echo "</form>";
?>