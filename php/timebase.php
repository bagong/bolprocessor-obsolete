<?php
require_once("_basic_tasks.php");

define('MAXFILESIZE',1000000);
if(isset($_GET['file'])) $file = urldecode($_GET['file']);
else $file = '';
if($file == '') die();
$url_this_page = "timebase.php?file=".urlencode($file);
$table = explode(SLASH,$file);
$filename = end($table);
$this_file = "..".SLASH.$file;
$dir = str_replace($filename,'',$this_file);

require_once("_header.php");
echo "<p>Current directory = ".$dir."</p>";
echo link_to_help();

$temp_folder = str_replace(' ','_',$filename)."_".session_id()."_temp";
// echo "temp_folder = ".$temp_folder."<br />";
if(!file_exists($dir.$temp_folder)) {
	mkdir($dir.$temp_folder);
	}

echo "<h3>Time base file “".$filename."”</h3>";

/* if(isset($_POST['savethisfile'])) {
	echo "<p id=\"timespan\" style=\"color:red;\">Saved file…</p>";
	$content = $_POST['thistext'];
	$handle = fopen($this_file,"w");
	$file_header = $top_header."\n// Time base file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	fwrite($handle,$content);
	fclose($handle);
	} */

if(isset($_POST['savealldata']) OR isset($_POST['addtrack'])) {
	$new_track = isset($_POST['addtrack']);
	echo "<p id=\"timespan\" style=\"color:red;\">Saved all data…</p>";
//	$handle = fopen($dir."-tb.test.txt","w");
	$handle = fopen($this_file,"w");
	$file_header = $top_header."\n// Time base file saved as \"".$filename."\". Date: ".gmdate('Y-m-d H:i:s');
	fwrite($handle,$file_header."\n");
	$maxticks = $_POST['maxticks'];
	if($new_track) {
		$i_cycle = $maxticks;
		$_POST['TickKey_'.$i_cycle] = 60;
		$_POST['TickChannel_'.$i_cycle] = 1;
		$_POST['TickVelocity_'.$i_cycle] = 64;
		$_POST['TickCycle_'.$i_cycle] = 4;
		$_POST['Ptick_'.$i_cycle] = 1;
		$_POST['Qtick_'.$i_cycle] = 1;
		$_POST['TickDuration_'.$i_cycle] = 50;
		$maxticks++;
		}
	$maxbeats = $_POST['maxbeats'];
	fwrite($handle,$maxticks."\n");
	fwrite($handle,$maxbeats."\n");
	for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
		fwrite($handle,"1\n"); // Obsolete variable
		fwrite($handle,"7\n"); // Obsolete variable
		fwrite($handle,$_POST['TickKey_'.$i_cycle]."\n");
		fwrite($handle,$_POST['TickChannel_'.$i_cycle]."\n");
		fwrite($handle,$_POST['TickVelocity_'.$i_cycle]."\n");
		$TickCycle[$i_cycle] = intval($_POST['TickCycle_'.$i_cycle]);
		if($TickCycle[$i_cycle] > 40) $TickCycle[$i_cycle] = 40;
		if($TickCycle[$i_cycle] == 0) $TickCycle[$i_cycle] = 1;
		fwrite($handle,$TickCycle[$i_cycle]."\n");
		fwrite($handle,$_POST['Ptick_'.$i_cycle]."\n");
		fwrite($handle,$_POST['Qtick_'.$i_cycle]."\n");
		fwrite($handle,$_POST['TickDuration_'.$i_cycle]."\n");
		for($i = 0; $i < $maxbeats; $i++) {
			if(isset($_POST['ThisTick_'.$i_cycle.'_'.$i])) fwrite($handle,"1\n");
			else fwrite($handle,"0\n");
			}
		}
	fwrite($handle,"DATA: ".$_POST['comment']."\n");
	fclose($handle);
	}

try_create_new_file($this_file,$filename);
$content = @file_get_contents($this_file,TRUE);
if($content === FALSE) ask_create_new_file($url_this_page,$filename);
if(trim($content) == '') $content = @file_get_contents("timebase_template",TRUE);
$pick_up_headers = pick_up_headers($content);
echo "<p style=\"color:blue;\">".$pick_up_headers['headers']."</p>";
$content = $pick_up_headers['content'];
$j = 0;
$table = explode(chr(10),$content);
$maxticks = $table[$j++];
// echo "maxticks = ".$maxticks."<br />";
$maxbeats = $table[$j++];
// echo "maxbeats = ".$maxbeats."<br />";

$p_clock = 4;
$q_clock = 1;
if(isset($_POST['p_clock'])) $p_clock = $_POST['p_clock'];
if(isset($_POST['q_clock'])) $q_clock = $_POST['q_clock'];

echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<input type=\"hidden\" name=\"maxticks\" value=\"".$maxticks."\">";
echo "<input type=\"hidden\" name=\"maxbeats\" value=\"".$maxbeats."\">";
$metronome = round(($p_clock * 60 / $q_clock),2);
echo "<p style=\"text-align:left;\">";
echo "<input style=\"background-color:yellow;\" type=\"submit\" name=\"savealldata\" value=\"SAVE ALL DATA\">";
echo "&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"p_clock\" size=\"3\" value=\"".$p_clock."\"> ticks in <input type=\"text\" name=\"q_clock\" size=\"3\" value=\"".$q_clock."\"> sec. ➡ mm = ".$metronome." beats/mn</p>";
echo "<hr>";
echo "<table style=\"background-color:gold;\">";
for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	$j += 2;
	$TickKey[$i_cycle] = $table[$j++];
	$TickChannel[$i_cycle] = $table[$j++];
	$TickVelocity[$i_cycle] = $table[$j++];
	$TickCycle[$i_cycle] = $table[$j++];
	$Ptick[$i_cycle] = $table[$j++];
	$Qtick[$i_cycle] = $table[$j++];
	$TickDuration[$i_cycle] = $table[$j++];
	for($i = 0; $i < $maxbeats; $i++) $ThisTick[$i_cycle][$i] = $table[$j++];
	echo "<tr>";
	echo "<td style=\"text-align: center; vertical-align: middle; background-color: gold; font-size: x-large; color:red;\" rowspan = \"2\"><b>".($i_cycle + 1)."</b></td><td style=\"padding:6px;\">Cycle of <input type=\"text\" name=\"TickCycle_".$i_cycle."\" size=\"3\" value=\"".$TickCycle[$i_cycle]."\"> beat(s) [max 40]</td>";
	echo "<td style=\"text-align: right;\">Speed ratio <input type=\"text\" name=\"Ptick_".$i_cycle."\" size=\"3\" value=\"".$Ptick[$i_cycle]."\">&nbsp;/&nbsp;<input type=\"text\" name=\"Qtick_".$i_cycle."\" size=\"3\" value=\"".$Qtick[$i_cycle]."\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan=\"2\" style=\"padding:6px;\">key = <input type=\"text\" name=\"TickKey_".$i_cycle."\" size=\"3\" value=\"".$TickKey[$i_cycle]."\"> channel = <input type=\"text\" name=\"TickChannel_".$i_cycle."\" size=\"3\" value=\"".$TickChannel[$i_cycle]."\"> velocity = <input type=\"text\" name=\"TickVelocity_".$i_cycle."\" size=\"3\" value=\"".$TickVelocity[$i_cycle]."\"> duration = <input type=\"text\" name=\"TickDuration_".$i_cycle."\" size=\"3\" value=\"".$TickDuration[$i_cycle]."\"> ms</td>";
	echo "</tr>";
	echo "<tr><td colspan=\"3\">";
	for($i = 0; $i < $maxbeats; $i++) {
		echo "<input type=\"checkbox\" name=\"ThisTick_".$i_cycle."_".$i."\"";
		if($ThisTick[$i_cycle][$i] == 1) echo " checked";
		if($i >= $TickCycle[$i_cycle]) echo " disabled";
		echo ">&nbsp;";
		}
	echo "</td></tr>";
	echo "<tr><td colspan=\"3\" style=\"background-color:gold;\">";
	echo "</td></tr>";
	}
if(isset($table[$j])) $comment = $table[$j];
else $comment = '';
$comment = trim(str_replace("DATA:",'',$comment));
$comment = str_ireplace("<HTML>",'',$comment);
$comment = str_ireplace("</HTML>",'',$comment);
echo "<tr><td colspan=\"3\" style=\"padding:6px;\">";


echo "<div style=\"float:right;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"addtrack\" value=\"ADD ANOTHER TRACK\"></div>";

echo "Comment on this timebase:<br /><input type=\"text\" name=\"comment\" size=\"80\" value=\"".$comment."\">";
echo "</td></tr>";
echo "</table>";
echo "<p><input style=\"background-color:yellow;\" type=\"submit\" name=\"savealldata\" value=\"SAVE ALL DATA\">";

$check_midi = FALSE;
// $check_midi = TRUE;

if($check_midi) echo "</p>p_clock = ".$p_clock."<br />";
if($check_midi) echo "q_clock = ".$q_clock."<br />";

for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	$duration[$i_cycle] = 1000 * $TickCycle[$i_cycle] * $Qtick[$i_cycle] / $Ptick[$i_cycle];
	if($check_midi) echo "duration = ".$duration[$i_cycle]."<br />";
	}
$gcd = gcd_array($duration,0);
if($check_midi) echo "gcd = ".$gcd."<br />";

$mult = 1;
for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	if($gcd > 0) {
		$duration[$i_cycle] = round($duration[$i_cycle] / $gcd);
		$approxQtick[$i_cycle] = round($duration[$i_cycle] * $gcd * $Ptick[$i_cycle] / $TickCycle[$i_cycle] / 1000);
		}
	else {
		$duration[$i_cycle] = round($duration[$i_cycle]);
		$approxQtick[$i_cycle] = round($duration[$i_cycle] * $Ptick[$i_cycle] / $TickCycle[$i_cycle] / 1000);
		}
	$mult = $mult * ($duration[$i_cycle] / gcd($mult,$duration[$i_cycle]));
	if($check_midi) echo "duration = ".$duration[$i_cycle]."<br />";
	}
