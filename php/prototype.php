<?php
require_once("_basic_tasks.php");
$path = getcwd();
$url_this_page = $path."/prototype.php";
define('MAXFILESIZE',1000000);

if(isset($_POST['object_name'])) {
	$object_name = $_POST['object_name'];
	$temp_folder = $_POST['temp_folder'];
	$object_file = $_POST['object_file'];
	}
else {
	"Sound-object prototype's name is not known. First open the ‘-mi’ file!"; die();
	}
	
$this_title = $object_name;
require_once("_header.php");

// echo "url_this_page = ".$url_this_page."<br />";

$object_foldername = clean_folder_name($object_name);
$save_codes_dir = $temp_folder."/".$object_foldername."_codes";
if(!is_dir($save_codes_dir)) mkdir($save_codes_dir);
$midi_file = $save_codes_dir."/midicodes.mid";
$midi_text = $save_codes_dir."/midicodes.txt";
$midi_bytes = $save_codes_dir."/midibytes.txt";
$midi_text_bytes = array();
if(isset($_FILES['mid_upload']) AND $_FILES['mid_upload']['tmp_name'] <> '') {
	$upload_filename = $_FILES['mid_upload']['name'];
	if($_FILES["mid_upload"]["size"] > MAXFILESIZE) {
		echo "<h3><font color=\"red\">Uploading failed:</font> <font color=\"blue\">".$upload_filename."</font> <font color=\"red\">is larger than ".MAXFILESIZE." bytes</font></h3>";
		}
	else {
		$tmpFile = $_FILES['mid_upload']['tmp_name'];
		copy($tmpFile,$midi_file) or die('Problems uploading this MIDI file');
		@chmod($midi_file,0666);
		$table = explode('.',$upload_filename);
		$extension = end($table);
		if($extension <> "mid" and $extension <> "midi") {
			echo "<h3><font color=\"red\">Uploading failed:</font> <font color=\"blue\">".$upload_filename."</font> <font color=\"red\">is not a MIDI file!</font></h3>";
			unlink($midi_file);
			}
		else {
			echo "<h3 id=\"timespan\"><font color=\"red\">Converting MIDI file:</font> <font color=\"blue\">".$upload_filename."</font> <font color=\"red\">...</font></h3>";
			$midi = new Midi();
			$midi_text_bytes = convert_midi_to_text(TRUE
			,$midi,$midi_file);
			}
		}
	}
else echo "<p>Object file: <font color=\"blue\">".str_replace($root,'',$object_file)."</font>";

