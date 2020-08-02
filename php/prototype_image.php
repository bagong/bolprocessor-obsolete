<?php
header("Content-type: image/png");
// require_once("_basic_tasks.php");
$save_codes_dir = urldecode($_GET['save_codes_dir']);
$image_file = $save_codes_dir."/image.php";
require_once($image_file);

if($pivbeg == 1) $pivot_pos = 0;
if($pivcent == 1) $pivot_pos = $Duration / 2;
if($pivend == 1) $pivot_pos = $Duration;
if(isset($first_note_on) AND $pivbegon == 1) $pivot_pos = $first_note_on;
if(isset($last_note_off) AND $pivendoff == 1) $pivot_pos = $last_note_off;
if(isset($last_note_off) AND isset($last_note_off) AND $pivcentonoff == 1) $pivot_pos = ($first_note_on + $last_note_off) / 2;

$margin_left = 50;
$width = 600;
$height = 20;
if($Duration > 0) $alpha = $width/$Duration;
else $alpha = 0;

$more = 0;

// Revise left and right margins to display gaps correctly if any
if($ContBeg) $beg_mssg = "ContBeg";
else $beg_mssg = "#ContBeg";
if($ContEnd) $end_mssg = "ContEnd";
else $end_mssg = "#ContEnd";

$gapbeg = 0;
if($ContBeg AND $ContBegMode == -1) {
	$gapbeg = $MaxBegGap;
	if($gapbeg > 0) $beg_mssg .= " with gap ".$MaxBegGap." ms";
	}
if($ContBeg AND $ContBegMode == 0) {
	$gapbeg = $MaxBegGap * $Duration / 100;
	if($gapbeg > 0) $beg_mssg .= " with gap ".$MaxBegGap." %";
	}

if(($alpha * $gapbeg) > ($margin_left - 50))
	$more += ($alpha * $gapbeg);

$margin_left += $more;

$gapend = 0;
if($ContEnd AND $ContEndMode == -1) {
	$gapend = $MaxEndGap;
	if($gapend > 0) $end_mssg .= " with gap ".$MaxEndGap." ms";
	}
if($ContEnd AND $ContEndMode == 0) {
	$gapend = $MaxEndGap * $Duration / 100;
	if($gapend > 0) $end_mssg .= " with gap ".$MaxEndGap." %";
	}
	
if(($alpha * $gapend) > 50) $more += ($alpha * $gapend);

// Revise left and right margins to display pivot if position is outside object
if($pivspec == 1) {
	if($PivMode == -1) $pivot_pos = $PivPos;
	if($PivMode == 0) $pivot_pos = $PivPos * $Duration / 100;
	if($pivot_pos < 0) {
		$more = -($alpha * $pivot_pos);
		$margin_left += $more;
		}
	if(($alpha * $pivot_pos) > $width) {
		$more += (($alpha * $pivot_pos) - $width);
		}
	}

$image_width = $width + 100 + $more;
if($image_width < 800) $image_width = 800;
$im = @imagecreatetruecolor($image_width,625)
      or die('Cannot Initialize new GD image stream');
$white = imagecolorallocate($im,255,255,255);
$black = imagecolorallocate($im,0,0,0);
$grey = imagecolorallocate($im, 128, 128, 128);
$red = imagecolorallocate($im,233,14,91);
$orange = imagecolorallocate($im,220,210,60);
$yellow = imagecolorallocate($im,255,255,5);
$blue = imagecolorallocate($im,0,255,255);
$green = imagecolorallocate($im,174,240,217);

imagefilledrectangle($im,0,0,$image_width,625,$white);

// Write title
$text = "Sound-object prototype \"".$object_name."\"";
/* https://www.php.net/manual/en/function.imagettftext.php
$font = 'arial.ttf';
imagettftext($im, 20, 0, $margin_left,30, $black, $font, $text); */
imagestring($im,10,$margin_left,30,$text,$black);
$text = "Duration ".$Duration." ms";
if($Tref > 0) $text .= " (".round(($Duration / $Tref),2)." beats)";
imagestring($im,10,$margin_left,50,$text,$black);

$x1 = $margin_left;
$y1 = 200;
$x2 = $x1 + $width;
$y2 = $y1 + $height;

