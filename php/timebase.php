<?php
require_once("_basic_tasks.php");
$url_this_page = "timebase.php";

if(isset($_GET['file'])) $file = $_GET['file'];
else $file = '';
if($file == '') die();

$url_this_page .= "?file=".$file;
$table = explode('/',$file);
$filename = $table[count($table) - 1];
$dir = str_replace($filename,'',$file);
$here = str_replace($root,'',$dir);
require_once("_header.php");
echo "<p>Current directory = ".$here."</p>";
echo link_to_help();

echo "<h3>Time base file “".$filename."”</h3>";

if(isset($_POST['savethisfile'])) {
	echo "<p style=\"color:red;\">Saved file…</p>";
	$content = $_POST['thistext'];
	$handle = fopen($file,"w");
	$file_header = $top_header."\n// Time base file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	fwrite($handle,$content);
	fclose($handle);
	}

try_create_new_file($file,$filename);
$content = @file_get_contents($file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
$pick_up_headers = pick_up_headers($content);
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."</p>";
$content = $pick_up_headers['content'];
echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisfile\" value=\"SAVE ‘".$filename."’\"></p>";
echo "<textarea name=\"thistext\" rows=\"40\" style=\"width:700px; background-color:Cornsilk;\">".$content."</textarea>";
echo "</form>";
?>