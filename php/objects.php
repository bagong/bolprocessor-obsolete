<?php
require_once("_basic_tasks.php");
$url_this_page = "objects.php";

if(isset($_GET['file'])) $file = $_GET['file'];
else $file = '';
if($file == '') die();

$url_this_page .= "?file=".$file;
$table = explode('/',$file);
$filename = $table[count($table) - 1];
$dir = str_replace($filename,'',$file);
$here = str_replace($root,'',$dir);
// $page_title = 
require_once("_header.php");
echo "<p>Current directory = ".$here;
echo "<span id='message1' style=\"margin-bottom:1em;\"></span>";
echo "</p>";
echo link_to_help();

echo "<h2>Object prototypes file “".$filename."”</h2>";


/* $iObj = -1;
foreach($_POST as $key => $value) {
	if(is_integer(strpos($key,"display_"))) {
		$iObj = str_replace("display_",'',$key);
		break;
		}
	} */

$temp_folder = $dir.str_replace(' ','_',$filename)."_".session_id()."_temp";
// echo $temp_folder;
if(!file_exists($temp_folder)) {
	echo "<p id=\"timespan\" style=\"color:red;\">Created folder: ".$temp_folder."</p>";
	$cmd = "mkdir ".$temp_folder;
	exec($cmd);
	}
	
if(isset($_POST['savethisfile'])) {
	echo "<p id=\"timespan\"><font color=\"red\">Saved file:</font> <font color=\"blue\">";
//	$handle = fopen($dir."-mi_my_test.bpmi","w");
	SaveObjectPrototypes(TRUE,$dir,$filename,$temp_folder);
	}