if(isset($_POST['savethisprototype']) OR isset($_POST['suppress_pressure']) OR isset($_POST['suppress_pitchbend']) OR isset($_POST['suppress_polyphonic_pressure']) OR isset($_POST['suppress_volume']) OR isset($_POST['adjust_duration']) OR isset($_POST['adjust_beats']) OR isset($_POST['adjust_duration']) OR isset($_POST['silence_before']) OR isset($_POST['silence_after']) OR isset($_POST['add_allnotes_off']) OR isset($_POST['suppress_allnotes_off']) OR isset($_POST['quantize_NoteOn'])) {
	echo "<span id=\"timespan\">&nbsp;&nbsp;<font color=\"red\">➡ Saving this file...</font></span>";
	$prototype_file = $object_file;
	$handle = fopen($prototype_file,"w");
	$source_file = $_POST['source_file'];
	$file_header = $top_header."\n// Object prototype saved as \"".$object_name."\". Date: ".gmdate('Y-m-d H:i:s');
	$file_header .= "\n".$source_file;
	fwrite($handle,$file_header."\n");
	$object_type = 0;
	if(isset($_POST['object_type1'])) $object_type += 1;
	if(isset($_POST['object_type4'])) $object_type += 4;
	fwrite($handle,$object_type."\n");
	$j = 1;
	$resolution = $_POST["object_param_".$j++];
	fwrite($handle,$resolution."\n");
	$default_channel = $_POST["object_param_".$j++];
	fwrite($handle,$default_channel."\n");
	$Tref = $_POST['Tref'] / $resolution;
	fwrite($handle,$Tref."\n");
	$j++;
	$quantization = $_POST["object_param_".$j++];
	fwrite($handle,$quantization."\n"); // Quantization
	$pivot_mode = $_POST['Pivot_mode'];
	$string = "00000000000000000000";
	$string[$pivot_mode] = "1";
//	echo "<p>".$string."</p>";
	$k = 6;
	$okrescale = $FixScale = $OkExpand = $OkCompress = 0;
	switch($_POST['Rescale']) {
		case "okrescale":
			$okrescale = 1;
			break;
		case "neverrescale":
			$FixScale = 1;
			break;
		case "dilationrange":
			$okrescale = 0;
			break;
		}
	
	if($FixScale == 0 AND isset($_POST['OkExpand'])) $OkExpand = 1;
	if($FixScale == 0 AND isset($_POST['OkCompress'])) $OkCompress = 1;
	
	$string[$k++] = $okrescale;
	$string[$k++] = $FixScale;
	$string[$k++] = $OkExpand;
	$string[$k++] = $OkCompress;
	
	$BreakTempo = $_POST['BreakTempo'];
	$OkRelocate = $_POST['OkRelocate'];
	
	$string[$k++] = $OkRelocate;
	$string[$k++] = $BreakTempo;
	
	$ContBeg = $_POST['ContBeg'];
	$ContEnd = $_POST['ContEnd'];
	
	$string[$k++] = $ContBeg;
	$string[$k++] = $ContEnd;
	
	$CoverBeg = $_POST['CoverBeg'];
	$CoverEnd = $_POST['CoverEnd'];
	$string[$k++] = $CoverBeg;
	$string[$k++] = $CoverEnd;
	
	$TruncBeg = $_POST['TruncBeg'];
	$TruncEnd = $_POST['TruncEnd'];
	$string[$k++] = $TruncBeg;
	$string[$k++] = $TruncEnd;
	
	$pivspec = 0;
	if($_POST['Pivot_mode'] == 18) $pivspec = 1;
	$string[$k++] = $pivspec;
	
	if(isset($_POST['AlphaCtrl'])) $AlphaCtrl = 1;
	else $AlphaCtrl = 0;
	$string[$k++] = $AlphaCtrl;

	fwrite($handle,$string."\n");
	$j++;
	
	$RescaleMode = $_POST['RescaleMode']; // ???
	fwrite($handle,$RescaleMode."\n");
	
	$AlphaMin = $_POST['AlphaMin']; if($AlphaMin == '') $AlphaMin = "0.0000";
	fwrite($handle,$AlphaMin."\n");
	$AlphaMax = $_POST['AlphaMax']; if($AlphaMax == '') $AlphaMax = "0.0000";
	fwrite($handle,$AlphaMax."\n");

	if(isset($_POST['DelayMode'])) $DelayMode = $_POST['DelayMode'];
	else $DelayMode = 1;
	$MaxDelay = '';
	if($DelayMode == -1) $MaxDelay = $_POST['MaxDelay1'];
	if($DelayMode == 0) $MaxDelay = $_POST['MaxDelay2'];
	if($OkRelocate OR $MaxDelay == '') {
		$MaxDelay = 0;
		$DelayMode = 1;
		}
	fwrite($handle,$DelayMode."\n");
	fwrite($handle,$MaxDelay."\n");
	
	if(isset($_POST['ForwardMode'])) $ForwardMode = $_POST['ForwardMode'];
	else $ForwardMode = 1;
	$MaxForward = '';
	if($ForwardMode == -1) $MaxForward = $_POST['MaxForward1'];
	if($ForwardMode == 0) $MaxForward = $_POST['MaxForward2'];
	if($OkRelocate OR $MaxForward == '') {
		$MaxForward = 0;
		$ForwardMode = 1;
		}
	fwrite($handle,$ForwardMode."\n");
	fwrite($handle,$MaxForward."\n");
	
	$BreakTempoMode = $_POST['BreakTempoMode'];
	fwrite($handle,$BreakTempoMode."\n");
	fwrite($handle,"1\n"); // $x ???
	
	if(isset($_POST['ContBegMode'])) $ContBegMode = $_POST['ContBegMode'];
	else $ContBegMode = 1;
	$MaxBegGap = '';
	if($ContBegMode == -1) $MaxBegGap = $_POST['MaxBegGap1'];
	if($ContBegMode == 0) $MaxBegGap = $_POST['MaxBegGap2'];
	if(!$ContBeg OR $MaxBegGap == '') {
		$MaxBegGap = 0;
		$ContBegMode = 1;
		}
	fwrite($handle,$ContBegMode."\n");
	fwrite($handle,$MaxBegGap."\n");
	
	if(isset($_POST['ContEndMode'])) $ContEndMode = $_POST['ContEndMode'];
	else $ContEndMode = 1;
	$MaxEndGap = '';
	if($ContEndMode == -1) $MaxEndGap = $_POST['MaxEndGap1'];
	if($ContEndMode == 0) $MaxEndGap = $_POST['MaxEndGap2'];
	if(!$ContEnd OR $MaxEndGap == '') {
		$MaxEndGap = 0;
		$ContEndMode = 1;
		}
	fwrite($handle,$ContEndMode."\n");
	fwrite($handle,$MaxEndGap."\n");
	
	if(isset($_POST['CoverBegMode'])) $CoverBegMode = $_POST['CoverBegMode'];
	else $CoverBegMode = 0;
	$MaxCoverBeg = '';
	if($CoverBegMode == -1) $MaxCoverBeg = $_POST['MaxCoverBeg1'];
	if($CoverBegMode == 0) $MaxCoverBeg = $_POST['MaxCoverBeg2'];
	if($CoverBeg) {
		$CoverBegMode = 0;
		$MaxCoverBeg = 100;
		}
	fwrite($handle,$CoverBegMode."\n");
	fwrite($handle,$MaxCoverBeg."\n");
	
	if(isset($_POST['CoverEndMode'])) $CoverEndMode = $_POST['CoverEndMode'];
	else $CoverEndMode = 0;
	$MaxCoverEnd = '';
	if($CoverEndMode == -1) $MaxCoverEnd = $_POST['MaxCoverEnd1'];
	if($CoverEndMode == 0) $MaxCoverEnd = $_POST['MaxCoverEnd2'];
	if($CoverEnd) {
		$CoverEndMode = 0;
		$MaxCoverEnd = 100;
		}
	fwrite($handle,$CoverEndMode."\n");
	fwrite($handle,$MaxCoverEnd."\n");
	
	if(isset($_POST['TruncBegMode'])) $TruncBegMode = $_POST['TruncBegMode'];
	else $TruncBegMode = 1;
	$MaxTruncBeg = '';
	if($TruncBegMode == -1) $MaxTruncBeg = $_POST['MaxTruncBeg1'];
	if($TruncBegMode == 0) $MaxTruncBeg = $_POST['MaxTruncBeg2'];
	if($TruncBeg OR $MaxTruncBeg == '') {
		$MaxTruncBeg = 0;
		$TruncBegMode = 1;
		}
	fwrite($handle,$TruncBegMode."\n");
	fwrite($handle,$MaxTruncBeg."\n");
	
	if(isset($_POST['TruncEndMode'])) $TruncEndMode = $_POST['TruncEndMode'];
	else $TruncEndMode = 1;
	$MaxTruncEnd = '';
	if($TruncEndMode == -1) $MaxTruncEnd = $_POST['MaxTruncEnd1'];
	if($TruncEndMode == 0) $MaxTruncEnd = $_POST['MaxTruncEnd2'];
	if($TruncEnd OR $MaxTruncEnd == '') {
		$MaxTruncEnd = 0;
		$TruncEndMode = 1;
		}
	fwrite($handle,$TruncEndMode."\n");
	fwrite($handle,$MaxTruncEnd."\n");
	
	if(isset($_POST['PivMode'])) $PivMode = $_POST['PivMode'];
	else $PivMode = -1;
	$PivPos = '';
	if($PivMode == -1) $PivPos = $_POST['PivPos1'];
	if($PivMode == 0) $PivPos = $_POST['PivPos2'];
	if($PivPos == '') {
		$PivPos = "0.0000";
		}
	fwrite($handle,$PivMode."\n");
	fwrite($handle,$PivPos."\n");
	
	$AlphaCtrlNr = $_POST['AlphaCtrlNr'];
	if($AlphaCtrlNr == '') $AlphaCtrlNr = -1;
	$AlphaCtrlChan = $_POST['AlphaCtrlChan'];
	if($AlphaCtrlChan == '') $AlphaCtrlChan = 0;
	if(!$AlphaCtrl) {
		$AlphaCtrlNr = -1;
		$AlphaCtrlChan = 0;
		}
	fwrite($handle,$AlphaCtrlNr."\n");
	fwrite($handle,$AlphaCtrlChan."\n");

	if(isset($_POST['OkTransp'])) $OkTransp = 1;
	else $OkTransp = 0;
	if(isset($_POST['OkArticul'])) $OkArticul = 1;
	else $OkArticul = 0;
	if(isset($_POST['OkVolume'])) $OkVolume = 1;
	else $OkVolume = 0;
	if(isset($_POST['OkPan'])) $OkPan = 1;
	else $OkPan = 0;
	if(isset($_POST['OkMap'])) $OkMap = 1;
	else $OkMap = 0;
	if(isset($_POST['OkVelocity'])) $OkVelocity = 1;
	else $OkVelocity = 0;
	
	fwrite($handle,$OkTransp."\n");
	fwrite($handle,$OkArticul."\n");
	fwrite($handle,$OkVolume."\n");
	fwrite($handle,$OkPan."\n");
	fwrite($handle,$OkMap."\n");
	fwrite($handle,$OkVelocity."\n");
	
	$PreRollMode = $_POST['PreRollMode'];
	$PreRoll = '';
	if($PreRollMode == -1) $PreRoll = $_POST['PreRoll1'];
	if($PreRollMode == 0) $PreRoll = $_POST['PreRoll2'];
	if($PreRoll == '') {
		$PreRoll = 0;
		$PreRollMode = -1;
		}
	$PostRollMode = $_POST['PostRollMode'];
	$PostRoll = '';
	if($PostRollMode == -1) $PostRoll = $_POST['PostRoll1'];
	if($PostRollMode == 0) $PostRoll = $_POST['PostRoll2'];
	if($PostRoll == '') {
		$PostRoll = 0;
		$PostRollMode = -1;
		}
	fwrite($handle,$PreRoll."\n");
	fwrite($handle,$PostRoll."\n");
	fwrite($handle,$PreRollMode."\n");
	fwrite($handle,$PostRollMode."\n");
	
	$PeriodMode = $_POST['PeriodMode'];
	$BeforePeriod = '';
	if($PeriodMode == -1) $BeforePeriod = $_POST['BeforePeriod1'];
	if($PeriodMode == 0) $BeforePeriod = $_POST['BeforePeriod2'];
	if($BeforePeriod == '') $BeforePeriod = "0.0000";
	fwrite($handle,$PeriodMode."\n");
	fwrite($handle,$BeforePeriod."\n");
	
	
	if(isset($_POST['ForceIntegerPeriod'])) $ForceIntegerPeriod = 1;
	else $ForceIntegerPeriod = 0;
	if(isset($_POST['DiscardNoteOffs'])) $DiscardNoteOffs = 1;
	else $DiscardNoteOffs = 0;
	fwrite($handle,$ForceIntegerPeriod."\n");
	fwrite($handle,$DiscardNoteOffs."\n");
	
	$StrikeAgain = $_POST['StrikeAgain'];
	fwrite($handle,$StrikeAgain."\n");

	$CsoundAssignedInstr = $_POST['CsoundAssignedInstr'];
	$CsoundInstr = $_POST['CsoundInstr'];
	if($CsoundInstr == '') $CsoundInstr = -1;
	fwrite($handle,$CsoundAssignedInstr."\n");
	fwrite($handle,$CsoundInstr."\n");
	
	$Tpict = $_POST['Tpict'];
	fwrite($handle,$Tpict."\n");
	
	fwrite($handle,"65535\n");
	fwrite($handle,"65535\n");
	fwrite($handle,"65535\n");
	$jmax = $_POST['jmax'];
	for($j = $j; $j < $jmax; $j++) {
		if(isset($_POST["object_param_".$j])) {
			$value = $_POST["object_param_".$j];
			if($j == 53 AND $value <> "_endCsoundScore_") { // Csound score
				$value = str_replace("\n","<BR>",$value);
				$value = str_replace("\r",'',$value);
				$value = "<HTML>".$value."</HTML>";
				}
			fwrite($handle,$value."\n");
			}
		}
	$object_comment = $_POST['object_comment'];
	$object_comment = recode_tags($object_comment);
	$line = "<HTML>".$object_comment."</HTML>\n";
	fwrite($handle,$line."\n");
	fclose($handle);
	}

