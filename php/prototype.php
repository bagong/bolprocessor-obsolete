<?php
require_once("_basic_tasks.php");
$path = getcwd();
$url_this_page = $path."/prototype.php";

if(isset($_POST['object_name'])) {
	$object_name = $_POST['object_name'];
	$temp_folder = $_POST['temp_folder'];
	$object_file = $_POST['object_file'];
//	echo $root."<br />";
//	echo "<p>Work directory: <font color=\"blue\">".str_replace($root,'',$temp_folder)."</font></p>";
	}
else {
	"Sound-object prototype's name is not known. First open the ‘-mi’ file!"; die();
	}
	
$this_title = $object_name;
require_once("_header.php");

echo "<p>Object file: <font color=\"blue\">".str_replace($root,'',$object_file)."</font>";


if(isset($_POST['savethisprototype'])) {
	echo "<span id=\"timespan\">&nbsp;&nbsp;<font color=\"red\">Saved this file…</font></span>";
//	$prototype_file = $temp_folder."/"."test.txt";
	$prototype_file = $object_file;
	$handle = fopen($prototype_file,"w");
	$source_file = $_POST['source_file'];
	$file_header = $top_header."\n// Object prototype saved as \"".$object_name."\". Date: ".gmdate('Y-m-d H:i:s');
	$file_header .= "\n".$source_file;
	fwrite($handle,$file_header."\n");
	$object_type = 0;
	if(isset($_POST['object_type1'])) $object_type += 1;
	if(isset($_POST['object_type4'])) $object_type += 4;
	// echo "object_type = ".$object_type."<br />";
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
	// echo "StrikeAgain = ".$StrikeAgain."<br />";
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
/*	$table = explode('/',$temp_folder);
	$temp_folder_name = end($table);
	$dir = str_replace($temp_folder_name,'',$temp_folder);
	echo "dir = ".$dir."<br />";
	echo "source_file = ".$source_file."<br />";
	echo "temp_folder = ".$temp_folder."<br />";
	SaveObjectPrototypes(TRUE,$dir,$source_file,$temp_folder); */
	}

echo "</p>";
echo link_to_help();

echo "<h2>Object prototype <big><font color=\"red\">".$object_name."</font></big></h2>";

$content = file_get_contents($object_file,TRUE);
$pick_up_headers = pick_up_headers($content);
$source_file = $pick_up_headers['objects'];
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."<br />// Source: ".$source_file."</p>";
$content = $pick_up_headers['content'];
// echo str_replace("\n","<br />",$content);

$table = explode(chr(10),$content);
$object_param = array();
// $cs_score = '';
$i = 0;
$j = 0; $cscore = FALSE;
do {
	$i++; $line = $table[$i];
	if(is_integer($pos=strpos($line,"_beginCsoundScore_"))) $cscore = TRUE;
	if(is_integer($pos=strpos($line,"_endCsoundScore_"))) $cscore = FALSE;
	if(!$cscore AND is_integer($pos=strpos($line,"<HTML>"))) break;
	$object_param[$j++] = $line;
	continue;
	}
while(TRUE);
$clean_line = str_replace("<HTML>",'',$line);
$clean_line = str_replace("</HTML>",'',$clean_line);
$object_comment = $clean_line;

// ---------- EDIT THIS PROTOTYPE ------------


echo "<form method=\"post\" action=\"prototype.php\" enctype=\"multipart/form-data\">";

echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisprototype\" value=\"SAVE THIS PROTOTYPE “".$object_name."”\"></p>";

echo "<input type=\"hidden\" name=\"object_name\" value=\"".$object_name."\">";
echo "<input type=\"hidden\" name=\"temp_folder\" value=\"".$temp_folder."\">";
echo "<input type=\"hidden\" name=\"object_file\" value=\"".$object_file."\">";
echo "<input type=\"hidden\" name=\"source_file\" value=\"".$source_file."\">";

$size = strlen($object_comment);
echo "Comment on this prototype = <input type=\"text\" name=\"object_comment\" size=\"".$size."\" value=\"".$object_comment."\"><br /><br />";
echo "OBJECT TYPE:<br/>";
$j = 0;
$object_type = $object_param[$j++];
echo "<input type=\"checkbox\" name=\"object_type1\"";
   if($object_type == 1 OR $object_type == 5) echo " checked";
   echo "> MIDI sequence<br />";
echo "<input type=\"checkbox\" name=\"object_type4\"";
   if($object_type > 3) echo " checked";
   echo "> Csound score<br /><br />";
   
// echo "Resolution = ".$object_param[$j]." ms<br />";

$resolution = $object_param[$j];
if($resolution == '' OR $resolution == 0) $resolution = 1;
$resolution = intval($resolution);
echo "Resolution = <input type=\"text\" name=\"object_param_".($j++)."\" size=\"5\" value=\"".$resolution."\"> ms<br />";

echo "Default channel = <input type=\"text\" name=\"object_param_".$j."\" size=\"5\" value=\"".$object_param[$j++]."\"><br />";

$Tref = $object_param[$j++] * $object_param[1];
echo "Tref = <input type=\"text\" name=\"Tref\" size=\"5\" value=\"".$Tref."\"> ms ➡ this is NOT the duration!<br />";

$object_quantization = $object_param[$j];
if(intval($object_quantization) == $object_quantization) $object_quantization = intval($object_quantization);
echo "Quantization = <input type=\"text\" name=\"object_param_".$j++."\" size=\"5\" value=\"".$object_quantization."\"> ms  ➡ zero means no quantization<br />";

