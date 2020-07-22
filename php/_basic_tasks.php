<?php
session_start();
$root = getcwd();
$root = preg_replace("/bolprocessor\/php.*/u",'',$root);
$text_help_file = $root."bolprocessor/BP2_help.txt";
$html_help_file = "BP2_help.html";
$help = compile_help($text_help_file,$html_help_file);
$tracefile = "trace_".session_id().".txt";
$top_header = "// Bol Processor BP3 compatible with version BP2.9.8";

function pick_up_headers($content) {
	$content = trim($content);
	$content = str_replace(chr(13).chr(10),chr(10),$content);
	$content = str_replace(chr(13),chr(10),$content);
	$content = str_replace(chr(9),' ',$content); // Remove tabulations
	$content = clean_up_encoding($content);
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
	$help = array();
	$help[0] = '';
	$no_entry = array("ON","OFF","vel");
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
	$link = "<p>➡ <a onclick=\"window.open('".$html_help_file."','Help','width=800,height=500'); return false;\" href=\"".$html_help_file."\">Display complete help file</a></p>";
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

function clean_up_encoding($text) {
	$text = mb_convert_encoding($text, "UTF-8", mb_detect_encoding($text, "UTF-8, ISO-8859-1, ISO-8859-15", true));
	$text = str_replace("¥","•",$text);
	$text = str_replace("Ô","‘",$text);
	$text = str_replace("Õ","’",$text);
	$text = str_replace("Ò","“",$text);
	$text = str_replace("Ó","”",$text);
	$text = str_replace("É","…",$text);
	$text = str_replace("Â","¬",$text);
	$text = str_replace("¤","•",$text);
	$text = str_replace(" "," ",$text);
	return $text;
	}

function recode_tags($text) {
	$text = str_replace("<","&lt;",$text);
	$text = str_replace(">","&gt;",$text);
	$text = str_replace('"',"&quot;",$text);
	return $text;
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
?>