echo "</p>";
echo link_to_help();

echo "<h2>Object prototype <big><font color=\"red\">".$object_name."</font></big></h2>";

$content = file_get_contents($object_file,TRUE);
$pick_up_headers = pick_up_headers($content);
$source_file = $pick_up_headers['objects'];
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."<br />// Source: ".$source_file."</p>";
$content = $pick_up_headers['content'];

$table = explode(chr(10),$content);
$object_param = array();
$i = 0;
$j = 0; $cscore = FALSE;
do {
	$i++; $line = $table[$i];
	if(is_integer($pos=strpos($line,"_beginCsoundScore_"))) $cscore = TRUE;
	if(is_integer($pos=strpos($line,"_endCsoundScore_"))) $cscore = FALSE;
	if(!$cscore AND is_integer($pos=stripos($line,"<HTML>"))) break;
	$object_param[$j++] = $line;
	continue;
	}
while(TRUE);
$clean_line = str_ireplace("<HTML>",'',$line);
$clean_line = str_ireplace("</HTML>",'',$clean_line);
$object_comment = $clean_line;

// ---------- EDIT THIS PROTOTYPE ------------


echo "<form method=\"post\" action=\"prototype.php\" enctype=\"multipart/form-data\">";

echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisprototype\" value=\"SAVE THIS PROTOTYPE\"></p>";

echo "<input type=\"hidden\" name=\"object_name\" value=\"".$object_name."\">";
echo "<input type=\"hidden\" name=\"temp_folder\" value=\"".$temp_folder."\">";
echo "<input type=\"hidden\" name=\"object_file\" value=\"".$object_file."\">";
echo "<input type=\"hidden\" name=\"source_file\" value=\"".$source_file."\">";

$size = strlen($object_comment);
echo "<p>Comment on this prototype = <input type=\"text\" name=\"object_comment\" size=\"".$size."\" value=\"".$object_comment."\"></p>";
echo "<p>OBJECT TYPE</p>";
$j = 0;
$object_type = $object_param[$j++];
echo "<input type=\"checkbox\" name=\"object_type1\"";
   if($object_type == 1 OR $object_type == 5) echo " checked";
   echo "> MIDI sequence<br />";
echo "<input type=\"checkbox\" name=\"object_type4\"";
   if($object_type > 3) echo " checked";
   echo "> Csound score";

echo "<p>GLOBAL PARAMETERS</p>";
$resolution = $object_param[$j];
if($resolution == '' OR $resolution == 0) $resolution = 1;
$resolution = intval($resolution);
echo "Resolution = <input type=\"text\" name=\"object_param_".($j++)."\" size=\"5\" value=\"".$resolution."\"> ms<br />";

echo "Default channel = <input type=\"text\" name=\"object_param_".$j."\" size=\"5\" value=\"".$object_param[$j++]."\"><br />";

$Tref = $object_param[$j++] * $object_param[1];
echo "Tref = <input type=\"text\" name=\"Tref\" size=\"5\" value=\"".$Tref."\"> ms ➡ ";
if($Tref > 0) echo "this object is <font color=\"blue\">striated</font> (it has a pivot) and Tref is the period of its reference metronome.<br /><i>Set this value to zero if the object is smooth (no pivot).</i><br />";
else echo "this object is <font color=\"blue\">smooth</font> (it has no pivot)<br />";

$object_quantization = $object_param[$j];
if(intval($object_quantization) == $object_quantization) $object_quantization = intval($object_quantization);
echo "<p>Quantization = <input type=\"text\" name=\"object_param_".$j++."\" size=\"5\" value=\"".$object_quantization."\"> ms<br /><i>Zero means no quantization, i.e. the duration of this object may decrease without limit in fast movements.</i></p>";

$string = $object_param[$j++];
$k = 0;
// echo $string."<br />";
$pivbeg = $string[$k++];
$pivend = $string[$k++];
$pivbegon = $string[$k++];
$pivendoff = $string[$k++];
$pivcent = $string[$k++];
$pivcentonoff = $string[$k++];
echo "<p>PIVOT</p>";
if($Tref > 0) echo "<p>This object has a pivot — it is <i>striated</i> — because Tref > 0 (see above).</p>";
else echo "<p>This object has NO pivot — it is <i>smooth</i> — because Tref = 0<br /><i>This pivot setting is therefore irrelevant.</i></p>";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"0\"";
if($pivbeg == 1) echo " checked";
echo ">Beginning<br />";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"4\"";
if($pivcent == 1) echo " checked";
echo ">Middle<br />";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"1\"";
if($pivend == 1) echo " checked";
echo ">End<br />";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"2\"";
if($pivbegon == 1) echo " checked";
echo ">First NoteOn<br />";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"5\"";
if($pivcentonoff == 1) echo " checked";
echo ">Middle NoteOn/Off<br />";
echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"3\"";
if($pivendoff == 1) echo " checked";
echo ">Last NoteOff<br />";