$string = $object_param[$j++];
$k = 0;
// echo $string."<br />";
$pivbeg = $string[$k++];
$pivend = $string[$k++];
$pivbegon = $string[$k++];
$pivendoff = $string[$k++];
$pivcent = $string[$k++];
$pivcentonoff = $string[$k++];
echo "<p>PIVOT<br />";
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
$red = $object_param[$j++];
$green = $object_param[$j++];
$blue = $object_param[$j++];


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


echo "DURATION<br />";
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

echo "MIDI CHANGES<br />";

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


echo "<br />LOCATION<br />";
// echo "OkRelocate = ".$OkRelocate."<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxForward2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<br />BREAK TEMPO (ORGANUM)<br />";
echo "(BreakTempoMode = ".$BreakTempoMode." ???)<br />";
echo "<input type=\"hidden\" name=\"BreakTempoMode\" value=\"".$BreakTempoMode."\">";

echo "<input type=\"radio\" name=\"BreakTempo\" value=\"0\"";
if($BreakTempo == 0) echo " checked";
echo ">Never break after this object<br />";
echo "<input type=\"radio\" name=\"BreakTempo\" value=\"1\"";
if($BreakTempo == 1) echo " checked";
echo ">Break at will<br />";

echo "<br /><br />FORCE CONTINUITY (BEGINNING)<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxBegGap2\" size=\"5\" value=\"".$value."\"> % of duration<br />";


echo "<br /><br />FORCE CONTINUITY (END)<br />";
echo "<input type=\"radio\" name=\"ContEnd\" value=\"0\"";
if($ContEnd == 0) echo " checked";
echo ">Do not force<br />";
echo "<input type=\"radio\" name=\"ContEnd\" value=\"1\"";
if($ContEnd == 1) echo " checked";
echo ">Force<br />";
// echo "ContEndMode = ".$ContEndMode."<br />";

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
echo "&nbsp;<input type=\"text\" name=\"MaxEndGap2\" size=\"5\" value=\"".$value."\"> % of duration<br />";



echo "<br /><br />COVER BEGINNING<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxCoverBeg2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<br /><br />COVER END<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxCoverEnd2\" size=\"5\" value=\"".$value."\"> % of duration<br />";
	
echo "<br /><br />TRUNCATE BEGINNING<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxTruncBeg2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<br /><br />TRUNCATE END<br />";
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
echo "&nbsp;<input type=\"text\" name=\"MaxTruncEnd2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<br /><br />PREROLL - POSTROLL<br />";
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
echo "&nbsp;<input type=\"text\" name=\"PostRoll2\" size=\"5\" value=\"".$value."\"> % of duration<br />";

echo "<br /><br />PERIOD<br />";
// echo "PeriodMode = ".$PeriodMode."<br />";

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
echo ">Discard NoteOff’s except in last period<br />";


echo "<br /><br />STRIKE MODE<br />";
// echo "StrikeAgain = ".$StrikeAgain."<br />";

echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"1\"";
if($StrikeAgain == 1) echo " checked";
echo ">Strike again NoteOn’s<br />";
echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"0\"";
if($StrikeAgain == 0) echo " checked";
echo ">Don’t strike again NoteOn’s<br />";
echo "<input type=\"radio\" name=\"StrikeAgain\" value=\"-1\"";
if($StrikeAgain == -1) echo " checked";
echo ">Strike NoteOn’s according to default<br />";

echo "<br /><br />MIDI TO CSOUND CONVERSION<br />";
// echo "CsoundAssignedInstr = ".$CsoundAssignedInstr."<br />";
// echo "CsoundInstr = ".$CsoundInstr."<br />";

echo "<input type=\"radio\" name=\"CsoundAssignedInstr\" value=\"0\"";
if($CsoundAssignedInstr == 0) echo " checked";
echo ">Force to current instrument<br />";
echo "<input type=\"radio\" name=\"CsoundAssignedInstr\" value=\"-1\"";
// if($CsoundAssignedInstr == -1 OR $CsoundInstr == -1) echo " checked";
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

echo "<p>Tpict = ".$Tpict." ???</p>";
echo "<input type=\"hidden\" name=\"Tpict\" value=\"".$Tpict."\">";

echo "<p>CSOUND SCORE:</p>";
echo "<input type=\"hidden\" name=\"object_param_".$j."\" value=\"".$object_param[$j++]."\">";
$text = str_replace("<HTML>",'',$object_param[$j]);
$text = str_replace("</HTML>",'',$text);
$text = str_replace("<BR>","\n",$text);
echo "<textarea name=\"object_param_".($j++)."\" rows=\"20\" style=\"width:700px; background-color:Cornsilk;\">".$text."</textarea><br />";
// echo $j.") ".$object_param[$j]."<br />";
echo "<input type=\"hidden\" name=\"object_param_".$j."\" value=\"".$object_param[$j++]."\">";

$kmax = $object_param[$j];
// echo $j.") kmax = ".$kmax."<br />";
echo "<input type=\"hidden\" name=\"object_param_".$j++."\" value=\"".$kmax."\">";

echo "<p>MIDI CODES:<br />➡ <i>Later I will add the “convert MIDI to Csound” and the “import MIDI file” procedures…</i></p>";
echo "<p>";
echo "<font color=\"blue\">";
for($k = 0; $k < $kmax; $k++) {
	$code = $object_param[$j];
	echo "<input type=\"hidden\" name=\"object_param_".$j++."\" value=\"".$code."\">";
	$code = $code % 256;
	$code = str_replace("144","&nbsp;NoteOn",$code);
	$code = str_replace("208","&nbsp;ChPress =",$code);
	echo $code." ";
	}
echo "<input type=\"hidden\" name=\"jmax\" value=\"".$j."\">";
echo "</font>";
echo "</p>";

echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisprototype\" value=\"SAVE THIS PROTOTYPE “".$object_name."”\"></p>";
echo "</form>";
?>