// Draw object rectangle
imagefilledrectangle($im,$x1+($alpha*$PreRoll),$y1,$x2+($alpha*$PostRoll),$y2,$yellow);
imagerectangle($im,$x1+($alpha*$PreRoll),$y1,$x2+($alpha*$PostRoll),$y2,$black);

// Draw MIDI events
if(isset($event[0])) {
	for($i = 0; $i < count($event); $i++) {
		$time = $event[$i];
		$x = $margin_left + ($alpha * $time);
		imageline($im,$x,$y1,$x,$y2+5,$red);
		}
	}

// Draw time line and time units
imageline($im,$x1,100,$x2+($alpha*$PostRoll),100,$black);
$t = 0;
while(TRUE) {
	imagefilledrectangle($im,$margin_left+($alpha*$t),100,$margin_left+($alpha*$t)+1,115,$black);
	$t += 1000;
	if($t > $Duration) break;
	}
$t = 0;
while(TRUE) {
	imageline($im,$margin_left+($alpha*$t),100,$margin_left+($alpha*$t),110,$black);
	$t += 100;
	if($t > $Duration) break;
	}
imagestring($im,10,$margin_left-2,82,"0",$black);

// Write time unit
if($Duration > 10000)
	imagestring($im,10,$margin_left-2+($alpha*10000),82,"10 s",$black);
else if($Duration > 1000)
	imagestring($im,10,$margin_left-2+($alpha*1000),82,"1.00 s",$black);
else if($Duration > 100)
	imagestring($im,10,$margin_left-2+($alpha*100),82,"100 ms",$black);

// Display beats
if($Tref > 0 AND $Tref < $Duration) {
	$mssg = "(beats)";
	$length_mssg = imagefontwidth(10) * strlen($mssg);
	$t = 0;
	while(TRUE) {
		$x = $x1 + ($alpha * $t);
		imagefilledrectangle($im,$x-1,115,$x+1,135,$green);
		$t += $Tref;
		if($t > ($Duration + $PostRoll)) break;
		}
	imageline($im,$x1,125,$x2 - $length_mssg - 10,125,$green);
	imagestring($im,10,$x2 - $length_mssg,115,$mssg,$black);
	}

// Draw period if object is cyclic
if(isset($PeriodMode)) {
	$before_period = -1;
	if($PeriodMode == -1) $before_period = $BeforePeriod;
	if($PeriodMode == 0) $before_period = $BeforePeriod * $Duration / 100;
	if($before_period >= 0) {
		imageline($im,$margin_left + ($alpha * $Duration),$y2-58,$margin_left + ($alpha * $Duration),$y2-20,$blue);
		imageline($im,$margin_left + ($alpha * $before_period),$y2-58,$margin_left + ($alpha * $Duration),$y2-58,$blue);
		imageline($im,$margin_left + ($alpha * $before_period),$y2-58,$margin_left + ($alpha * $before_period),$y2-20,$blue);
		arrow($im,$margin_left + ($alpha * $before_period),$y1 - 38,$margin_left + ($alpha * $before_period),$y1,17,5,0,$blue);
		$mssg = "(cyclic)";
		$length_mssg = imagefontwidth(10) * strlen($mssg);
		imagestring($im,10,$x2 - $length_mssg,$y2-50,$mssg,$blue);
		}
	}

// Draw pivot if object is striated
if($Tref > 0 AND isset($pivot_pos)) {
	arrow($im,$margin_left+($alpha*$pivot_pos),$y1 - 30,$margin_left+($alpha*$pivot_pos),$y1,17,5,$OkRelocate,$red);
	}

$vshift = 30;

// Draw trailing rectangle if continuous beginning
if(isset($ContBeg) AND $ContBeg)
	imagefilledrectangle($im,0,$y1,$x1+($alpha*$PreRoll)-($alpha*$gapbeg)-1,$y2,$yellow);

// Indicate measure of gap at beginning if any
if($gapbeg > 0) {
	imagefilledrectangle($im,$x1+($alpha*$PreRoll)-($alpha*$gapbeg)-1,$y2 + $vshift,$x1,$y2 + $vshift + 1,$blue);
	imageline($im,$x1,$y2 + $vshift,$x1,$y2 + $vshift - 20,$blue);
	imageline($im,$x1+($alpha*$PreRoll)-($alpha*$gapbeg)-1,$y2 + $vshift,$x1+($alpha*$PreRoll)-($alpha*$gapbeg)-1,$y2 + $vshift - 20,$blue);
	}