$okrescale = $string[$k++];
$FixScale = $string[$k++];
$OkExpand = $string[$k++];
$OkCompress = $string[$k++];
$OkRelocate = $string[$k++];
$BreakTempo = $string[$k++];

$ContBeg = $string[$k++];
$ContEnd = $string[$k++];

$CoverBeg = $string[$k++];
$CoverEnd = $string[$k++];
$TruncBeg = $string[$k++];
$TruncEnd = $string[$k++];

$pivspec = $string[$k++];
$AlphaCtrl = $string[$k++];

$RescaleMode = $object_param[$j++];

$AlphaMin = $object_param[$j++];
$AlphaMax = $object_param[$j++];

$DelayMode = $object_param[$j++];
$MaxDelay = $object_param[$j++];
$ForwardMode = $object_param[$j++];
$MaxForward = $object_param[$j++];

$BreakTempoMode = $object_param[$j++];

$x = $object_param[$j++];
$ContBegMode = $object_param[$j++];
$MaxBegGap = $object_param[$j++];
$ContEndMode = $object_param[$j++];
$MaxEndGap = $object_param[$j++];

$CoverBegMode = $object_param[$j++];
$MaxCoverBeg = $object_param[$j++];
$CoverEndMode = $object_param[$j++];
$MaxCoverEnd = $object_param[$j++];
$TruncBegMode = $object_param[$j++];
$MaxTruncBeg = $object_param[$j++];
$TruncEndMode = $object_param[$j++];
$MaxTruncEnd = $object_param[$j++];

$PivMode = $object_param[$j++];
$PivPos = $object_param[$j++];

$AlphaCtrlNr = $object_param[$j++];
$AlphaCtrlChan = $object_param[$j++];

$OkTransp = $object_param[$j++];
$OkArticul = $object_param[$j++];
$OkVolume = $object_param[$j++];
$OkPan = $object_param[$j++];
$OkMap = $object_param[$j++];
$OkVelocity = $object_param[$j++];

$PreRoll = $object_param[$j++];
$PostRoll = $object_param[$j++];
$PreRollMode = $object_param[$j++];
$PostRollMode = $object_param[$j++];

$PeriodMode = $object_param[$j++];
$BeforePeriod = $object_param[$j++];
$ForceIntegerPeriod = $object_param[$j++];
$DiscardNoteOffs = $object_param[$j++];

$StrikeAgain = $object_param[$j++];

$CsoundAssignedInstr = $object_param[$j++];
$CsoundInstr = $object_param[$j++];

$Tpict = $object_param[$j++];
if(!is_numeric($Tpict)) {
	echo "<p style=\"color:red;\">WARNING: you are trying to edit an obsolete version of the ‘-mi’ file. Load and save it again in BP2.9.8!</p>";
	$j -= 4;
	}
$red = $object_param[$j++];
$green = $object_param[$j++];
$blue = $object_param[$j++];

$silence_before_warning = '';
if(isset($_POST['silence_before'])) {
	$PreRoll -= $_POST['SilenceBefore'];
	$silence_before_warning = "<font color=\"blue\">Inserting a silence before the object amounts to adding a negative value to its pre-roll.<br />The duration remains unchanged but the pre-roll is now: ".$PreRoll." ms</font>";
	}

$silence_after_warning = '';
if(isset($_POST['silence_after'])) {
	$PostRoll += $_POST['SilenceAfter'];
	$silence_after_warning = "<font color=\"blue\">Appending a silence after the object amounts to adding a positive value to its post-roll.<br />The duration remains unchanged but the post-roll is now: ".$PostRoll." ms</font>";
	}

echo "<input type=\"radio\" name=\"Pivot_mode\" value=\"18\"";
if($pivspec == 1) echo " checked";
echo ">Set pivot:<br />";
echo "&nbsp;<input type=\"radio\" name=\"PivMode\" value=\"-1\"";
if($pivspec == 1 AND $PivMode == -1) {
	echo " checked";
	$value = intval($PivPos);
	}
else $value = '';
echo ">&nbsp;<input type=\"text\" name=\"PivPos1\" size=\"5\" value=\"".$value."\"> ms from beginning<br />";
echo "&nbsp;<input type=\"radio\" name=\"PivMode\" value=\"0\"";
$value = '';
if($pivspec == 1 AND $PivMode == 0) {
	echo " checked";
	$value = intval($PivPos);
	}
echo ">&nbsp;<input type=\"text\" name=\"PivPos2\" size=\"5\" value=\"".$value."\"> % duration from beginning<br /><br />";


echo "<p>RESCALING</p>";
$value_min = $value_max = $dilation_controller = $dilation_channel = $value_controller = $value_channel = '';
$dilation_ok = FALSE;
if(!$FixScale AND !$OkExpand AND !$OkCompress) {
	$dilation_ok = TRUE;
	if($AlphaMin > 0) $value_min = intval($AlphaMin);
	if($AlphaMax > 0) $value_max = intval($AlphaMax);
	}
	
$scalable = FALSE;
if(($okrescale OR $OkExpand OR $OkCompress OR $dilation_ok) AND !$FixScale) $scalable = TRUE;

echo "<input type=\"radio\" name=\"Rescale\" value=\"okrescale\"";
if($okrescale OR $OkExpand OR $OkCompress) echo " checked";
echo ">OK rescale<br />";
echo "&nbsp;<input type=\"checkbox\" name=\"OkExpand\" value=\"OkExpand\"";
if($OkExpand) echo " checked";
echo ">Expand at will<br />";
echo "&nbsp;<input type=\"checkbox\" name=\"OkCompress\" value=\"OkCompress\"";
if($OkCompress) echo " checked";
echo ">Compress at will<br />";
echo "<input type=\"radio\" name=\"Rescale\" value=\"neverrescale\"";
if($FixScale) echo " checked";
echo ">Never rescale<br />";

echo "<input type=\"radio\" name=\"Rescale\" value=\"dilationrange\"";
if($dilation_ok) echo " checked";
echo ">Dilation ratio range from";
echo "&nbsp;<input type=\"text\" name=\"AlphaMin\" size=\"5\" value=\"".$value_min."\"> to <input type=\"text\" name=\"AlphaMax\" size=\"5\" value=\"".$value_max."\"> %<br />";

$alpha_controller = FALSE;
if($AlphaCtrl AND $AlphaCtrlNr > 0 AND $AlphaCtrlChan > 0) {
	$alpha_controller = TRUE;
	$value_controller = $AlphaCtrlNr;
	$value_channel = $AlphaCtrlChan;
	}
echo "<input type=\"checkbox\" name=\"AlphaCtrl\"";
if($alpha_controller) echo " checked";
echo ">Send dilation ration to controller ";
echo "&nbsp;<input type=\"text\" name=\"AlphaCtrlNr\" size=\"5\" value=\"".$value_controller."\"> channel <input type=\"text\" name=\"AlphaCtrlChan\" size=\"5\" value=\"".$value_channel."\"><br />";