try_create_new_file($file,$filename);
$content = @file_get_contents($file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
$objects_file = $csound_file = $alphabet_file = $settings_file = $orchestra_file = $interaction_file = $midisetup_file = $timebase_file = $keyboard_file = $glossary_file = '';
$pick_up_headers = pick_up_headers($content);
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."</p>";
$content = $pick_up_headers['content'];
$csound_file = $pick_up_headers['csound'];

$comment_on_file = '';
echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<input type=\"hidden\" name=\"dir\" value=\"".$dir."\">";
echo "<input type=\"hidden\" name=\"filename\" value=\"".$filename."\">";
echo "<input type=\"hidden\" name=\"temp_folder\" value=\"".$temp_folder."\">";
$table = explode(chr(10),$content);
$iobj = -1;
$handle_object = FALSE;
for($i = 0; $i < count($table); $i++) {
	$line = $table[$i];
	if($i == 0) {
		$PrototypeTickKey = $line;
		echo "PrototypeTickKey = <input type=\"text\" name=\"PrototypeTickKey\" size=\"4\" value=\"".$PrototypeTickKey."\"><br />";
		}
	if($i == 1) {
		$PrototypeTickChannel = $line;
		echo "PrototypeTickChannel = <input type=\"text\" name=\"PrototypeTickChannel\" size=\"4\" value=\"".$PrototypeTickChannel."\"><br />";
		}
	if($i == 2) {
		$PrototypeTickVelocity = $line;
		echo "PrototypeTickVelocity = <input type=\"text\" name=\"PrototypeTickVelocity\" size=\"4\" value=\"".$PrototypeTickVelocity."\"><br />";
		}
	if($i == 3) {
		$CsoundInstruments_filename = $line;
		echo "CsoundInstruments filename = <input type=\"text\" name=\"CsoundInstruments_filename\" size=\"20\" value=\"".$CsoundInstruments_filename."\"><br />";
		}
	if($i == 4) {
		$maxsounds = $line;
		echo "<input type=\"hidden\" name=\"maxsounds\" value=\"".$maxsounds."\">";
		}
	if($line == "TABLE:") break;
	if($line == "DATA:") {
		$comment_on_file = $table[$i+1];
		$comment_on_file = str_replace("<HTML>",'',$comment_on_file);
		$comment_on_file = str_replace("</HTML>",'',$comment_on_file);
		echo "Comment on this file = <input type=\"text\" name=\"comment_on_file\" size=\"80\" value=\"".$comment_on_file."\"><br />";
		break;
		}
	if(!is_integer($pos=strpos($line,"<HTML>"))) continue;
	else {
		$iobj++;
		$clean_line = str_replace("<HTML>",'',$line);
		$clean_line = str_replace("</HTML>",'',$clean_line);
		$object_name[$iobj] = trim($clean_line);
		
		$object_file[$iobj] = $temp_folder."/".$object_name[$iobj].".txt";
		$object_foldername = clean_folder_name($object_name[$iobj]);
		$save_codes_dir = $temp_folder."/".$object_foldername."_codes";
		if(!is_dir($save_codes_dir)) mkdir($save_codes_dir);

		$handle_object = fopen($object_file[$iobj],"w");
		$midi_bytes = $save_codes_dir."/midibytes.txt";
		$handle_bytes = fopen($midi_bytes,"w");
		
		$file_header = $top_header."\n// Object prototype saved as \"".$object_name[$iobj]."\". Date: ".gmdate('Y-m-d H:i:s');
		$file_header .= "\n".$filename;
		fwrite($handle_object,$file_header."\n");
		echo "<input type=\"hidden\" name=\"object_name_".$iobj."\" value=\"".$object_name[$iobj]."\">";
		$j = $i_start_midi = $n = 0; $first = TRUE; 
		do {
			$i++; $line = $table[$i];
			if(is_integer($pos=strpos($line,"_beginCsoundScore_"))) {
				fwrite($handle_object,$line."\n");
				$i++; $line = $table[$i];
				if(is_integer($pos=strpos($line,"_endCsoundScore_"))) {
					// CsoundScore is empty; create 1 empty line
					$score = "<HTML></HTML>";
					fwrite($handle_object,$score."\n");
					}
				else while(!is_integer($pos=strpos($line,"_endCsoundScore_"))) {
					fwrite($handle_object,$line."\n");
					$i++; $line = $table[$i];
					}
				$i_start_midi = $i;
				}
			// We send MIDI codes to separate file"midibytes.txt"
			$number_codes = FALSE;
			if($i_start_midi > 0 AND $i > $i_start_midi AND !is_integer(strpos($line,"<HTML>"))) {
				if($first) {
					$nmax = intval($line);
					$first = FALSE;
				//	echo $object_name[$iobj]." nmax = ".$nmax."<br />";
					$number_codes = TRUE;
					}
				if($n <= $nmax) fwrite($handle_bytes,$line."\n");
				$n++;
				}
			else if(!$number_codes) fwrite($handle_object,$line."\n");
			if(is_integer($pos=strpos($line,"<HTML>"))) break;
	/*		if($iobj <> $iObj)
				echo "<input type=\"hidden\" name=\"object_param_".$j."_".$iobj."\" value=\"".$line."\">"; */
			$j++;
			continue;
			}
		while(TRUE);
		$clean_line = str_replace("<HTML>",'',$line);
		$clean_line = str_replace("</HTML>",'',$clean_line);
		$object_comment[$iobj] = $clean_line;
		fclose($handle_bytes);
	/*	if($iobj <> $iObj)
			echo "<input type=\"hidden\" name=\"object_comment_".$iobj."\" value=\"".$object_comment[$iobj]."\">"; */
		}
	}
fclose($handle_object);
echo "<p style=\"color:blue;\">".$comment_on_file."</p>";
echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisfile\" value=\"SAVE ‘".$filename."’ INCLUDING ALL CHANGES TO PROTOTYPES\"><br />";
echo "➡ <i>This file is autosaved every 20 seconds. Still, it is safe to save it before quitting the editor.</i></p>";
echo "<script type=\"text/javascript\" src=\"autosave.js\"></script>";
echo "</form>";
 echo "<hr>";
 echo "<h3>Click object prototypes below to edit them:</h3>";
echo "<table style=\"background-color:white;\">";
for($i = 0; $i <= $iobj; $i++) {
	echo "<form method=\"post\" action=\"prototype.php\" enctype=\"multipart/form-data\">";
	echo "<tr><td>";
	echo "<input type=\"hidden\" name=\"temp_folder\" value=\"".$temp_folder."\">";
	echo "<input type=\"hidden\" name=\"object_file\" value=\"".$object_file[$i]."\">";
	echo "<input style=\"background-color:azure; font-size:larger;\" type=\"submit\" onclick=\"this.form.target='_blank';return true;\" name=\"object_name\" value=\"".$object_name[$i]."\">";
	echo "</td>";
	echo "<td style=\"vertical-align:middle;\">";
	echo $object_comment[$i];
	echo "</td>";
	echo "</tr>";
	echo "</form>";
	}
echo "</table>";

display_more_buttons($content,$url_this_page,$dir,$objects_file,$csound_file,$alphabet_file,$settings_file,$orchestra_file,$interaction_file,$midisetup_file,$timebase_file,$keyboard_file,$glossary_file);

?>