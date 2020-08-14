<?php
require_once("_basic_tasks.php");
require_once("_header.php");
echo "<h2>This is on-line Bol Processor</h2>";
$url_this_page = $this_page = "index.php";

if($path <> '') {
	$url_this_page .= "?path=".urlencode($path);
	$dir = realpath($bp_application_path.SLASH.urldecode($path));
	$table = explode(SLASH,$path);
	if(($n=count($table)) > 1) {
		$upper_dir = $table[$n - 2];
		}
	else $upper_dir = '';
	if($test) echo "upper_dir = ".$upper_dir."<br />";
	if($upper_dir == '') $link = $this_page;
	else $link = $this_page."?path=".urlencode($upper_dir);
	if($test) echo "link = ".$link."<br />";
	echo "<h3>[<a href=\"".$link."\">move up</a>]</h3>";
	}
else $dir = $bp_application_path;

if($test) echo "dir = ".$dir."<br />";
if($test) echo "url_this_page = ".$url_this_page."<br />";

$new_file = '';
if(isset($_POST['create_grammar'])) {
	$filename = trim($_POST['filename']);
	if($test) echo "filename = ".$filename."<br />";
	if($filename <> '') {
		if(!is_integer($pos=strpos($filename,"-gr")) OR $pos > 0) {
			$filename = trim(str_replace("-gr",'',$filename));
			$filename = "-gr.".$filename;
			}
		$new_file = $filename;
		if($test) echo "newfile = ".$new_file."<br />";
		if(file_exists($dir.SLASH.$filename)) {
			echo "<p><font color=\"red\">This file already exists:</font> <font color=\"red\">".$filename."</font></p>";
			unset($_POST['create_grammar']);
			}
		else {
			echo "<p style=\"color:red;\" id=\"timespan\">Creating ‘".$filename."’…</p>";
			$handle = fopen($dir.SLASH.$filename,"w");
			fclose($handle);
			}
		}
	else unset($_POST['create_grammar']);
	}
if(isset($_POST['create_alphabet'])) {
	$filename = trim($_POST['filename']);
	if($filename <> '') {
		if(!is_integer($pos=strpos($filename,"-ho")) OR $pos > 0) {
			$filename = trim(str_replace("-ho",'',$filename));
			$filename = "-ho.".$filename;
			}
		$new_file = $filename;
		if(file_exists($dir.SLASH.$filename)) {
			echo "<p><font color=\"red\">This file already exists:</font> <font color=\"red\">".$filename."</font></p>";
			unset($_POST['create_alphabet']);
			}
		else {
			echo "<p style=\"color:red;\" id=\"timespan\">Creating ‘".$filename."’…</p>";
			$handle = fopen($dir.SLASH.$filename,"w");
			fclose($handle);
			}
		}
	else unset($_POST['create_alphabet']);
	}
if(isset($_POST['create_timebase'])) {
	$filename = trim($_POST['filename']);
	if($filename <> '') {
		if(!is_integer($pos=strpos($filename,"-tb")) OR $pos > 0) {
			$filename = trim(str_replace("-tb",'',$filename));
			$filename = "-tb.".$filename;
			}
		$new_file = $filename;
		if(file_exists($dir.SLASH.$filename)) {
			echo "<p><font color=\"red\">This file already exists:</font> <font color=\"red\">".$filename."</font></p>";
			unset($_POST['create_timebase']);
			}
		else {
			echo "<p style=\"color:red;\" id=\"timespan\">Creating ‘".$filename."’…</p>";
			$handle = fopen($dir.SLASH.$filename,"w");
			$template = $bp_php_path."/timebase_template";
			$template_content = @file_get_contents($template,TRUE);
			fwrite($handle,$template_content."\n");
			fclose($handle);
			}
		}
	else unset($_POST['create_timebase']);
	}