echo "<p>RescaleMode = <input type=\"text\" name=\"RescaleMode\" size=\"5\" value=\"".$RescaleMode."\"> ???</p>";

echo "<p>MIDI CHANGES</p>";

echo "<input type=\"checkbox\" name=\"OkTransp\"";
if($OkTransp) echo " checked";
echo "> Accept transposition<br />";
echo "<input type=\"checkbox\" name=\"OkArticul\"";
if($OkArticul) echo " checked";
echo "> Accept articulation<br />";
echo "<input type=\"checkbox\" name=\"OkVolume\"";
if($OkVolume) echo " checked";
echo "> Accept volume changes<br />";
echo "<input type=\"checkbox\" name=\"OkPan\"";
if($OkPan) echo " checked";
echo "> Accept panoramic changes<br />";
echo "<input type=\"checkbox\" name=\"OkMap\"";
if($OkMap) echo " checked";
echo "> Accept key changes<br />";
echo "<input type=\"checkbox\" name=\"OkVelocity\"";
if($OkVelocity) echo " checked";
echo "> Accept velocity changes<br />";

echo "<p>LOCATION</p>";
echo "<input type=\"radio\" name=\"OkRelocate\" value=\"1\"";
if($OkRelocate == 1) echo " checked";
echo ">Relocate at will<br />";
echo "<input type=\"radio\" name=\"OkRelocate\" value=\"0\"";
if($OkRelocate == 0) echo " checked";
echo ">Do not relocate at will<br /><br />";

echo "<input type=\"radio\" name=\"DelayMode\" value=\"-1\"";
if(!$OkRelocate AND $DelayMode == -1) {
	echo " checked";
	$value = $MaxDelay;
	}
else $value = '';
echo ">Allow delay";
echo "&nbsp;<input type=\"text\" name=\"MaxDelay1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"DelayMode\" value=\"0\"";
if(!$OkRelocate AND $DelayMode == 0) {
	echo " checked";
	$value = $MaxDelay;
	}
else $value = '';
echo ">Allow delay";
echo "&nbsp;<input type=\"text\" name=\"MaxDelay2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<input type=\"radio\" name=\"ForwardMode\" value=\"-1\"";
if(!$OkRelocate AND $ForwardMode == -1) {
	echo " checked";
	$value = $MaxForward;
	}
else $value = '';
echo ">Allow forward";
echo "&nbsp;<input type=\"text\" name=\"MaxForward1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"ForwardMode\" value=\"0\"";
if(!$OkRelocate AND $ForwardMode == 0) {
	echo " checked";
	$value = $MaxForward;
	}
else $value = '';
echo ">Allow forward";
echo "&nbsp;<input type=\"text\" name=\"MaxForward2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>BREAK TEMPO (ORGANUM)</p>";
// echo "(BreakTempoMode = ".$BreakTempoMode." ???)<br />";
echo "<input type=\"hidden\" name=\"BreakTempoMode\" value=\"".$BreakTempoMode."\">";

echo "<input type=\"radio\" name=\"BreakTempo\" value=\"0\"";
if($BreakTempo == 0) echo " checked";
echo ">Never break after this object<br />";
echo "<input type=\"radio\" name=\"BreakTempo\" value=\"1\"";
if($BreakTempo == 1) echo " checked";
echo ">Break at will";

echo "<p>FORCE CONTINUITY (BEGINNING)</p>";
echo "<input type=\"radio\" name=\"ContBeg\" value=\"0\"";
if($ContBeg == 0) echo " checked";
echo ">Do not force<br />";
echo "<input type=\"radio\" name=\"ContBeg\" value=\"1\"";
if($ContBeg == 1) echo " checked";
echo ">Force<br />";

echo "<input type=\"radio\" name=\"ContBegMode\" value=\"-1\"";
if($ContBeg AND $ContBegMode == -1) {
	echo " checked";
	$value = $MaxBegGap;
	}
else $value = '';
echo ">Allow gap";
echo "&nbsp;<input type=\"text\" name=\"MaxBegGap1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"ContBegMode\" value=\"0\"";
if($ContBeg AND $ContBegMode == 0) {
	echo " checked";
	$value = $MaxBegGap;
	}
else $value = '';
echo ">Allow gap";
echo "&nbsp;<input type=\"text\" name=\"MaxBegGap2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>FORCE CONTINUITY (END)</p>";
echo "<input type=\"radio\" name=\"ContEnd\" value=\"0\"";
if($ContEnd == 0) echo " checked";
echo ">Do not force<br />";
echo "<input type=\"radio\" name=\"ContEnd\" value=\"1\"";
if($ContEnd == 1) echo " checked";
echo ">Force<br />";

echo "<input type=\"radio\" name=\"ContEndMode\" value=\"-1\"";
if($ContEnd AND $ContEndMode == -1) {
	echo " checked";
	$value = $MaxEndGap;
	}
else $value = '';
echo ">Allow gap";
echo "&nbsp;<input type=\"text\" name=\"MaxEndGap1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"ContEndMode\" value=\"0\"";
if($ContEnd AND $ContEndMode == 0) {
	echo " checked";
	$value = $MaxEndGap;
	}