if($check_midi) echo "mult = ".$mult."<br />";

for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	$repeat[$i_cycle] = $mult / $duration[$i_cycle];
	if($check_midi) echo "repeat = ".$repeat[$i_cycle]."<br />";
	}
$actual_beats_combined = round($repeat[0] * $TickCycle[0] * $Qtick[0] / $Ptick[0]);
$actual_duration_combined = round($actual_beats_combined * $q_clock / $p_clock);
echo "<p>Actual duration of combined tracks is ".$actual_beats_combined." beats = ".$actual_duration_combined." seconds with:</p>";
echo "<ul>";
for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	echo "<li>track #".($i_cycle + 1)." repeated ".$repeat[$i_cycle]." time(s)</li>";
	}
echo "</ul>";
	
$mf2t = $dir.$temp_folder.SLASH."mf2t.txt";
$handle = fopen($mf2t,"w");
$number_of_tracks = $maxticks;
$division = 480;
if(isset($_POST['max_repeat'])) $max_repeat = intval($_POST['max_repeat']);
else $max_repeat = 3;
if($max_repeat == 0) $max_repeat = 1;
if(isset($_POST['max_time_play'])) $max_time_play = $_POST['max_time_play'];
else $max_time_play = 60 * 2; // Not longer than 2 minutes
if(isset($_POST['end_silence'])) $end_silence = $_POST['end_silence'];
else $end_silence = 200; // ms
$MaxTime = 1000 * $max_time_play;
fwrite($handle,"MFile 1 ".$number_of_tracks." ".$division."\n");
for($i_cycle = 0; $i_cycle < $maxticks; $i_cycle++) {
	$trk = $i_cycle + 1;
	$time = $start_time = 0;
	fwrite($handle,"MTrk\n");
	$track_name = "track_".$trk;
	fwrite($handle,"0 Meta TrkName \"".$track_name."\"\n");
	$delta_t = 1000 * $q_clock *  $approxQtick[$i_cycle] / $p_clock / $Ptick[$i_cycle];
	for($r = 0; $r < ($max_repeat * $repeat[$i_cycle]); $r++) {
		if($check_midi) echo $track_name." repeat ".$r."<br />";
		if($start_time > $MaxTime) break;
		for($i = 0; $i < $maxbeats; $i++) {
			$time = $start_time + round($i * $delta_t);
			if($i >= $TickCycle[$i_cycle]) break;
			if($ThisTick[$i_cycle][$i] == 1) {
				$channel = $TickChannel[$i_cycle];
				$key = $TickKey[$i_cycle];
				$velocity = $TickVelocity[$i_cycle];
				$mf2t_line = $time." On ch=".$channel." n=".$key." v=".$velocity;
				fwrite($handle,$mf2t_line."\n");
				$time += $TickDuration[$i_cycle];
				$velocity = 0;
				$mf2t_line = $time." On ch=".$channel." n=".$key." v=".$velocity;
				fwrite($handle,$mf2t_line."\n");
				}
			}
		$start_time = $time;
		}
	$time += $end_silence;
	$mf2t_line = $time." On ch=".$channel." n=".$key." v=".$velocity;
	fwrite($handle,$mf2t_line."\n");
	fwrite($handle,$time." Meta TrkEnd\n");
	fwrite($handle,"TrkEnd\n");
	}
fclose($handle);

$midi_file = $dir.$temp_folder.SLASH."midicodes.mid";

$mf2t_content = @file_get_contents($mf2t,TRUE);
$midi = new Midi();
$midi->importTxt($mf2t_content);
$midi->saveMidFile($midi_file);

if(file_exists($midi_file)) {
	echo "&nbsp;<a href=\"#midi\" onClick=\"MIDIjs.play('".$midi_file."');\"><img src=\"pict/loudspeaker.png\" width=\"70px;\" style=\"vertical-align:middle;\" />Play MIDI file</a>";
	echo " (<a href=\"#midi\" onClick=\"MIDIjs.stop();\">Stop playing</a>) up to <input type=\"text\" name=\"max_repeat\" size=\"3\" value=\"".$max_repeat."\">&nbsp;repetitions and less than <input type=\"text\" name=\"max_time_play\" size=\"3\" value=\"".$max_time_play."\">&nbsp;sec ending with silence of <input type=\"text\" name=\"end_silence\" size=\"5\" value=\"".$end_silence."\">&nbsp;ms</p>";
	}
echo "</form>";

/*
echo "<form method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
echo "<p style=\"text-align:left;\"><input style=\"background-color:yellow;\" type=\"submit\" name=\"savethisfile\" value=\"SAVE ‘".$filename."’\"></p>";
echo "<textarea name=\"thistext\" rows=\"40\" style=\"width:700px; background-color:Cornsilk;\">".$content."</textarea>";
echo "</form>"; */
?>
