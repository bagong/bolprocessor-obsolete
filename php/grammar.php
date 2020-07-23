<?php
require_once("_basic_tasks.php");
require_once("_settings.php");

$url_this_page = "grammar.php";

// echo $root;

if(isset($_GET['file'])) $grammar_file = $_GET['file'];
else $grammar_file = '';
if($grammar_file == '') die();
$url_this_page .= "?file=".$grammar_file;

$table = explode('/',$grammar_file);
$filename = $table[count($table) - 1];
$dir = str_replace($filename,'',$grammar_file);

$here = str_replace($root,'',$dir);
$trace_link = $here.$tracefile;
// echo "<br />".$trace_link."<br />";
if($output_folder <> '')
	$output = $root."bolprocessor/".$output_folder;
else
	$output = $root."bolprocessor";
$output_file = "out.sco";
$file_format = "csound";
if(isset($_POST['output_file'])) $output_file = $_POST['output_file'];
if(isset($_POST['file_format'])) $file_format = $_POST['file_format'];

require_once("_header.php");

echo "<p>Current directory = ".$here;

if(isset($_POST['savegrammar']) OR isset($_POST['compilegrammar'])) {
	if(isset($_POST['savegrammar'])) echo "…&nbsp;<span id=\"timespan\" style=\"color:red;\"> Saved “".$filename."” file…</span>";
	$content = $_POST['thisgrammar'];
	$output_file = $_POST['output_file'];
	$file_format = $_POST['file_format'];
	$show_production = $_POST['show_production'];
	$trace_production = $_POST['trace_production'];
	$produce_all_items = $_POST['produce_all_items'];
	if(isset($_POST['alphabet_file'])) $alphabet_file = $_POST['alphabet_file'];
	else $alphabet_file = '';
	if(isset($_POST['note_convention'])) $note_convention = $_POST['note_convention'];
	else $note_convention = '';
	if($file_format == "data") {
		$output_file = trim(str_replace(".bpda",'',$output_file));
		if($output_file == '') $output_file = "out";
		$output_file .= ".bpda";
		}
	if($file_format == "csound") {
		$table = explode('.',$output_file);
		$extension = $table[count($table) - 1];
		if($extension <> "sco") {
			$output_file = trim(str_replace(".sco",'',$output_file));
			if($output_file == '') $output_file = "out";
			$output_file .= ".sco";
			}
		}
	if($file_format == "midi") {
		$table = explode('.',$output_file);
		$extension = $table[count($table) - 1];
		if($extension <> "mid") {
			$output_file = trim(str_replace(".mid",'',$output_file));
			if($output_file == '') $output_file = "out";
			$output_file .= ".mid";
			}
		}
	if($file_format == '') $output_file = '';
	$handle = fopen($grammar_file,"w");
	$content = recode_entities($content);
	$file_header = $top_header."\n// Grammar file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	fwrite($handle,$content);
	fclose($handle);
/*	if(isset($_POST['produce'])) {
		if($produce_all_items > 0) $action = "produce-all";
		else $action = "produce";
		$link = "produce.php?instruction=".$action."&grammar=".$grammar_file;
		if($alphabet_file <> '')
			$link .= "&alphabet=".$alphabet_file;
		if($note_convention <> '')
			$link .= "&note_convention=".$note_convention;
		$link .= "&output=".$output."/".$output_file."&format=".$file_format;
		if($show_production > 0)
			$link .= "&show_production=1";
		if($trace_production > 0)
			$link .= "&trace_production=1";
		header("Location: ".$link);
		die();
		} */
	}
echo "</p>";

if(isset($_POST['change_output_folder'])) {
	$output_folder = trim($_POST['output_folder']);
	$output_folder = trim(str_replace('/',' ',$output_folder));
	$output_folder = str_replace(' ','/',$output_folder);
	$output = $root."bolprocessor/".$output_folder;
	do $output = str_replace("//",'/',$output,$count);
	while($count > 0);
//	echo $output."<br />";
	if(!file_exists($output)) {
		echo "<p id=\"timespan\"><font color=\"red\">Created folder:</font><font color=\"blue\"> ".$output."</font><br />";
		$cmd = "mkdir ".$output;
		echo "(".$cmd.")</p>";
		exec($cmd);
		}
	$handle = fopen("_settings.php","w");
	fwrite($handle,"<?php\n");
	$line = "§output_folder = \"".$output_folder."\";\n";
	$line = str_replace('§','$',$line);
	fwrite($handle,$line);
	$line = "§>\n";
	$line = str_replace('§','?',$line);
	fwrite($handle,$line);
	fclose($handle);
	}

// require_once("_header.php");
echo link_to_help();

echo "<h3>Grammar file “".$filename."”</h3>";

