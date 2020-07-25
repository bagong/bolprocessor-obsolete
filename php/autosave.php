<?php
require_once("_basic_tasks.php");

if(isset($_GET['save'])) {
	$dir = $_POST['dir'];
	$filename = $_POST['filename'];
	$temp_folder = $_POST['temp_folder'];
	SaveObjectPrototypes(FALSE,$dir,$filename,$temp_folder);
	echo "INACTIVE! … <font color=\"red\">".date('H\hi - s \s\e\c\o\n\d\s')."</font> ➡ <font color=\"red\">Autosaved all prototypes in</font> “<font color=\"blue\">".$filename."</font>”";
	}
?>