$folder = str_replace($bp_parent_path.SLASH,'',$dir);
echo "<h3>Content of folder <font color=\"red\">".$folder."</font></h3>";
// echo "dir = ".$dir."<br />";
$table = explode('_',$folder);
$extension = end($table);
if(is_integer(strpos($dir,SLASH.$bp_home_dir)) AND $folder <> $bp_home_dir.SLASH."php" AND $extension <> "temp") {
	if(!isset($_POST['create_grammar'])) {
		echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<p style=\"text-align:left;\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"create_grammar\" value=\"CREATE NEW GRAMMAR FILE IN THIS FOLDER\">&nbsp;➡&nbsp;";
		echo "<font color=\"blue\">".$folder.SLASH."</font>";
		echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-gr.\"></p>";
		echo "</form>";
		}
	if(!isset($_POST['create_alphabet'])) {
		echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<p style=\"text-align:left;\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"create_alphabet\" value=\"CREATE NEW ALPHABET FILE IN THIS FOLDER\">&nbsp;➡&nbsp;";
		echo "<font color=\"blue\">".$folder.SLASH."</font>";
		echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-ho.\"></p>";
		echo "</form>";
		}
	if(!isset($_POST['create_timebase'])) {
		echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<p style=\"text-align:left;\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"create_timebase\" value=\"CREATE NEW TIMEBASE IN THIS FOLDER\">&nbsp;➡&nbsp;";
		echo "<font color=\"blue\">".$folder.SLASH."</font>";
		echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-tb.\"></p>";
		echo "</form>";
		}
	}

$dircontent = scandir($dir);
//$now = time();
//$yesterday = $now - (24 * 3600);
foreach($dircontent as $thisfile) {
	if($thisfile == '.' OR $thisfile == ".." OR $thisfile == ".DS_Store") continue;
//	$time_saved = filemtime($dir.SLASH.$thisfile);
//	if($time_saved < $yesterday) $old = TRUE;
//	else $old = FALSE;
	if(is_dir($dir.SLASH.$thisfile)) {
		$table = explode('_',$thisfile);
		$extension = end($table);
		if($path == '') $link = $this_page."?path=".urlencode($thisfile);
		else $link = $this_page."?path=".urlencode($path.SLASH.$thisfile);
/*		if($extension == "temp" AND count($table) > 2) {
			$id = $table[count($table) - 2];
			if($old) {
				if($id <> session_id()) {
					my_rmdir($dir.SLASH.$thisfile);
					continue;
					}
				}
			} */
		if($extension <> "temp")
			echo "<b><a href=\"".$link."\">".$thisfile."</a></b><br />";
		continue;
		}
	$table = explode(".",$thisfile);
	$extension = end($table);
/*	if($old) {
		$table = explode('_',$thisfile);
		$prefix = $table[0];
		if($prefix == "trace") {
			$id = $table[1];
			if(($extension == "txt" OR $extension == "html") AND $id <> session_id()) {
				unlink($dir.SLASH.$thisfile);
				continue;
				}
			}
		} */
	$table = explode("_",$thisfile);
	$prefix = $table[0];
	if($prefix == "trace") continue;
	$prefix = substr($thisfile,0,3);
	switch($prefix) {
		case '-gr':
			$type = "grammar"; break;
		case '-da':
			$type = "data"; break;
		case '-ho':
			$type = "alphabet"; break;
		case '-se':
			$type = "settings"; break;
		case '-cs':
			$type = "csound"; break;
		case '-mi':
			$type = "objects"; break;
		case '-or':
			$type = "orchestra"; break;
		case '-in':
			$type = "interaction"; break;
		case '-md':
			$type = "midisetup"; break;
		case '-tb':
			$type = "timebase"; break;
		case '-kb':
			$type = "keyboard"; break;
		case '-gl':
			$type = "glossary"; break;
		default:
			$type = ''; break;
		}
	switch($extension) {
		case "bpgr": $type = "grammar"; break;
		case "bpda": $type = "data"; break;
		case "bpho": $type = "alphabet"; break;
		case "bpse": $type = "settings"; break;
		case "bpcs": $type = "csound"; break;
		case "bpmi": $type = "objects"; break;
		case "bpor": $type = "orchestra"; break;
		case "bpin": $type = "interaction"; break;
		case "bpmd": $type = "midisetup"; break;
		case "bptb": $type = "timebase"; break;
		case "bpkb": $type = "keyboard"; break;
		case "bpgl": $type = "glossary"; break;
		}
	if($type <> '') {
		$link = $type.".php?file=".urlencode($path.SLASH.$thisfile);
		if($new_file == $thisfile) echo "<font color=\"red\">➡</font> ";
		echo "<a target=\"_blank\" href=\"".$link."\">";
		echo $thisfile."</a> ";
		if($type == "grammar") echo "<font color=\"red\">";
		else if($type == "data") echo "<font color=\"gold\">";
		else if($type <> "settings") echo "<font color=\"lightgreen\">";
		echo $type."</font><br />";
		}
	else echo $thisfile."<br />";
	}
?>