// Draw trailing rectangle if continuous end
if(isset($ContEnd) AND $ContEnd)
	imagefilledrectangle($im,$x2+($alpha*$gapend)+1,$y1,$width+100+$more,$y2,$yellow);

// Indicate measure of gap at beginning if any
if($gapend > 0) {
	imagefilledrectangle($im,$x2,$y2 + $vshift,$x2+($alpha*$gapend)+1,$y2 + $vshift + 1,$blue);
	imageline($im,$x2,$y2 + $vshift,$x2,$y2 + $vshift - 20,$blue);
	imageline($im,$x2+($alpha*$gapend)+1,$y2 + $vshift,$x2+($alpha*$gapend)+1,$y2 + $vshift - 20,$blue);
	$vshift += 10;
	}

// Write continuity settings and values of gaps if any
imagestring($im,10,$margin_left-($alpha*$gapbeg),$y2 + $vshift,$beg_mssg,$black);
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($end_mssg))+($alpha*$gapend),$y2 + $vshift,$end_mssg,$black);

$vshift += 30;

// Truncate beginning, truncate end
if($TruncBeg) $beg_mssg = "TruncBeg at will";
else $beg_mssg = "#TruncBeg";
if($TruncEnd) $end_mssg = "TruncEnd at will";
else $end_mssg = "#TruncEnd";

$max_truncbeg = -1;
if(!$TruncBeg AND $TruncBegMode == 0) {
	$max_truncbeg = $MaxTruncBeg * $Duration / 100;
	if($max_truncbeg > 0) $beg_mssg = "TruncBeg ".$MaxTruncBeg." %";
	}
if(!$TruncBeg AND $TruncBegMode == -1) {
	$max_truncbeg = $MaxTruncBeg;
	if($max_truncbeg > 0) $beg_mssg = "TruncBeg ".$MaxTruncBeg." ms";
	}

if($max_truncbeg > 0) {
	$vshift += 30;
	imagefilledrectangle($im,$x1,$y2 + $vshift,$x1 + ($alpha * $max_truncbeg),$y2 + $vshift + 1,$blue);
	imageline($im,$x1,$y2 + $vshift,$x1,$y2 + $vshift - 20,$blue);
	imageline($im,$x1 + ($alpha * $max_truncbeg),$y2 + $vshift,$x1 + ($alpha * $max_truncbeg),$y2 + $vshift - 20,$blue);
	$vshift += 10;
	}
imagestring($im,10,$margin_left,$y2 + $vshift,$beg_mssg,$black);

$max_truncend = -1;
if(!$TruncEnd AND $TruncEndMode == 0) {
	$max_truncend = $MaxTruncEnd * $Duration / 100;
	if($max_truncend > 0) $end_mssg = "TruncEnd ".$MaxTruncEnd." %";
	}
if(!$TruncEnd AND $TruncEndMode == -1) {
	$max_truncend = $MaxTruncEnd;
	if($max_truncend > 0) $end_mssg = "TruncEnd ".$MaxTruncEnd." ms";
	}

if($max_truncend > 0) {
	$vshift += 40;
	imagefilledrectangle($im,$x2 - ($alpha * $max_truncend),$y2 + $vshift,$x2,$y2 + $vshift + 1,$blue);
	imageline($im,$x2 - ($alpha * $max_truncend),$y2 + $vshift,$x2 - ($alpha * $max_truncend),$y2 + $vshift - 20,$blue);
	imageline($im,$x2,$y2 + $vshift,$x2,$y2 + $vshift - 20,$blue);
	$vshift += 10;
	}
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($end_mssg)),$y2 + $vshift,$end_mssg,$black);

$vshift += 20;

// Cover beginning, cover end
if($CoverBeg) $beg_mssg = "CoverBeg at will";
else $beg_mssg = "#CoverBeg";
if($CoverEnd) $end_mssg = "CoverEnd at will";
else $end_mssg = "#CoverEnd";

