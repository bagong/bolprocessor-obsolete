<?php
session_start();
require('midi.class.php');
// Source: https://github.com/robbie-cao/midi-class-php

$root = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;

// take bottom-up approach
$bp_php_path = getcwd();
$bp_application_path = dirname($bp_php_path);
$bp_parent_path = dirname($bp_application_path);

$bp_home_dir = str_replace($bp_parent_path.DIRECTORY_SEPARATOR,'',$bp_application_path);
$part_dir = str_replace($bp_parent_path,'',$bp_php_path);
$path_above = str_replace($root,'',$bp_php_path);
$path_above = str_replace($part_dir,'',$path_above);

$test = FALSE;
if($test) {
	// Beware that test mode will disrupt the display of images
	echo "root = ".$root."<br />";
	echo "bp_php_path = ".$bp_php_path."<br />";
	echo "bp_application_path = ".$bp_application_path."<br />";
	echo "bp_parent_path = ".$bp_parent_path."<br />";
	echo "bp_home_dir = ".$bp_home_dir."<br />";
	echo "part_dir = ".$part_dir."<br />";
	echo "path_above = ".$path_above."<br />";
	}
	
// previous $root and $path_to_root must be replaced
// $root.$path_to_bp is $bp_parent_path
// $root.$path_to_bp."/"."bolprocessor" is $bp_application_path
// $root.$path_to_bp."/"."bolprocessor/php" is $bp_php_path

$text_help_file = $bp_application_path.DIRECTORY_SEPARATOR."BP2_help.txt";
$html_help_file = "BP2_help.html";
$help = compile_help($text_help_file,$html_help_file);
$tracefile = "trace_".session_id().".txt";
$top_header = "// Bol Processor BP3 compatible with version BP2.9.8";

function pick_up_headers($content) {
	$content = trim($content);
	$content = str_replace(chr(13).chr(10),chr(10),$content);
	$content = str_replace(chr(13),chr(10),$content);
	$content = str_replace(chr(9),' ',$content); // Remove tabulations
	$content = clean_up_encoding(TRUE,$content);
	do $content = str_replace(chr(10).chr(10).chr(10),chr(10).chr(10),$content,$count);
	while($count > 0);
	$table = explode(chr(10),$content);
	$table_out = $pick_up_headers = array();
	$start = TRUE;
	$pick_up_headers['metronome'] = $pick_up_headers['time_structure'] = $pick_up_headers['headers'] = $pick_up_headers['alphabet'] = $pick_up_headers['objects'] = $pick_up_headers['csound'] = $pick_up_headers['settings'] = $pick_up_headers['data'] = $pick_up_headers['orchestra'] = $pick_up_headers['timebase'] = $pick_up_headers['interaction'] = $pick_up_headers['midisetup'] = $pick_up_headers['timebase'] = $pick_up_headers['keyboard'] = $pick_up_headers['glossary'] = '';
	for($i = 0; $i < count($table); $i++) {
		$line = trim($table[$i]);
		$line = preg_replace("/\s/u",' ',$line);
	//	echo $i." “".$table[$i]."”<br />";
		if($i == 0)
			$line = preg_replace("/.*(\/\/.*)/u","$1",$line);
		if($start AND is_integer($pos=strpos($line,"//")) AND $pos == 0) {
			if($i > 1) $table_out[] = $line;
			else {
				if($pick_up_headers['headers'] <> '')
					$pick_up_headers['headers'] .= "<br />";
				$pick_up_headers['headers'] .= $line;
				}
			continue;
			}
		$table_out[] = $line;
		if(is_integer($pos=strpos($line,"_mm")) AND $pos == 0) {
			$metronome = preg_replace("/.+\((.+)\).+/u","$1",$line);
			$pick_up_headers['metronome'] = $metronome;
			$time_structure = preg_replace("/.+\)\s+_(.+)$/u","$1",$line);
			if($time_structure == "striated" OR $time_structure == "smooth")
				$pick_up_headers['time_structure'] = $time_structure;
			}
		if(is_integer($pos=strpos($line,"-ho")) AND $pos == 0)
			$pick_up_headers['alphabet'] = fix_file_name($line,"ho");
		else if(is_integer($pos=strpos($line,"-mi")) AND $pos == 0)
			$pick_up_headers['objects'] = fix_file_name($line,"mi");
		else if(is_integer($pos=strpos($line,"-cs")) AND $pos == 0)
			$pick_up_headers['csound'] = fix_file_name($line,"cs");
		else if(is_integer($pos=strpos($line,"-se")) AND $pos == 0)
			$pick_up_headers['settings'] = fix_file_name($line,"se");
		else if(is_integer($pos=strpos($line,"-da")) AND $pos == 0)
			$pick_up_headers['data'] = fix_file_name($line,"da");
		else if(is_integer($pos=strpos($line,"-or")) AND $pos == 0)
			$pick_up_headers['orchestra'] = fix_file_name($line,"or");
		else if(is_integer($pos=strpos($line,"-tb")) AND $pos == 0)
			$pick_up_headers['timebase'] = fix_file_name($line,"tb");
		else if(is_integer($pos=strpos($line,"-in")) AND $pos == 0)
			$pick_up_headers['interaction'] = fix_file_name($line,"in");
		else if(is_integer($pos=strpos($line,"-md")) AND $pos == 0)
			$pick_up_headers['midisetup'] = fix_file_name($line,"md");
		else if(is_integer($pos=strpos($line,"-tb")) AND $pos == 0)
			$pick_up_headers['timebase'] = fix_file_name($line,"tb");
		else if(is_integer($pos=strpos($line,"-kb")) AND $pos == 0)
			$pick_up_headers['keyboard'] = fix_file_name($line,"kb");
		else if(is_integer($pos=strpos($line,"-gl")) AND $pos == 0)
			$pick_up_headers['glossary'] = fix_file_name($line,"gl");
		else if($line <> '') $start = FALSE;
		}
	$pick_up_headers['content'] = implode(chr(10),$table_out);
	return $pick_up_headers;
	}