if(isset($_POST['compilegrammar'])) {
	if(isset($_POST['alphabet_file'])) $alphabet_file = $_POST['alphabet_file'];
	else $alphabet_file = '';
	if(isset($_POST['note_convention'])) $note_convention = $_POST['note_convention'];
	else $note_convention = '';
	echo "<p id=\"timespan\" style=\"color:red;\">Compiling ‘".$filename."’</p>";
	$initial_time = filemtime($grammar_file);
//	echo date("F d Y H:i:s",$initial_time)."<br />";
	$application_path = $root."bolprocessor/";
	chdir($dir);
	$command = $application_path."bp compile";
	if($note_convention <> '') $command .= " --".$note_convention;
	$thisgrammar = $filename;
	if(is_integer(strpos($thisgrammar,' ')))
		$thisgrammar = '"'.$thisgrammar.'"';
	$command .= " ".$thisgrammar;
	$thisalphabet = $alphabet_file;
	if(is_integer(strpos($thisalphabet,' ')))
		$thisalphabet = '"'.$thisalphabet.'"';
	if($alphabet_file <> '')
		$command .= " ".$thisalphabet;
	$command .= " --traceout ".$tracefile;
	echo "<p><small>".$command."</small></p>";
	$no_error = FALSE;
	exec($command,$o);
	$n_messages = count($o);
	if($n_messages > 0) {
		for($i=0; $i < $n_messages; $i++) {
			$mssg = $o[$i];
			$mssg = clean_up_encoding(TRUE,$mssg);
			if(is_integer($pos=strpos($mssg,"Errors: 0")) AND $pos == 0) $no_error = TRUE;
			}
		}
	if(!$no_error) {
		$tracefile_html = clean_up_file($tracefile);
		$trace_link = str_replace($tracefile,$tracefile_html,$trace_link);
		echo "<p><font color=\"red\">Errors found! Open the </font> <a onclick=\"window.open('/".$trace_link."','trace','width=800,height=800'); return false;\" href=\"/".$trace_link."\">trace file</a>!</p>";
		}
	else echo "<p><font color=\"red\">➡</font> <font color=\"blue\">No error.</font></p>";
		
	// Now reformat the grammar
	reformat_grammar(FALSE,$grammar_file);
	}
else {
	echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
	echo "<input type=\"hidden\" name=\"output_file\" value=\"".$output_file."\">";
	echo "<input type=\"hidden\" name=\"file_format\" value=\"".$file_format."\">";
	echo "Location of output files: <font color=\"blue\">/bolprocessor/</font>";
	echo "<input type=\"text\" name=\"output_folder\" size=\"25\" value=\"".$output_folder."\">";
	echo "&nbsp;<input style=\"background-color:yellow;\" type=\"submit\" name=\"change_output_folder\" value=\"SAVE THIS LOCATION\"><br />➡ global setting for all projects in this session.<br /><i>Folder will be created if necessary…</i>";
	echo "</form>";
	}