$max_coverbeg = -1;
 if(!$CoverBeg AND $CoverBegMode == 0) {
	$max_coverbeg = $MaxCoverBeg * $Duration / 100;
	if($max_coverbeg > 0) $beg_mssg = "CoverBeg ".$MaxCoverBeg." %";
	}
 if(!$CoverBeg AND $CoverBegMode == -1) {
	$max_coverbeg = $MaxCoverBeg;
	if($max_coverbeg > 0) $beg_mssg = "CoverBeg ".$MaxCoverBeg." ms";
	}

if($max_coverbeg > 0) {
	$vshift += 30;
	imagefilledrectangle($im,$x1,$y2 + $vshift,$x1 + ($alpha * $max_coverbeg),$y2 + $vshift + 1,$blue);
	imageline($im,$x1,$y2 + $vshift,$x1,$y2 + $vshift - 20,$blue);
	imageline($im,$x1 + ($alpha * $max_coverbeg),$y2 + $vshift,$x1 + ($alpha * $max_coverbeg),$y2 + $vshift - 20,$blue);
	$vshift += 10;
	}
imagestring($im,10,$margin_left,$y2 + $vshift,$beg_mssg,$black);

$vshift += 30;

$max_coverend = -1;
if(!$CoverEnd AND $CoverEndMode == 0) {
	$max_coverend = $MaxCoverEnd * $Duration / 100;
	if($max_coverend > 0) $end_mssg = "CoverEnd ".$MaxCoverEnd." %";
	}
if(!$CoverEnd AND $CoverEndMode == -1) {
	$max_coverend = $MaxCoverEnd;
	if($max_coverend > 0) $end_mssg = "CoverEnd ".$MaxCoverEnd." ms";
	}

if($max_coverend > 0) {
	$vshift += 30;
	imagefilledrectangle($im,$x2 - ($alpha * $max_coverend),$y2 + $vshift,$x2,$y2 + $vshift + 1,$blue);
	imageline($im,$x2,$y2 + $vshift,$x2,$y2 + $vshift - 20,$blue);
	imageline($im,$x2 - ($alpha * $max_coverend),$y2 + $vshift,$x2 - ($alpha * $max_coverend),$y2 + $vshift - 20,$blue);
	$vshift += 10;
	}
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($end_mssg)),$y2 + $vshift,$end_mssg,$black);

$vshift += 30;

// Write Break tempo (organum)
if($BreakTempo) $mssg = "BreakTempo (organum)";
else $mssg = "#BreakTempo";
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($mssg)),$y2 + $vshift,$mssg,$black);

$vshift += 40;

// Write dilation status
if(isset($dilation_mssg))
	imagestring($im,10,$margin_left,$y2 + $vshift,$dilation_mssg,$black);
else {
	if($FixScale)
		imagestring($im,10,$margin_left,$y2 + $vshift,"Fixed scale",$black);
	else {
		if($OkCompress) {
			imagestring($im,10,$margin_left,$y2 + $vshift,"Compress at will",$black);
			$vshift += 20;
			}
		if($OkExpand)
			imagestring($im,10,$margin_left,$y2 + $vshift,"Expand at will",$black);
		}
	}

$vshift += 20;

// Write MIDI channel status
if(isset($event[0])) {
	if($MIDIchannel > 0) $mssg = "Force to MIDI channel #".$MIDIchannel;
	else if($MIDIchannel < 0) $mssg = "Do not change MIDI channels";
	else if($MIDIchannel == 0) $mssg = "Force to current MIDI channel";
	imagestring($im,10,$margin_left,$y2 + $vshift,$mssg,$black);
	}

imagepng($im);
imagedestroy($im);

// ------- functions ---------

function arrow($im,$x1,$y1,$x2,$y2,$alength,$awidth,$relocate,$color) {
    $distance = sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2));
    $dx = $x2 + ($x1 - $x2) * $alength / $distance;
    $dy = $y2 + ($y1 - $y2) * $alength / $distance;
    $k = $awidth / $alength;
    $x2o = $x2 - $dx;
    $y2o = $dy - $y2;
    $x3 = $y2o * $k + $dx;
    $y3 = $x2o * $k + $dy;
    $x4 = $dx - $y2o * $k;
    $y4 = $dy - $x2o * $k;
    if(!$relocate) imagefilledrectangle($im,$x1-1,$y1,$x1+1,$y2-2,$color);
    imagefilledpolygon($im, array($x2, $y2, $x3, $y3, $x4, $y4), 3, $color);
	}
?>