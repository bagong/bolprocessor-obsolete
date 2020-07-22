<?php
require_once("_basic_tasks.php");
$current_path = $root;
echo "<p>root = ".$root."</p>";
echo "<h2>This is on-line Bol Processor</h2>";
$this_page = "index.php";
if(isset($_GET['path'])) {
	$dir = $_GET['path'];
	$current_path = '';
//	echo "dir = ".$dir."<br />";
	$table = explode('/',$dir);
	$table[count($table)-1] = '';
	$upper_dir = implode('/',$table);
	$upper_dir = preg_replace("/\/$/u",'',$upper_dir);
//	echo "upperdir = ".$upper_dir."<br />";
	$link = $this_page."?path=".$upper_dir;
	echo "<p>[<a href=\"".$link."\">move up</a>]</p>";
	}
else {
	chdir($root);
	$dir = "bolprocessor";
	}

$new_file = '';
if(isset($_POST['createfile'])) {
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
	else unset($_POST['createfile']);
	}

$folder = str_replace($root,'',$dir);
require_once("_header.php");
echo "<h3>Content of this folder ‘".$folder."’</h3>";

if(!isset($_POST['createfile'])) {
	echo "<form method=\"post\" action=\"".$this_page."?path=".$dir."\" enctype=\"multipart/form-data\">";
	echo "<p style=\"text-align:left;\">";
	echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"createfile\" value=\"CREATE NEW GRAMMAR IN THIS FOLDER\">&nbsp;➡&nbsp;";
	echo "<font color=\"blue\">".$folder."/</font>";
	echo "<input type=\"text\" name=\"filename\" size=\"20\" style=\"background-color:CornSilk;\" value=\"-gr.\"></p>";
	echo "</form>";
	}
	
$dircontent = scandir($dir);
foreach($dircontent as $thisfile) {
	if($thisfile == '.' OR $thisfile == ".." OR $thisfile == ".DS_Store") continue;
	if(is_dir($dir."/".$thisfile)) {
		$link = $this_page."?path=".$current_path.$dir."/".$thisfile;
		echo "<b><a href=\"".$link."\">".$thisfile."</a></b><br />";
		continue;
		}
	$table = explode(".",$thisfile);
	$extension = end($table);
//	if($extension <> "bpgr" AND (!is_integer(strpos($thisfile,'-')) OR ($pos=strpos($thisfile,'-')) > 0)) continue;
	$prefix = substr($thisfile,1,2);
	switch($prefix) {
		case 'gr':
			$type = "grammar"; break;
		case 'da':
			$type = "data"; break;
		case 'ho':
			$type = "alphabet"; break;
		case 'se':
			$type = "settings"; break;
		case 'cs':
			$type = "csound"; break;
		case 'mi':
			$type = "objects"; break;
		case 'or':
			$type = "orchestra"; break;
		case 'in':
			$type = "interaction"; break;
		case 'md':
			$type = "midisetup"; break;
		case 'tb':
			$type = "timebase"; break;
		case 'kb':
			$type = "keyboard"; break;
		case 'gl':
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
	//	if($type == "grammar") echo "⚪️ ";
		echo $thisfile."</a> ";
		if($type == "grammar") echo "<font color=\"red\">";
		else if($type == "data") echo "<font color=\"gold\">";
		else if($type <> "settings") echo "<font color=\"lightgreen\">";
		echo $type."</font><br />";
		}
	else echo $thisfile."<br />";
	}
?>