else $value = '';
echo ">Allow gap";
echo "&nbsp;<input type=\"text\" name=\"MaxEndGap2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>COVER BEGINNING</p>";
echo "<input type=\"radio\" name=\"CoverBeg\" value=\"1\"";
if($CoverBeg == 1) echo " checked";
echo ">Cover at will<br />";
echo "<input type=\"radio\" name=\"CoverBeg\" value=\"0\"";
if($CoverBeg == 0) echo " checked";
echo ">Never cover<br />";
echo "<input type=\"radio\" name=\"CoverBegMode\" value=\"-1\"";
if(!$CoverBeg AND $CoverBegMode == -1) {
	echo " checked";
	$value = $MaxCoverBeg;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxCoverBeg1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"CoverBegMode\" value=\"0\"";
if(!$CoverBeg AND $CoverBegMode == 0) {
	echo " checked";
	$value = $MaxCoverBeg;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxCoverBeg2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>COVER END</p>";
echo "<input type=\"radio\" name=\"CoverEnd\" value=\"1\"";
if($CoverEnd == 1) echo " checked";
echo ">Cover at will<br />";
echo "<input type=\"radio\" name=\"CoverEnd\" value=\"0\"";
if($CoverEnd == 0) echo " checked";
echo ">Never cover<br />";
echo "<input type=\"radio\" name=\"CoverEndMode\" value=\"-1\"";
if(!$CoverEnd AND $CoverEndMode == -1) {
	echo " checked";
	$value = $MaxCoverEnd;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxCoverEnd1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"CoverEndMode\" value=\"0\"";
if(!$CoverEnd AND $CoverEndMode == 0) {
	echo " checked";
	$value = $MaxCoverEnd;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxCoverEnd2\" size=\"5\" value=\"".$value."\"> % of duration";
	
echo "<p>TRUNCATE BEGINNING</p>";
echo "<input type=\"radio\" name=\"TruncBeg\" value=\"1\"";
if($TruncBeg == 1) echo " checked";
echo ">Truncate at will<br />";
echo "<input type=\"radio\" name=\"TruncBeg\" value=\"0\"";
if($TruncBeg == 0) echo " checked";
echo ">Do not truncate<br />";

echo "<input type=\"radio\" name=\"TruncBegMode\" value=\"-1\"";
if(!$TruncBeg AND $TruncBegMode == -1) {
	echo " checked";
	$value = $MaxTruncBeg;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxTruncBeg1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"TruncBegMode\" value=\"0\"";
if(!$TruncBeg AND $TruncBegMode == 0) {
	echo " checked";
	$value = $MaxTruncBeg;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxTruncBeg2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>TRUNCATE END</p>";
echo "<input type=\"radio\" name=\"TruncEnd\" value=\"1\"";
if($TruncEnd == 1) echo " checked";
echo ">Truncate at will<br />";
echo "<input type=\"radio\" name=\"TruncEnd\" value=\"0\"";
if($TruncEnd == 0) echo " checked";
echo ">Do not truncate<br />";

echo "<input type=\"radio\" name=\"TruncEndMode\" value=\"-1\"";
if(!$TruncEnd AND $TruncEndMode == -1) {
	echo " checked";
	$value = $MaxTruncEnd;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxTruncEnd1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"TruncEndMode\" value=\"0\"";
if(!$TruncEnd AND $TruncEndMode == 0) {
	echo " checked";
	$value = $MaxTruncEnd;
	}
else $value = '';
echo ">Not more than";
echo "&nbsp;<input type=\"text\" name=\"MaxTruncEnd2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>PREROLL - POSTROLL";
echo "<input type=\"radio\" name=\"PreRollMode\" value=\"-1\"";
if($PreRollMode == -1) {
	echo " checked";
	$value = $PreRoll;
	}
else $value = '';
echo ">Pre-roll";
echo "&nbsp;<input type=\"text\" name=\"PreRoll1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"PreRollMode\" value=\"0\"";
if($PreRollMode == 0) {
	echo " checked";
	$value = $PreRoll;
	}
else $value = '';
echo ">Pre-roll";
echo "&nbsp;<input type=\"text\" name=\"PreRoll2\" size=\"5\" value=\"".$value."\"> % of duration<br />";
	
echo "<input type=\"radio\" name=\"PostRollMode\" value=\"-1\"";
if($PostRollMode == -1) {
	echo " checked";
	$value = $PostRoll;
	}
else $value = '';
echo ">Post-roll";
echo "&nbsp;<input type=\"text\" name=\"PostRoll1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"PostRollMode\" value=\"0\"";
if($PostRollMode == 0) {
	echo " checked";
	$value = $PostRoll;
	}
else $value = '';
echo ">Post-roll";
echo "&nbsp;<input type=\"text\" name=\"PostRoll2\" size=\"5\" value=\"".$value."\"> % of duration";

echo "<p>CYCLIC</p>";
echo "<input type=\"radio\" name=\"PeriodMode\" value=\"-2\"";
if($PeriodMode == -2) {
	echo " checked";
	}
echo ">No period<br />";
echo "<input type=\"radio\" name=\"PeriodMode\" value=\"-1\"";
if($PeriodMode == -1) {
	echo " checked";
	$value = $BeforePeriod;
	}
else $value = '';
echo ">Periodical after";
echo "&nbsp;<input type=\"text\" name=\"BeforePeriod1\" size=\"5\" value=\"".$value."\"> ms<br />";
echo "<input type=\"radio\" name=\"PeriodMode\" value=\"0\"";
if($PeriodMode == 0) {
	echo " checked";
	$value = $BeforePeriod;
	}
else $value = '';
echo ">Periodical after";
echo "&nbsp;<input type=\"text\" name=\"BeforePeriod2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<input type=\"checkbox\" name=\"ForceIntegerPeriod\"";
if($ForceIntegerPeriod) echo " checked";
echo ">Force integer number of periods<br />";
echo "<input type=\"checkbox\" name=\"DiscardNoteOffs\"";
if($DiscardNoteOffs) echo " checked";
echo ">Discard NoteOff’s except in last period";

echo "<p>STRIKE MODE</p>";
echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"1\"";
if($StrikeAgain == 1) echo " checked";
echo ">Strike again NoteOn’s<br />";
echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"0\"";
if($StrikeAgain == 0) echo " checked";
echo ">Don’t strike again NoteOn’s<br />";
echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"-1\"";
if($StrikeAgain == -1) echo " checked";
echo ">Strike NoteOn’s according to default";

echo "<p>MIDI TO CSOUND CONVERSION</p>";
echo "<input type=\"radio\" name=\"CsoundAssignedInstr\" value=\"0\"";
if($CsoundAssignedInstr == 0) echo " checked";
echo ">Force to current instrument<br />";
echo "<input type=\"radio\" name=\"CsoundAssignedInstr\" value=\"-1\"";
if($CsoundAssignedInstr == -1 AND $CsoundInstr == -1) echo " checked";
echo ">Do not change instrument<br />";
echo "<input type=\"radio\" name=\"CsoundAssignedInstr\" value=\"-1\"";
if($CsoundAssignedInstr == -1 AND $CsoundInstr <> -1) {
	echo " checked";
	$value = $CsoundInstr;
	}
else $value = '';
echo ">Force to instrument";
echo "&nbsp;<input type=\"text\" name=\"CsoundInstr\" size=\"5\" value=\"".$value."\"><br />";

echo "<small><p>Tpict = ".$Tpict." ???</p></small>";
echo "<input type=\"hidden\" name=\"Tpict\" value=\"".$Tpict."\">";

echo "<p>CSOUND SCORE</p>";
echo "<input type=\"hidden\" name=\"object_param_".$j."\" value=\"".$object_param[$j++]."\">";
$text = str_ireplace("<HTML>",'',$object_param[$j]);
$text = str_ireplace("</HTML>",'',$text);
$text = str_ireplace("<BR>","\n",$text);
echo "<textarea name=\"object_param_".($j++)."\" rows=\"20\" style=\"width:700px; background-color:Cornsilk;\">".$text."</textarea><br />";
echo "<input type=\"hidden\" name=\"object_param_".$j."\" value=\"".$object_param[$j++]."\">";

$kmax = 0;
$new_midi = FALSE;
if(count($midi_text_bytes) > 0) $new_midi = TRUE;
else {
	$all_bytes = @file_get_contents($midi_bytes,TRUE);
	$table_bytes = explode(chr(10),$all_bytes);
	$midi_text_bytes = array();
	for($k = 1; $k < count($table_bytes); $k++) {
		$byte = trim($table_bytes[$k]);
		if($byte == '') break;
		$midi_text_bytes[$k-1] = $byte;
		}
	}
$kmax = count($midi_text_bytes);
// echo "kmax = ".$kmax."<br />";

if(isset($_POST['suppress_allnotes_off'])) {
	$new_midi_code = array();
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		if($code >= 176 AND $code < 192) {
			$ctrl = $midi_text_bytes[$k+1] % 256;
			if($ctrl == 123) $k += 2;
			else $new_midi_code[] = $byte;
			}
		else $new_midi_code[] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	}

if(isset($_POST['add_allnotes_off'])) {
	$new_midi_code = array();
	$time_max = 0;
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		$time = ($byte - $code) / 256;
		if($time > $time_max) $time_max = $time;
		$new_midi_code[$k] = $byte;
		}
	for($channel = 0; $channel < 16; $channel++) {
		$code = 176 + $channel;
		$byte = $code + (256 * $time_max);
	//	echo $channel." -> ".$byte."<br />";
		$midi_text_bytes[$k++] = $byte;
		$code = 123;
		$byte = $code + (256 * $time_max);
		$new_midi_code[$k++] = $byte;
		$code = 0;
		$byte = $code + (256 * $time_max);
		$new_midi_code[$k++] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	}

if(isset($_POST['quantize_NoteOn'])) {
	$test = TRUE;
	$NoteOnQuantize = intval($_POST['NoteOnQuantize']);
	if($NoteOnQuantize > 0) {
		$step = $Tref / $NoteOnQuantize;
		$new_midi_code = array();
		for($k = 0; $k < $kmax; $k++) {
			$byte = $midi_text_bytes[$k];
			$code = $byte % 256;
			$time = ($byte - $code) / 256;
			if($code >= 128 AND $code < 160) { // NoteOn or NoteOff
				$frames = intval($time / $step);
				if($test) echo $time."ms -> ".$frames." frames<br />";
				$time_this_event = round($frames * $step);
				$byte = $code + (256 * $time_this_event);
				if($test) echo "-> ".$time_this_event."ms -> ".$byte."<br />";
				$new_midi_code[] = $byte;
				$byte = $midi_text_bytes[++$k];
				$code = $byte % 256;
				$byte = $code + (256 * $time_this_event);
				if($test) echo "-> ".$time_this_event."ms -> ".$byte."<br />";
				$new_midi_code[] = $byte;
				$byte = $midi_text_bytes[++$k];
				$code = $byte % 256;
				$byte = $code + (256 * $time_this_event);
				if($test) echo "-> ".$time_this_event."ms -> ".$byte."<br />";
				$new_midi_code[] = $byte;
				}
			else {
				if($test) echo "-> ".$byte."<br />";
				$new_midi_code[] = $byte;
				}
			}
		$midi_text_bytes = array();
		$handle_bytes = fopen($midi_bytes,"w");
		fwrite($handle_bytes,$kmax."\n");
		for($k = 0; $k < $kmax; $k++) {
			$byte = $new_midi_code[$k];
			fwrite($handle_bytes,$byte."\n");
			$midi_text_bytes[$k] = $byte;
			}
		fclose($handle_bytes);
		}
	}

$flatten_all = FALSE;
if(isset($_POST['adjust_duration']) OR isset($_POST['adjust_beats'])) {
	$NewDuration = intval($_POST['NewDuration']);
	$NewBeats = $_POST['NewBeats'];
	if(isset($_POST['adjust_duration']) AND $NewDuration == 0) $flatten_all = TRUE;
	if(isset($_POST['adjust_beats']) AND $NewBeats == 0) $flatten_all = TRUE;
	}
	
if($flatten_all OR isset($_POST['suppress_pressure'])) {
	$new_midi_code = array();
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		if($code >= 208 AND $code < 224) {
			$k += 1;
			}
		else $new_midi_code[] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	// unlink($midi_text);
	}

if($flatten_all OR isset($_POST['suppress_pitchbend'])) {
	$new_midi_code = array();
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		if($code >= 224 AND $code < 240) {
			$k += 2;
			}
		else $new_midi_code[] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	//unlink($midi_text);
	}

if($flatten_all OR isset($_POST['suppress_polyphonic_pressure'])) {
	$new_midi_code = array();
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		if($code >= 160 AND $code < 176) {
			$k += 2;
			}
		else $new_midi_code[] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	//unlink($midi_text);
	}

if($flatten_all OR isset($_POST['suppress_volume'])) {
	$new_midi_code = array();
	for($k = 0; $k < $kmax; $k++) {
		$byte = $midi_text_bytes[$k];
		$code = $byte % 256;
		$time = ($byte - $code) / 256;
		if($code >= 176 AND $code < 192) {
			$ctrl = $midi_text_bytes[$k + 1];
			$ctrl = $ctrl % 256;
			if($ctrl == 7) $k += 3;
			else $new_midi_code[] = $byte;
			}
		else $new_midi_code[] = $byte;
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
	//unlink($midi_text);
	}

$duration_warning = '';
$change_beats = FALSE;
if(isset($_POST['adjust_beats'])) {
	$NewBeats = $_POST['NewBeats'];
	$NewDuration = intval($Tref * $NewBeats);
	$change_beats = TRUE;
	}
if($change_beats OR isset($_POST['adjust_duration'])) {
	if(!$change_beats) $NewDuration = intval($_POST['NewDuration']);
	$Duration = intval($_POST['Duration']);
	if($Duration > 0) {
		$alpha = $NewDuration / $Duration;
		$new_midi_code = array();
		for($k = 0; $k < $kmax; $k++) {
			$byte = $midi_text_bytes[$k];
			$code = $byte % 256;
			$time = ($byte - $code) / 256;
			$newtime = intval($alpha * $time); 
			$new_midi_code[$k] = $code + (256 * $newtime);
			}
		}
	else {
		$duration_warning = "<p style=\"color:red;\">Check ‘explicit MIDI codes’ because the preceding duration was equal to zero.</p>";
		$kmax = count($midi_text_bytes);
		$number_notes = 0;
		for($k = 0; $k < $kmax; $k++) {
			$byte = $midi_text_bytes[$k];
			$code = $byte % 256;
			if($code >= 128 AND $code < 160) $number_notes++;
			// NoteOn or NoteOff
			}
		if($number_notes > 1)
			$step = $NewDuration / ($number_notes);
		else $step = 0;
		$new_midi_code = array();
		$newtime = 0;
		for($k = 0; $k < $kmax; $k++) {
			$byte = $midi_text_bytes[$k];
			$code = $byte % 256;
			if($code >= 128 AND $code < 160) $newtime += $step;
			$new_midi_code[$k] = $code + (256 * intval($newtime));
			}
		}
	$kmax = count($new_midi_code);
	$midi_text_bytes = array();
	$handle_bytes = fopen($midi_bytes,"w");
	fwrite($handle_bytes,$kmax."\n");
	for($k = 0; $k < $kmax; $k++) {
		$byte = $new_midi_code[$k];
		fwrite($handle_bytes,$byte."\n");
		$midi_text_bytes[$k] = $byte;
		}
	fclose($handle_bytes);
//	unlink($midi_text);
	$Duration = $NewDuration;
	}

$handle_text = fopen($midi_text,"w");
if($new_midi) $handle_bytes = fopen($midi_bytes,"w");
$more = 0; $code_line = '';
if($new_midi) fwrite($handle_bytes,$kmax."\n");
$time_max = 0;
for($k = 0; $k < $kmax; $k++) {
	$byte = $midi_text_bytes[$k];
	if($new_midi) fwrite($handle_bytes,$byte."\n");
	$code = $byte % 256;
	$time = ($byte - $code) / 256;
	if($time > $time_max) $time_max = $time;
//	echo "(".$time.") ".$code."<br />";
	if($code >= 144 AND $code < 160) {
		$channel = $code - 144 + 1;
		$byte = $midi_text_bytes[$k + 2];
		$velocity = $byte % 256;
		if($velocity > 0)
			$code_line = $time." (ch ".$channel.") NoteOn ";
		else
			$code_line = $time." (ch ".$channel.") NoteOff ";
		$more = 2;
		}
	else if($code >= 128 AND $code < 144) {
		$channel = $code - 128 + 1;
		$code_line = $time." (ch ".$channel.") NoteOff ";
		$more = 2;
		}
	else if($code >= 160 AND $code < 176) {
		$channel = $code - 160 + 1;
		$code_line = $time." (ch ".$channel.") Poly pressure key ";
		$more = 2;
		}
	else if($code >= 176 AND $code < 192) {
		$channel = $code - 176 + 1;
		$ctrl = $midi_text_bytes[$k + 1];
		$ctrl = $ctrl % 256;
		$code_line = $time." (ch ".$channel.") Parameter ctrl ";
		if($ctrl == 123)
			$code_line = $time." (ch ".$channel.") AllNotesOff ";
		if($ctrl > 64)
			$more = 2; // 7-bit controller/switch
		else $more = 3; // 14-bit controller/switch
		}
	else if($code >= 208 AND $code < 224) {
		$channel = $code - 208 + 1;
		$code_line = $time." (ch ".$channel.") Channel pressure ";
		$more = 1;
		}
	else if($code >= 224 AND $code < 240) {
		$channel = $code - 224 + 1;
		$code_line = $time." (ch ".$channel.") Pitchbend ";
		$more = 2;
		}
	else if($code >= 192 AND $code < 208) {
		$channel = $code - 208 + 1;
		$code_line = $time." (ch ".$channel.") Prog change ";
		$more = 1;
		}
	else {
		$more--;
		if($more == 0) {
			$code_line .= $code." ";
			fwrite($handle_text,$code_line."\n");
			}
		else $code_line .= $code." ";
		}
	}
fclose($handle_text);
if($new_midi) fclose($handle_bytes);
$Duration = $time_max;
	
echo "<input type=\"hidden\" name=\"jmax\" value=\"".$j."\">";
echo "<p>MIDI CODES</p>";
/* echo "<p>Object duration = ".$Duration." ms";
if($Tref > 0) echo " = ".($Duration/$Tref)." beats (striated object)";
echo "</p>"; */

if(file_exists($midi_text)) {
	$text_link = "/".str_replace($root,'',$midi_text);
	$bytes_link = "/".str_replace($root,'',$midi_bytes);
/*	echo "midi_text = ".$midi_text."<br />";
	echo "midi_bytes = ".$midi_bytes."<br />";
	echo "text_link = ".$text_link."<br />";
	echo "bytes_link = ".$bytes_link."<br />"; */
	
//	$text_link = str_replace("/Applications/MAMP/htdocs",'',$midi_text);
	
	echo "• <a onclick=\"window.open('".$text_link."','MIDItext','width=300,height=300'); return false;\" href=\"".$text_link."\">EXPLICIT MIDI codes</a><br />• <a onclick=\"window.open('".$bytes_link."','MIDIbytes','width=300,height=500'); return false;\" href=\"".$bytes_link."\">TIME-STAMPED MIDI bytes</a>";
	if($new_midi) echo " ... <font color=\"blue\">from the file you have just loaded</font>";
	echo "<br />";
echo "<br /><i>If changes are not visible on these pop-up windows, juste clear the cache!</i><br />";
	}
else "No codes in this sound-object prototype<br />";

echo "<p>DURATION</p>";
$real_duration = $Duration - $PreRoll + $PostRoll;
echo "Real duration of this object will be:<br /><b>event duration - pre-roll + post-roll</b> = ".$Duration." - (".$PreRoll.") + (".$PostRoll.") = ".$real_duration." ms<br />for a metronome period Tref = ".$Tref." ms";


if($duration_warning <> '') echo $duration_warning;
echo "<input type=\"hidden\" name=\"Duration\" value=\"".$Duration."\">";
echo "<p><input style=\"background-color:azure;\" type=\"submit\" name=\"adjust_duration\" value=\"Adjust event time duration\"> to <input type=\"text\" name=\"NewDuration\" size=\"8\" value=\"".$Duration."\"> ms<br />";
if($Tref > 0) echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"adjust_beats\" value=\"Adjust event beat duration\"> to <input type=\"text\" name=\"NewBeats\" size=\"8\" value=\"".($Duration/$Tref)."\"> beats (striated object with Tref = ".$Tref." ms)";
echo "</p>";

if($silence_before_warning <> '') echo "<font color=\"red\">➡</font> ".$silence_before_warning."<br />";
echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"silence_before\" value=\"Insert silence before this object\"> = <input type=\"text\" name=\"SilenceBefore\" size=\"8\" value=\"\"> ms ➡ current pre-roll = ".$PreRoll." ms<br />";

if($silence_after_warning <> '') echo "<font color=\"red\">➡</font> ".$silence_after_warning."<br />";
echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"silence_after\" value=\"Append silence after this object\"> = <input type=\"text\" name=\"SilenceAfter\" size=\"8\" value=\"\"> ms ➡ current post-roll = ".$PostRoll." ms<br /><br />";

if($new_midi) {
	echo "<p style=\"color:red;\">You should save this prototype to preserve uploaded MIDI codes! ➡ <input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisprototype\" value=\"SAVE IT\">&nbsp;<input style=\"background-color:azure;\" type=\"submit\" name=\"cancel\" value=\"CANCEL\"></p>";
	}
echo "<font color=\"red\">➡</font> Create or replace MIDI codes loading a MIDI file (*.mid): <input type=\"file\" name=\"mid_upload\">&nbsp;<input type=\"submit\" value=\" send \">";
if(!$new_midi) {
	echo "<p style=\"text-align:left;\"><input style=\"background-color:azure;\" type=\"submit\" name=\"suppress_pressure\" value=\"SUPPRESS channel pressure\">&nbsp;<input style=\"background-color:azure;\" type=\"submit\" name=\"suppress_polyphonic_pressure\" value=\"SUPPRESS polyphonic pressure\">&nbsp;<input style=\"background-color:azure;\" type=\"submit\" name=\"suppress_pitchbend\" value=\"SUPPRESS pitchbend\">&nbsp;<input style=\"background-color:azure;\" type=\"submit\" name=\"suppress_volume\" value=\"SUPPRESS volume control\"><br />";
	echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"add_allnotes_off\" value=\"Append AllNotesOff (all channels)\">&nbsp;<input style=\"background-color:azure;\" type=\"submit\" name=\"suppress_allnotes_off\" value=\"Suppress AllNotesOff (all channels)\"><br />";
	
	echo "<input style=\"background-color:azure;\" type=\"submit\" name=\"quantize_NoteOn\" value=\"Quantize NoteOns\"> = 1 / <input type=\"text\" name=\"NoteOnQuantize\" size=\"4\" value=\"64\"> beat";
	
	echo "<p style=\"text-align:center;\"><i>I am working on the “convert MIDI to Csound” procedure…</i></p>";
	echo "<p style=\"text-align:center;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisprototype\" value=\"SAVE THIS PROTOTYPE\">&nbsp;<big> = <b><font color=\"red\">".$object_name."</font></b></big></p>";
	}
echo "</form>";
?>