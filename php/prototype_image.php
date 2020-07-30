<?php
header("Content-type: image/png");
$save_codes_dir = urldecode($_GET['save_codes_dir']);
$image_file = $save_codes_dir.DIRECTORY_SEPARATOR."image.php";
require_once($image_file);

if($pivbeg == 1) $pivot_pos = 0;
if($pivcent == 1) $pivot_pos = $Duration / 2;
if($pivend == 1) $pivot_pos = $Duration;
if(isset($first_note_on) AND $pivbegon == 1) $pivot_pos = $first_note_on;
if(isset($last_note_off) AND $pivendoff == 1) $pivot_pos = $last_note_off;
if(isset($last_note_off) AND isset($last_note_off) AND $pivcentonoff == 1) $pivot_pos = ($first_note_on + $last_note_off) / 2;

$margin_left = 50;
$width = 500;
$height = 20;
if($Duration > 0) $alpha = $width/$Duration;
else $alpha = 0;

$more = 0;
if($pivspec == 1) {
	if($PivMode == -1) $pivot_pos = $PivPos;
	if($PivMode == 0) $pivot_pos = $PivPos * $Duration / 100;
	if($pivot_pos < 0) {
		$more = -($alpha * $pivot_pos);
		$margin_left += $more;
		}
	}

$im = @imagecreatetruecolor($width+100+$more,600)
      or die('Cannot Initialize new GD image stream');
$white = imagecolorallocate($im,255,255,255);
$black = imagecolorallocate($im,0,0,0);
$red = imagecolorallocate($im,233,14,91);
$orange = imagecolorallocate($im,220,210,60);
$yellow = imagecolorallocate($im,255,255,5);

imagefilledrectangle($im,0,0,$width+100+$more,600,$white);

$text = "This sound-object \"".$object_name."\" has duration ".$Duration." ms";
imagestring($im,10,$margin_left,30,$text,$black);

$x1 = $margin_left;
$y1 = 200;
$x2 = $x1 + $width;
$y2 = $y1 + $height;

imagefilledrectangle($im,$x1+($alpha*$PreRoll),$y1,$x2+($alpha*$PostRoll),$y2,$yellow);
imagerectangle($im,$x1+($alpha*$PreRoll),$y1,$x2+($alpha*$PostRoll),$y2,$black);

for($i = 0; $i < count($event); $i++) {
	$time = $event[$i];
	$x = $margin_left + ($alpha * $time);
	imageline($im,$x,$y1,$x,$y2+5,$red);
	}

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
if($Duration > 1000)
	imagestring($im,10,$margin_left-2+($alpha*1000),82,"1.00s",$black);
else 
	imagestring($im,10,$margin_left-2+($alpha*100),82,"100ms",$black);

if(isset($pivot_pos)) {
	arrow($im,$margin_left+($alpha*$pivot_pos),$y1 - 40,$margin_left+($alpha*$pivot_pos),$y1,17,5,$OkRelocate,$red);
	}

if($ContBeg) $beg_mssg = "ContBeg";
else $beg_mssg = "#ContBeg";
if($ContEnd) $end_mssg = "ContEnd";
else $end_mssg = "#ContEnd";
imagestring($im,10,$margin_left,$y2 + 10,$beg_mssg,$black);
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($end_mssg)),$y2 + 10,$end_mssg,$black);

if($TruncBeg) $beg_mssg = "TruncBeg";
else $beg_mssg = "#TruncBeg";
if($TruncEnd) $end_mssg = "TruncEnd";
else $end_mssg = "#TruncEnd";
imagestring($im,10,$margin_left,$y2 + 30,$beg_mssg,$black);
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($end_mssg)),$y2 + 30,$end_mssg,$black);

if($BreakTempo) $mssg = "BreakTempo";
else $mssg = "#BreakTempo";
imagestring($im,10,$x2 - (imagefontwidth(10) * strlen($mssg)),$y2 + 50,$mssg,$black);

if(isset($dilation_mssg))
	imagestring($im,10,$margin_left,$y2 + 80,$dilation_mssg,$black);
else {
	if($FixScale)
		imagestring($im,10,$margin_left,$y2 + 80,"Fixed scale",$black);
	else {
		if($OkCompress)
			imagestring($im,10,$margin_left,$y2 + 80,"Compress at will",$black);
		if($OkExpand)
			imagestring($im,10,$margin_left,$y2 + 110,"Expand at will",$black);
		}
	}

if($MIDIchannel > 0) $mssg = "Force to MIDI channel #".$MIDIchannel;
else if($MIDIchannel < 0) $mssg = "Do not change MIDI channels";
else if($MIDIchannel == 0) $mssg = "Force to current MIDI channel";
imagestring($im,10,$margin_left,$y2 + 140,$mssg,$black);



// $CoverBeg);
// $CoverEnd);



imagepng($im);
imagedestroy($im);

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