$content = @file_get_contents($grammar_file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
$metronome = 0;
$time_structure = $objects_file = $csound_file = $alphabet_file = $settings_file = $orchestra_file = $interaction_file = $midisetup_file = $timebase_file = $keyboard_file = $glossary_file = '';
$pick_up_headers = pick_up_headers($content);
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."</p>";
$content = $pick_up_headers['content'];
$alphabet_file = $pick_up_headers['alphabet'];
$objects_file = $pick_up_headers['objects'];
$csound_file = $pick_up_headers['csound'];
$settings_file = $pick_up_headers['settings'];
$orchestra_file = $pick_up_headers['orchestra'];
$interaction_file = $pick_up_headers['interaction'];
$midisetup_file = $pick_up_headers['midisetup'];
$timebase_file = $pick_up_headers['timebase'];
$keyboard_file = $pick_up_headers['keyboard'];
$glossary_file = $pick_up_headers['glossary'];
$metronome = $pick_up_headers['metronome'];
$time_structure = $pick_up_headers['time_structure'];

if($settings_file <> '') $note_convention = note_convention(get_setting("note_convention",$settings_file));
else $note_convention = '';
if($settings_file <> '') $show_production = get_setting("show_production",$settings_file);
else $show_production = 0;
if($settings_file <> '') $trace_production = get_setting("trace_production",$settings_file);
else $trace_production = 0;
if($settings_file <> '') $produce_all_items = get_setting("produce_all_items",$settings_file);
else $produce_all_items = 0;

echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<table cellpadding=\"8px;\"><tr style=\"background-color:white;\">";
echo "<td><p>Name of output file (with proper extension):<br /><input type=\"text\" name=\"output_file\" size=\"25\" value=\"".$output_file."\">&nbsp;";
echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"savegrammar\" value=\"SAVE\"></p>";
echo "</td>";
echo "<td><p style=\"text-align:left;\">";
echo "<input type=\"radio\" name=\"file_format\" value=\"\"";
if($file_format == "") echo " checked";
echo ">No file";
echo "<br /><input type=\"radio\" name=\"file_format\" value=\"data\"";
if($file_format == "data") echo " checked";
echo ">BP data file";
echo "<br /><input type=\"radio\" name=\"file_format\" value=\"midi\"";
if($file_format == "midi") echo " checked";
echo ">MIDI file";
echo "<br /><input type=\"radio\" name=\"file_format\" value=\"csound\"";
if($file_format == "csound") echo " checked";
echo ">CSOUND file";
echo "</p></td>";
echo "<td style=\"text-align:right; vertical-align:middle;\" rowspan=\"2\">";
echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"savegrammar\" value=\"SAVE ‘".$filename."’\"><br /><br />";
// echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"compilegrammar\" value=\"COMPILE GRAMMAR\"></p>";
echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"compilegrammar\" value=\"COMPILE GRAMMAR\"><br /><br />";
// echo "<p><input style=\"color:DarkBlue; background-color:Aquamarine;\" onclick=\"this.form.target='_blank';return true;\" type=\"submit\" name=\"produce\" value=\"PRODUCE ITEM(s)\">";
if($produce_all_items > 0) $action = "produce-all";
else $action = "produce";
$link = "produce.php?instruction=".$action."&grammar=".$grammar_file;
if($alphabet_file <> '')
	$link .= "&alphabet=".$alphabet_file;
if($note_convention <> '')
	$link .= "&note_convention=".$note_convention;
$link .= "&output=".$output."/".$output_file."&format=".$file_format;
if($show_production > 0)
	$link .= "&show_production=1";
if($trace_production > 0)
	$link .= "&trace_production=1";
echo "<input style=\"color:DarkBlue; background-color:Aquamarine;\" onclick=\"window.open('".$link."','produce','width=800,height=800'); return false;\" type=\"submit\" name=\"produce\" value=\"PRODUCE ITEM(s)\">";
echo "</td></tr>";
echo "<tr><td colspan=\"2\"><p style=\"text-align:center;\">➡ <i>You can change above settings, then save the grammar…</i></p></td></tr>";
echo "</table>";

if($settings_file == '') {
	if($metronome > 0) {
		$p = intval($metronome * 10000);
		$q = 600000;
		$gcd = gcd($p,$q);
		$p = $p / $gcd;
		$q = $q / $gcd;
		if(intval($metronome) == $metronome)
			$metronome = intval($metronome);
		else $metronome = sprintf("%.4f",$metronome);
		echo "<p style=\"color:blue;\">⏱ Time base: ".$p." ticks in ".$q." seconds (metronome = ".$metronome." beats per minute)<br />";
		if($time_structure == '') $time_structure = "striated";
		echo "⏱ Time structure: ".$time_structure."</p>";
		}
	else {
		$metronome = 60;
		if($time_structure <> '')
			echo "<p style=\"color:blue;\">⏱ Metronome (time base) is not properly specified. It will be set to 60 beats per minute and time structure will be ".$time_structure.".</p>";
		else
			echo "<p style=\"color:blue;\">⏱ Metronome (time base) and structure of time are neither specified nor set up by a ‘-se’ file.<br />Therefore they will be set to 60 beats per minute and striated.</p>";
		$time_structure = "striated";
		}
	}
else {
//	if(!$bad_metronome) ;
	if($metronome > 0)
		echo "<font color=\"red\">⏱ Metronome and structure of time indicated in this grammar will be ignored as they are set up by ‘".$settings_file."’</font><br />";
	else
		echo "<font color=\"blue\">⏱ Metronome (time base) and structure of time will be fixed by ‘".$settings_file."’</font><br />";
	$metronome = 0;
	$time_structure = '';
	echo "<input type=\"hidden\" name=\"settings_file\" value=\"".$settings_file."\">";
	}
// echo "<p>";
if($note_convention <> '') {
	echo "• Note convention = <font color=\"blue\">".ucfirst($note_convention)."</font> found in ‘".$settings_file."’<br />";
	echo "<input type=\"hidden\" name=\"note_convention\" value=\"".$note_convention."\">";
	}
// echo "show_production = ".$show_production."<br />";
if($produce_all_items == 1) echo "• Produce all items has been set in ‘".$settings_file."’<br />";
if($show_production == 1) echo "• Show production has been set in ‘".$settings_file."’<br />";
if($trace_production == 1) echo "• Trace production has been set in ‘".$settings_file."’<br />";
echo "</p>";

echo "<input type=\"hidden\" name=\"produce_all_items\" value=\"".$produce_all_items."\">";
echo "<input type=\"hidden\" name=\"show_production\" value=\"".$show_production."\">";
echo "<input type=\"hidden\" name=\"trace_production\" value=\"".$trace_production."\">";
echo "<input type=\"hidden\" name=\"metronome\" value=\"".$metronome."\">";
echo "<input type=\"hidden\" name=\"time_structure\" value=\"".$time_structure."\">";
echo "<input type=\"hidden\" name=\"alphabet_file\" value=\"".$alphabet_file."\">";
	
echo "<textarea name=\"thisgrammar\" rows=\"25\" style=\"width:700px; background-color:Cornsilk;\">".$content."</textarea>";
echo "</form>";

display_more_buttons($content,$url_this_page,$dir,$objects_file,$csound_file,$alphabet_file,$settings_file,$orchestra_file,$interaction_file,$midisetup_file,$timebase_file,$keyboard_file,$glossary_file);
?>