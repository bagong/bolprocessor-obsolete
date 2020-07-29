<?php
require_once("_basic_tasks.php");
require_once("_header.php");
echo "<h2>This is on-line Bol Processor</h2>";
$this_page = "index.php";
if(isset($_GET['path'])) {
	$dir = realpath($_GET['path']);
	$table = explode(DIRECTORY_SEPARATOR,$dir);
	$upper_dir = dirname($dir);
	$link = $this_page."?path=".$upper_dir;
	if($table[count($table)-1]) echo "<h3>[<a href=\"".$link."\">move up</a>]</h3>";
	}
else {
	$dir = $bp_application_dir;
	}

// echo "dir = ".$dir."<br />";

$new_file = '';
if(isset($_POST['create_grammar'])) {
	$filename = trim($_POST['filename']);
	if($filename <> '') {
		if(!is_integer($pos=strpos($filename,"-gr")) OR $pos > 0) {
			$filename = trim(str_replace("-gr",'',$filename));
			$filename = "-gr.".$filename;
			}
		echo "<p style=\"color:red;\">Creating ‘".$filename."’…</p>";
		$new_file = $filename;
		$handle = fopen($dir."/".$filename,"w");
		fclose($handle);
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
		echo "<p style=\"color:red;\">Creating ‘".$filename."’…</p>";
		$new_file = $filename;
		$handle = fopen($dir."/".$filename,"w");
		fclose($handle);
		}
	else unset($_POST['create_alphabet']);
	}

$folder = str_replace($bp_parent_dir.DIRECTORY_SEPARATOR,'',$dir);
echo "<h3>Content of folder <font color=\"red\">".$folder."</font></h3>";
// echo "dir = ".$dir."<br />";
$table = explode('_',$folder);
$extension = end($table);
if(is_integer(strpos($dir,DIRECTORY_SEPARATOR."bolprocessor")) AND $folder <> "bolprocessor".DIRECTORY_SEPARATOR."php" AND $extension <> "temp" AND !isset($_POST['create_grammar']) AND !isset($_POST['create_alphabet'])) {
	echo "<form method=\"post\" action=\"".$this_page."?path=".$dir."\" enctype=\"multipart/form-data\">";
	echo "<p style=\"text-align:left;\">";
	echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"create_grammar\" value=\"CREATE NEW GRAMMAR FILE IN THIS FOLDER\">&nbsp;➡&nbsp;";
	echo "<font color=\"blue\">".$folder.DIRECTORY_SEPARATOR."</font>";
	echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-gr.\"></p>";
	echo "</form>";
	echo "<form method=\"post\" action=\"".$this_page."?path=".$dir."\" enctype=\"multipart/form-data\">";
	echo "<p style=\"text-align:left;\">";
	echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"create_alphabet\" value=\"CREATE NEW ALPHABET FILE IN THIS FOLDER\">&nbsp;➡&nbsp;";
	echo "<font color=\"blue\">".$folder.DIRECTORY_SEPARATOR."</font>";
	echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-ho.\"></p>";
	echo "</form>";
	}

$dircontent = scandir($dir);
$now = time();
$yesterday = $now - (24 * 3600);
foreach($dircontent as $thisfile) {
	if($thisfile == '.' OR $thisfile == ".." OR $thisfile == ".DS_Store") continue;
	$time_saved = filemtime($dir."/".$thisfile);
	if($time_saved < $yesterday) $old = TRUE;
	else $old = FALSE;
	if(is_dir($dir."/".$thisfile)) {
		$table = explode('_',$thisfile);
		$extension = end($table);
		$link = $this_page."?path=".$dir."/".$thisfile;
		if($extension == "temp" AND count($table) > 2) {
			$id = $table[count($table) - 2];
			if($old) {
				if($id <> session_id()) {
					my_rmdir($dir."/".$thisfile);
					continue;
					}
				}
			}
		if($extension <> "temp")
			echo "<b><a href=\"".$link."\">".$thisfile."</a></b><br />";
		continue;
		}
	$table = explode(".",$thisfile);
	$extension = end($table);
	if($old) {
		$table = explode('_',$thisfile);
		$prefix = $table[0];
		if($prefix == "trace") {
			$id = $table[1];
			if(($extension == "txt" OR $extension == "html") AND $id <> session_id()) {
				unlink($dir."/".$thisfile);
				continue;
				}
			}
		}
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
		$link = $type.".php?file=".$dir."/".$thisfile;
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
