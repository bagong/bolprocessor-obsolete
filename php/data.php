<?php
require_once("_basic_tasks.php");


if(isset($_GET['file'])) $file = urldecode($_GET['file']);
else $file = '';
if($file == '') die();
$url_this_page = "data.php?file=".urlencode($file);
$table = explode(SLASH,$file);
$filename = end($table);
$this_file = "..".SLASH.$file;
$dir = str_replace($filename,'',$this_file);


/*
$url_this_page = "data.php";

if(isset($_GET['file'])) $file = urldecode($_GET['file']);
else $file = '';
if($file == '') die();


$url_this_page .= "?file=".urlencode($file);
$table = explode(SLASH,$file);
$filename = $table[count($table) - 1];
$dir = str_replace($filename,'',$file);
$here = str_replace($bp_parent_path.SLASH,'',$dir); */
require_once("_header.php");
echo "<p>Current directory = ".$dir."</p>";
echo link_to_help();

echo "<h3>Data file “".$filename."”</h3>";

if(isset($_POST['savethisfile'])) {
	echo "<p id=\"timespan\" style=\"color:red;\">Saved file…</p>";
	$content = $_POST['thistext'];
	$handle = fopen($this_file,"w");
	$file_header = $top_header."\n// Data file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	fwrite($handle,$content);
	fclose($handle);
	}

try_create_new_file($this_file,$filename);
$content = @file_get_contents($this_file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
$objects_file = $csound_file = $alphabet_file = $settings_file = $orchestra_file = $interaction_file = $midisetup_file = $timebase_file = $keyboard_file = $glossary_file = '';
$pick_up_headers = pick_up_headers($content);
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."</p>";
$content = $pick_up_headers['content'];
$objects_file = $pick_up_headers['objects'];
$csound_file = $pick_up_headers['csound'];
$alphabet_file = $pick_up_headers['alphabet'];
$settings_file = $pick_up_headers['settings'];
$orchestra_file = $pick_up_headers['orchestra'];
$midisetup_file = $pick_up_headers['midisetup'];
$timebase_file = $pick_up_headers['timebase'];
$keyboard_file = $pick_up_headers['keyboard'];
$glossary_file = $pick_up_headers['glossary'];

echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";

echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisfile\" value=\"SAVE ‘".$filename."’\"></p>";
echo "<textarea name=\"thistext\" rows=\"40\" style=\"width:700px; background-color:Cornsilk;\">".$content."</textarea>";
echo "</form>";

display_more_buttons($content,$url_this_page,$dir,$objects_file,$csound_file,$alphabet_file,$settings_file,$orchestra_file,$interaction_file,$midisetup_file,$timebase_file,$keyboard_file,$glossary_file);
?>