function fix_file_name($line,$type) {
	// Detect for instance: "-se.:somefile.bpse"
	$goodline = $line;
	if(is_integer($pos=strpos($line,":")) AND $pos == 4) {
		$line = substr($line,5,strlen($line) - 5);
		$extension = "bp".$type;
		$goodline = str_replace(".".$extension,'',$line);
		$goodline = str_replace(".","_",$goodline);
		$goodline .= ".".$extension;
		if($goodline <> $line)
			echo "<p style=\"color:red;\">ERROR: incorrect file name ‘-".$type.".:".$line."’, it should be ‘-".$type.".:".$goodline."’</p>";
		}
	return $goodline;
	}

function display_more_buttons($content,$url_this_page,$dir,$objects_file,$csound_file,$alphabet_file,$settings_file,$orchestra_file,$interaction_file,$midisetup_file,$timebase_file,$keyboard_file,$glossary_file) {
	global $output_file, $file_format;
	$page_type = str_replace(".php",'',$url_this_page);
	$page_type = preg_replace("/\.php.*/u",'',$url_this_page);
//	echo $page_type;
	if($page_type == "grammar" OR $page_type == "alphabet" OR $page_type == "glossary" OR $page_type == "interaction") {
		if(isset($_POST['show_help_entries'])) {
			$entries = display_help_entries($content);
			echo $entries."<br />";
			}
		else {
			echo "<div style=\"float:right; width:600px;\">";
			echo "<form method=\"post\" action=\"".$url_this_page."#help_entries\" enctype=\"multipart/form-data\">";
			echo "<input type=\"hidden\" name=\"output_file\" value=\"".$output_file."\">";
			echo "<input type=\"hidden\" name=\"file_format\" value=\"".$file_format."\">";
			echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"show_help_entries\" value=\"SHOW HELP ENTRIES\">";
			echo "</form></div>";
			}
		}
	echo "<table style=\"padding:0px; background-color:white; border-spacing: 2px;\" cellpadding=\"0px;\"><tr>";
	if($alphabet_file <> '') {
		$url_this_page = "alphabet.php?file=".$dir.$alphabet_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"openobjects\" onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$alphabet_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($objects_file <> '') {
		$url_this_page = "objects.php?file=".$dir.$objects_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$objects_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($csound_file <> '') {
		$url_this_page = "csound.php?file=".$dir.$csound_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"opencsound\" onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$csound_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($settings_file <> '') {
		$url_this_page = "settings.php?file=".$dir.$settings_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$settings_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($orchestra_file <> '') {
		$url_this_page = "orchestra.php?file=".$dir.$orchestra_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$orchestra_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($interaction_file <> '') {
		$url_this_page = "interaction.php?file=".$dir.$interaction_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$interaction_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($midisetup_file <> '') {
		$url_this_page = "midisetup.php?file=".$dir.$midisetup_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$midisetup_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($timebase_file <> '') {
		$url_this_page = "timebase.php?file=".$dir.$timebase_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$timebase_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($keyboard_file <> '') {
		$url_this_page = "keyboard.php?file=".$dir.$keyboard_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$keyboard_file."’\">&nbsp;";
		echo "</td></form>";
		}
	if($glossary_file <> '') {
		$url_this_page = "glossary.php?file=".$dir.$glossary_file;
		echo "<td><form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<input style=\"background-color:yellow;\" type=\"submit\"  onclick=\"this.form.target='_blank';return true;\" value=\"EDIT ‘".$glossary_file."’\">&nbsp;";
		echo "</td></form>";
		}
	echo "</tr></table>";
	return;
	}

function ask_create_new_file($url_this_page,$filename) {
	echo "File ‘".$filename."’ not found. Do you wish to create a new one under that name?";
	echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";

	echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"createfile\" value=\"YES\">";
	echo "&nbsp;<input style=\"background-color:yellow;\" type=\"submit\" name=\"dontcreate\" value=\"NO\"></p>";
	echo "</form>";
	die();
	}

function try_create_new_file($file,$filename) {
	if(isset($_POST['dontcreate'])) {
		echo "<p style=\"color:red;\">No file created. You can close this tab…</p>";
		die();
		}
	if(isset($_POST['createfile'])) {
		echo "<p style=\"color:red;\">Creating ‘".$filename."’…</p>";
		$handle = fopen($file,"w");
		fclose($handle);
		}
	}

function compile_help($text_help_file,$html_help_file) {
//	echo "text_help_file = ".$text_help_file."<br />";
//	echo "html_help_file = ".$html_help_file."<br />";
	$help = array();
	$help[0] = '';
	$no_entry = array("ON","OFF","vel");
	if(!file_exists($text_help_file)) {
		echo "<p style=\"color:red;\">WARNING: file “BP2_helt.txt” has not been found. It should be placed at the same level as the “php” folder.</p>";
		return '';
		}
	$content = @file_get_contents($text_help_file,TRUE);
	if($content) {
		$file_header = "<!DOCTYPE HTML>\n";
		$file_header .= "<html lang=\"en\">";
		$file_header .= "<head>";
		$file_header .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />";
		$file_header .= "<link rel=\"stylesheet\" href=\"bp.css\" />\n";
		$file_header .= "<script>\n";
		$file_header .= "function unhide(divID) {
	    var x = document.getElementById(divID);
	    if(x) {
	      x.className=(x.className=='hidden')?'unhidden':'hidden'; }
	  }\n";
		$file_header .= "</script>\n";
		$file_header .= "</head>";
		$file_header .= "<body>\n";
		$content = str_replace("<","&lt;",$content);
		$content = str_replace(">","&gt;",$content);
		$content = str_replace(chr(10),"<br />",$content);
		$content = str_replace("  ","&nbsp;&nbsp;",$content); // Remove tabulations
		$table = explode("###",$content);
		$handle = fopen($html_help_file,"w");
		$file_header .= "<p style=\"color:green;\">".$table[0]."</p>";
		$im = count($table);
		for($i = 1; $i < $im; $i++) {
			$table2 = explode("<br />",$table[$i]);
			$thetitle = trim($table2[0]);
			if($thetitle == "END OF BP2 help") {
			//	$im--;
				break;
				}
			$title[$i] = $thetitle;
			$item[$i] = '';
			for($j = 1; $j < count($table2); $j++)
				$item[$i] .= $table2[$j]."<br />";
			}
		fwrite($handle,$file_header."\n");
		$table_of_contents = "<table style=\"border-spacing: 2px;\" cellpadding=\"2px;\"><tr>";
		$col = 1;
		for($i = 1; $i < $im; $i++) {
			if($col > 2) {
				$col = 1;
				$table_of_contents .= "</tr><tr>";
				}
			if(isset($title[$i]) AND $title[$i] <> '') {
				$table_of_contents .= "<td><small><a href=\"#".$i."\">".$title[$i]."</a></small></td>";
				$col++;
				$token = preg_replace("/\s?\[.*$/u",'',$title[$i]);
				$token = preg_replace("/\s?\(.*$/u",'',$token);
		//		$token = preg_replace("/\s?:.*$/u",'',$token);
				if(!in_array($token,$no_entry))
					$help[$i] = $title[$i];
				else $help[$i] = '';
				}
			}
		$table_of_contents .= "</tr></table>";
		$table_header = "<h4 id=\"toc\" style=\"color:red;\">►&nbsp; Table of contents <a  href=\"javascript:unhide('up');unhide('up2');unhide('down');\"><span id=\"down\" class=\"unhidden\">[Show list…]</span></a>&nbsp;<a  href=\"javascript:unhide('up');unhide('up2');unhide('down');\"><span id=\"up2\" class=\"hidden\">[Hide list…]</span></a></h4>";
		$table_header  .= "<div id=\"up\" class=\"hidden\">";
		$table_header  .= $table_of_contents;
		$table_header  .= "<p style=\"text-align:center;\">[<a class=\"triangle\" href=\"javascript:unhide('up');unhide('up2');unhide('down');\">Hide list…</a>]</p></div>";
		fwrite($handle,$table_header."\n");
		for($i = 1; $i < $im; $i++) {
			if(!isset($title[$i])) continue;
			fwrite($handle,"<h4 style=\"color:green;\" id=\"".$i."\"><a href=\"#toc\">⇪</a> ".$title[$i]."</h4>\n");
			fwrite($handle,$item[$i]."\n");
			}
		fwrite($handle,"</body>");
		fclose($handle);
		}
	return $help;
	}

function link_to_help() {
	global $html_help_file;
	$console_link = "produce.php?instruction=help";
	$link = "<p>➡ <a onclick=\"window.open('".$html_help_file."','Help','width=800,height=500'); return false;\" href=\"".$html_help_file."\">Display complete help file</a> or the console's <a href=\"".$console_link."\" onclick=\"window.open('".$console_link."','help','width=800,height=800,left=200'); return false;\">help file</a></p>";
	return $link;
	}

function display_help_entries($content) {
	$table = explode("\n",$content);
	$ignore = FALSE;
	$entries = "<br /><table id=\"help_entries\" style=\"border-spacing: 2px;\"><tr><td style=\"padding:1em; background-color:azure;\">";
	for($i = 0; $i < count($table); $i++) {
		$line = trim($table[$i]);
		$last_one = FALSE;
		if(is_integer(strpos($line,"COMMENT:")) OR is_integer(strpos($line,"DATA:"))) $last_one = TRUE;
		$line = preg_replace("/\/\/.*$/u",'',$line);
		if(strlen($line) < 2) continue;
		if(!$ignore) $line = add_help_links($line);
		if($last_one) $ignore = TRUE;
		$entries .= $line."<br />";
		}
	$entries .= "</td></tr></table>";
	return $entries;
	}

function add_help_links($line) {
	global $help, $html_help_file;
	if(is_integer($pos=strpos($line,"-")) AND $pos == 0) return $line;
	$done = array();
	for($i = count($help) - 1; $i > 0; $i--) {
		if(!isset($help[$i])) continue;
		$token = preg_replace("/\s?\[.*$/u",'',$help[$i]);
		$token = preg_replace("/\s?\(.*$/u",'',$token);
		$token = preg_replace("/\s?«.*$/u",'',$token);
		$token = preg_replace("/\s?:.*$/u",'',$token);
		$token_length = strlen($token);
		if($token_length < 2) continue;
		$start = 0;
		$start_max = strlen($line) - $token_length;
		do {
			if(is_integer($pos=strpos($line,$token,$start))) {
				if(isset($done[$pos])) $start = $pos + strlen($token);
				else {
					$pos1 = $pos;
					$pos2 = $pos + strlen($token);
					$l1 = substr($line,0,$pos1);
					$l2 = substr($line,$pos1,strlen($token));
					$l3 = substr($line,$pos2,strlen($line) - $pos2);
					$insert = "<a onclick=\"window.open('".$html_help_file."#".$i."','show_".$i."','width=800,height=300'); return false;\" href=\"".$html_help_file."#".$i."\">";
					$line = $l1.$insert.$l2."</a>".$l3;
					$posdone = $pos1 + strlen($insert);
					$done[$posdone] = TRUE;
					// We should not insert another help link if a shorter token has been found at the same position
					// For instance: “_velcont _vel”
					break;
					}
				}
			else break;
			}
		while($start <= $start_max);
		}
	return $line;
	}

function gcd ($a, $b) {
    return $b ? gcd($b, $a % $b) : $a;
	}

function clean_up_encoding($convert,$text) {
	if($convert) $text = mb_convert_encoding($text, "UTF-8", mb_detect_encoding($text, "UTF-8, ISO-8859-1, ISO-8859-15", true));
	$text = str_replace("¥","•",$text);
	$text = str_replace("Ô","‘",$text);
	$text = str_replace("Õ","’",$text);
	$text = str_replace("Ò","“",$text);
	$text = str_replace("Ó","”",$text);
	$text = str_replace("É","…",$text);
	$text = str_replace("Â","¬",$text);
	$text = str_replace("¤","•",$text);
	$text = str_replace("â¢","•",$text);
	$text = str_replace(" "," ",$text);
	return $text;
	}

function recode_tags($text) {
	$text = str_replace("<","&lt;",$text);
	$text = str_replace(">","&gt;",$text);
	$text = str_replace('"',"&quot;",$text);
	return $text;
	}

function recode_entities($text) {
	$text = str_replace("•","&bull;",$text);
	$text = str_replace(" … "," _rest ",$text);
	return $text;
	}

function clean_up_file($file) {
	if(!file_exists($file)) {
		echo "<p style=\"color:red;\">ERROR file not found: ".$file."</p>";
		return '';
		}
	$tracefile_html = str_replace(".txt",".html",$file);
	$text = @file_get_contents($file,TRUE);
	$text = str_replace(chr(13).chr(10),chr(10),$text);
	$text = str_replace(chr(13),chr(10),$text);
	$text = str_replace(chr(9),' ',$text);
	$text = trim($text);
	$text = clean_up_encoding(TRUE,$text);
	do $text = str_replace(chr(10).chr(10).chr(10),chr(10).chr(10),$text,$count);
	while($count > 0);
	$text = str_replace(chr(10),"<br />",$text);
	$handle = fopen($tracefile_html,"w");
	$header = "<head>\n";
	$header .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />\n";
	$header .= "</head><body>\n";
	fwrite($handle,$header."\n");
	fwrite($handle,$text."\n");
	fwrite($handle,"</body>\n");
	fclose($handle);
	return $tracefile_html;
	}

function get_setting($parameter,$settings_file) {
	global $dir;
	$bp_parameter_names = @file_get_contents("bp_parameter_names.txt",TRUE);
	if($bp_parameter_names == FALSE) return "error reading bp_parameter_names.txt";
	$table = explode(chr(10),$bp_parameter_names);
	$imax = count($table);
	$imax_parameters = 0;
	for($i = 0; $i < $imax; $i++) {
		$line = trim($table[$i]);
		if($line == "-- end --") break;
		$imax_parameters++;
		$table2 = explode(chr(9),$line);
		$x = str_replace(chr(9),".",$line);
		if(count($table2) < 3) echo "ERR: ".$table2[0]."<br />";
		$parameter_name[$i] = $table2[0];
		$parameter_unit[$i] = $table2[1];
		$parameter_edit[$i] = $table2[2];
		if(count($table2) > 3 AND $table2[3] > 0)
			$parameter_yesno[$i] = TRUE;
		else $parameter_yesno[$i] = FALSE;
		}
	$content = @file_get_contents($dir.$settings_file,TRUE);
	if($content == FALSE) return "error reading ".$dir.$settings_file;
	$pick_up_headers = pick_up_headers($content);
	$content = $pick_up_headers['content'];
	$table = explode(chr(10),$content);
	$i = -1;
	if($parameter == "note_convention") $i = 47;
	if($parameter == "show_production") $i = 14;
	if($parameter == "trace_production") $i = 17;
	if($parameter == "produce_all_items") $i = 13;
	if($i <> -1) return $table[$i];
	else return '';
	}

function note_convention($i) {
	switch($i) {
		case 0: $c = "english"; break;
		case 1: $c = "french"; break;
		case 2: $c = "indian"; break;
		}
	return $c;
	}

function my_rmdir($src) {
    $dir = opendir($src);
    while(FALSE !== ($file = readdir($dir))) {
        if(($file <> '.' ) && ($file <> '..')) {
            $full = $src.'/'.$file;
            if(is_dir($full)) my_rmdir($full);
            else unlink($full);
            }
        }
    closedir($dir);
    rmdir($src);
    return;
	}

function SaveObjectPrototypes($verbose,$dir,$filename,$temp_folder) {
	global $top_header;
	$handle = fopen($dir.$filename,"w");
//	$handle = fopen($dir."essai.txt","w");
	$file_header = $top_header."\n// Object prototypes file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	$PrototypeTickKey = $_POST['PrototypeTickKey'];
	fwrite($handle,$PrototypeTickKey."\n");
	$PrototypeTickChannel = $_POST['PrototypeTickChannel'];
	fwrite($handle,$PrototypeTickChannel."\n");
	$PrototypeTickVelocity = $_POST['PrototypeTickVelocity'];
	fwrite($handle,$PrototypeTickVelocity."\n");
	$CsoundInstruments_filename = $_POST['CsoundInstruments_filename'];
	fwrite($handle,$CsoundInstruments_filename."\n");
	$maxsounds = $_POST['maxsounds'];
	fwrite($handle,$maxsounds."\n");
	$dircontent = scandir($temp_folder);
	foreach($dircontent as $thisfile) {
		if($thisfile == '.' OR $thisfile == ".." OR $thisfile == ".DS_Store") continue;
		$table = explode(".",$thisfile);
		$extension = end($table);
		if($extension <> "txt") continue;
		$object_label = str_replace(".".$extension,'',$thisfile);
		if($verbose) echo $object_label." ";
		$content = file_get_contents($temp_folder."/".$thisfile,TRUE);
		$pick_up_headers = pick_up_headers($content);
		$headers = $pick_up_headers['headers'];
		if(!is_integer($pos=strpos($headers,"//"))) continue;
		$content = $pick_up_headers['content'];
		$table = explode(chr(10),$content);
		$line = "<HTML>".$object_label."</HTML>";
		fwrite($handle,$line."\n");
		for($i = 1; $i < count($table); $i++) {
			$line = $table[$i];
			fwrite($handle,$line."\n");
			if($line == "_endCsoundScore_") {
				// We fetch MIDI codes from a separate "midibytes.txt" file
				$object_foldername = clean_folder_name($object_label);
				$save_codes_dir = $temp_folder."/".$object_foldername."_codes";
				$midi_bytes = $save_codes_dir."/midibytes.txt";
			//	if(!file_exists($midi_bytes)) { echo $midi_bytes; die(); }
				$all_bytes = @file_get_contents($midi_bytes,TRUE);
				$table_bytes = explode(chr(10),$all_bytes);
				for($j = 0; $j < count($table_bytes); $j++) {
					$byte = trim($table_bytes[$j]);
					if($byte <> '') fwrite($handle,$byte."\n");
					}
				}
			}
		}
	fwrite($handle,"DATA:\n");
	$comment_on_file = $_POST['comment_on_file'];
	$comment_on_file = recode_tags($comment_on_file);
	fwrite($handle,"<HTML>".$comment_on_file."</HTML>\n");
	fwrite($handle,"_endSoundObjectFile_\n");
	fclose($handle);
	if($verbose) echo "</font></p><hr>";
	return;
	}

function reformat_grammar($verbose,$grammar_file) {
	if(!file_exists($grammar_file)) return;
	$content = @file_get_contents($grammar_file,TRUE);
	$new_content = $content;
	$i_gram = $irul = 1;
	$section_headers = array("RND","ORD","LIN","SUB","SUB1","TEM","POSLONG","LEFT","RIGHT","INIT:","TIMEPATTERNS:","DATA:","COMMENTS:");
	$table = explode(chr(10),$new_content);
	$ignore_all = FALSE;
	$i_line_max = count($table);
	for($i_line = 0; $i_line < $i_line_max; $i_line++) {
		$line = trim($table[$i_line]);
		$line_no_brackets = preg_replace("/\s*?\[.*\]/u",'',$line);
		$ignore = FALSE;
		if($line_no_brackets == '') $ignore = TRUE;
		if(!is_integer(strpos($line,"-->")) AND !is_integer(strpos($line,"<->")) AND !is_integer(strpos($line,"<--"))) $ignore = TRUE;
		if(is_integer($pos=strpos($line,"//")) AND $pos == 0) $ignore = TRUE;
		if(is_integer($pos=strpos($line,"--")) AND $pos == 0) {
			$i_gram++; $irul = 1;
			$ignore = TRUE;
			}
		if(is_integer($pos=strpos($line,"-")) AND $pos == 0) $ignore = TRUE;
		if(is_integer($pos=strpos($line,"_")) AND $pos == 0) $ignore = TRUE;
		if(is_integer($pos=strpos($line,"[")) AND $pos == 0) $ignore = TRUE;
		if(is_integer($pos=stripos($line,"gram#")) AND $pos == 0) {
			$ignore = TRUE;
			$line = preg_replace("/^GRAM#/u","gram#",$line);
			$line = preg_replace("/^gram#([0-9]+)\[([0-9]+)\]/u","gram#".$i_gram."[".$irul."]",$line);
			$irul++;
			}
		if(in_array($line_no_brackets,$section_headers)) $ignore = TRUE;
		if($line_no_brackets == "TIMEPATTERNS:") {
			if($verbose) echo $line."<br />";
			$i_line++;
			do {
				$line = trim($table[$i_line]);
				$table[$i_line] = $line;
				if($verbose) echo $line."<br />";
				$i_line++;
				}
			while(!is_integer($pos=strpos($line,"--")) AND $i_line < $i_line_max);
			continue;
			}
		if($line_no_brackets == "DATA:" OR $line_no_brackets == "COMMENTS:") $ignore_all = TRUE;
		if(!$ignore AND !$ignore_all) {
			$line = "gram#".$i_gram."[".$irul."] ".$line;
			$irul++;
			}
		if($verbose) echo $line."<br />";
		$table[$i_line] = $line;
		}
	$new_content = implode(chr(10),$table);
	// $grammar_file = "-gr._test";
	$handle = fopen($grammar_file,"w");
	fwrite($handle,$new_content);
	fclose($handle);
	return;
	}

function clean_folder_name($name) {
	// It shouldn't create trouble when part of PHP, Javascript or command-line arguments
	$name = str_replace('_','-',$name);
	$name = str_replace(' ','-',$name);
	$name = str_replace("'",'-',$name);
	$name = str_replace('"','-',$name);
	return $name;
	}

function convert_mf2t_to_bytes($verbose,$midi_import,$midi,$midi_file) {
	// midi_file contains the code in MIDI format
	$midi->importMid($midi_file);
	$midi_text_bytes = array();
	$jcode = 0;
	$tt = 0; // We ask for absolute time stamps
	$text = $midi->getTxt($tt);
	$handle = fopen($midi_import,"w");
	$table = explode(chr(10),$text);
	for($i = 0; $i < count($table); $i++) {
		$line = $table[$i];
	//	echo $line."<br />";
		fwrite($handle,$line."\n");
		$table2 = explode(" ",$line);
		if(count($table2) < 4) continue;
		$time = intval($table2[0]);
		$chan = str_replace("ch=",'',$table2[2]);
		$code[0] = $code[1] = $code[2] = $code[3] = -1;
		if(isset($table2[3]) AND $table2[0] == "MFile") {
			$division = $table2[3];
			$midi_text_bytes[$jcode++] = intval($division);
			}
		else if(isset($table2[1]) AND $table2[1] == "ChPr") {
			$val = str_replace("v=",'',$table2[3]);
			if($verbose) echo $time." (ch ".$chan.") Channel pressure ".$val."<br />";
			$code[0] = 208 + $chan - 1;
			$code[1] = $val;
			}
		else if(isset($table2[1]) AND $table2[1] == "Pb") {
			$val = str_replace("v=",'',$table2[3]);
			if($verbose) echo $time." (ch ".$chan.") Pitchbend ".$val."<br />";
			$code[0] = 224 + $chan - 1;
			$code[1] = $val % 256;
			$code[2] = ($val - $code[1]) / 256;
			}
		else if(isset($table2[1]) AND $table2[1] == "PrCh") {
			$prog = str_replace("p=",'',$table2[3]);
			if($verbose) echo $time." (ch ".$chan.") Prog change ".$prog."<br />";
			$code[0] = 192 + $chan - 1;
			$code[1] = $prog;
			}
		else if(isset($table2[1]) AND $table2[1] == "On") {
			$key = str_replace("n=",'',$table2[3]);
			$vel = str_replace("v=",'',$table2[4]);
			if($verbose) echo $time." (ch ".$chan.") NoteOn ".$key." ".$vel."<br />";
			$code[0] = 144 + $chan - 1;
			$code[1] = $key;
			$code[2] = $vel;
			}
		else if(isset($table2[1]) AND $table2[1] == "Off") {
			$key = str_replace("n=",'',$table2[3]);
			$vel = str_replace("v=",'',$table2[4]);
			if($verbose) echo $time." (ch ".$chan.") NoteOff key ".$key." ".$vel."<br />";
			$code[0] = 128 + $chan - 1;
			$code[1] = $key;
			$code[2] = $vel;
			}
		else if(isset($table2[1]) AND $table2[1] == "Par") {
			$ctrl = str_replace("c=",'',$table2[3]);
			$val = str_replace("v=",'',$table2[4]);
			if($verbose) echo $time." (ch ".$chan.") Parameter ctrl ".$ctrl." ".$val."<br />";
			$code[0] = 176 + $chan - 1;
			$code[1] = $ctrl;
			if($ctrl > 64) { // 7-bit controller/switch
				$code[2] = $val;
				}
			else { // 14-bit controller
				$code[2] = $val % 256;
				$code[3] = ($val - $code[2]) / 256;
				}
			}
		else if(isset($table2[1]) AND $table2[1] == "PoPr") {
			$key = str_replace("n=",'',$table2[3]);
			$val = str_replace("v=",'',$table2[4]);
			if($verbose) echo $time." (ch ".$chan.") Poly pressure key ".$key." ".$val."<br />";
			$code[0] = 160 + $chan - 1;
			$code[1] = $key;
			$code[2] = $val;
			}
		$time_signature = 256 * $time;
		for($j = 0; $j < 4; $j++) {
			if($code[$j] >= 0) {
				$byte = $time_signature + $code[$j];
				$midi_text_bytes[$jcode++] = $byte;
				}
			}
		}
	fclose($handle);
	return $midi_text_bytes;
	}

function rcopy($src,$dst) {
	if(file_exists($dst)) my_rmdir($dst);
	if(is_dir($src)) {
		mkdir($dst);
		$files = scandir($src);
		foreach($files as $file)
			if($file <> "." AND $file <> "..") rcopy("$src/$file","$dst/$file");
		}
	else if(file_exists($src)) copy($src,$dst);
	return;
	}
?>
