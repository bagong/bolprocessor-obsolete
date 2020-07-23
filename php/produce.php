<?php
require_once("_basic_tasks.php");

$cwd = getcwd();
// echo "cwd = ".$cwd."<br />";
$root = preg_replace("/bolprocessor\/php.*/u",'',$cwd);
// echo "root = ".$root."<br />";
$url_this_page = "produce.php";

$this_title = "BP console";
require_once("_header.php");

if(isset($_GET['instruction'])) $instruction = $_GET['instruction'];
else $instruction = '';
if($instruction == '') {
	echo "ERROR: No instruction has been sent";
	die();
	}
if(isset($_GET['grammar'])) $grammar_path = $_GET['grammar'];
else $grammar_path = '';
if($grammar_path == '') die();
if(isset($_GET['note_convention'])) $note_convention = $_GET['note_convention'];
else $note_convention = '';
if(isset($_GET['format'])) $file_format = $_GET['format'];
else $file_format = '';
if($file_format <> '' AND isset($_GET['output'])) $output = $_GET['output'];
else $output = '';
if(isset($_GET['show_production'])) $show_production = TRUE;
else $show_production = FALSE;
if(isset($_GET['trace_production'])) $trace_production = TRUE;
else $trace_production = FALSE;

$table = explode('/',$grammar_path);
$grammar_name = $table[count($table) - 1];
$dir = str_replace($grammar_name,'',$grammar_path);
$application_path = $root."bolprocessor/";

if($output <> '') @unlink($output);
if($tracefile <> '') @unlink($tracefile);
$command = $application_path."bp ".$instruction." -gr ".$grammar_path;
if($note_convention <> '') $command .= " --".$note_convention;
switch($file_format) {
	case "data":
		$command .= " -d -o ".$output;
		break;
	case "midi":
		$command .= " -d --midiout ".$output;
		break;
	case "csound":
		$command .= " -d --csoundout ".$output;
		break;
	default:
		$command .= " -d --rtmidi";
		break;
	}
if($tracefile <> '') $command .= " --traceout ".$dir."/".$tracefile;
if($show_production) $command .= " --show-production";
if($trace_production) $command .= " --trace-production";
echo "<p><small>command = ".$command."</small></p>";
// die();
exec($command,$o);
$n_messages = count($o);
$no_error = FALSE;
for($i=0; $i < $n_messages; $i++) {
	$mssg = $o[$i];
	if(is_integer($pos=strpos($mssg,"Errors: 0")) AND $pos == 0) $no_error = TRUE;
	}
echo "<hr>";
$output_link = str_replace($root,'',$output);
// echo "dir = ".$dir."<br />";
// echo "tracefile = ".$tracefile."<br />";
$tracefile_html = clean_up_file($dir.$tracefile);
// echo "tracefile_html = ".$tracefile_html."<br />";
$trace_link = str_replace($root,'',$tracefile_html);
// echo "trace_link = ".$trace_link."<br />";
if(!$no_error) {
	echo "<p><font color=\"red\">Errors found… Open the </font> <a onclick=\"window.open('/".$trace_link."','errors','width=800,height=800'); return false;\" href=\"/".$trace_link."\">error trace file</a>!</p>";
	}
else {
	if($output <> '') echo "<p><font color=\"red\">➡</font> Read the output file: <a onclick=\"window.open('/".$output_link."','".$file_format."','width=800,height=800'); return false;\" href=\"/".$output_link."\">click this link</a></p>";
	if($trace_production) echo "<p><font color=\"red\">➡</font> Read the <a onclick=\"window.open('/".$trace_link."','trace','width=800,height=800'); return false;\" href=\"/".$trace_link."\">trace file produced</a>!</p>";
	}
for($i=0; $i < $n_messages; $i++) {
	$mssg = $o[$i];
	$mssg = clean_up_encoding(TRUE,$mssg);
	echo $mssg."<br />";
	}
if($n_messages == 0) echo "No message produced…";
?>