<?php
session_start();
// $_SERVER unfortunaely returns a "unixy" path for DOCUMENT_ROOT
// on Windows because it reads httpd.conf - when "slash"-syntax
// is used
$root = $_SERVER['DOCUMENT_ROOT']."/";
// this can be converted to a Windows path (note the removed slash):
$root = realpath($root);
echo "root = ".$root."<br />";
$current_path = getcwd();
echo "current_path = ".$current_path."<br />";
// here the missing trailing (back-)slash gets into play becomes
// a problem
$path_to_bp = str_replace($root,'',$current_path);
echo $path_to_bp."<br />";
// the cross-platform fix is to use .DIRECTORY_SEPARATOR.
$path_to_bp = str_replace($root.DIRECTORY_SEPARATOR,'',$current_path);
echo $path_to_bp."<br />";
// but here the "to-be-replaced-string" is not found because of "/"
$path_to_bp = str_replace("bolprocessor/php",'',$path_to_bp);
echo "path_to_bp = ".$path_to_bp."<br />";
// again .DIRECTORY_SEPARATOR.
$path_to_bp = str_replace("bolprocessor".DIRECTORY_SEPARATOR."php",'',$path_to_bp);
echo "path_to_bp = ".$path_to_bp."<br />";
// but isn't it clumsy?
// again we need directory_separator - and while the slash below
// might work, one shouldn't rely on it:
$text_help_file = $root.$path_to_bp."bolprocessor/BP2_help.txt";
echo $text_help_file."<br />";
// "fixed"
$text_help_file = $root.$path_to_bp.DIRECTORY_SEPARATOR."bolprocessor".DIRECTORY_SEPARATOR."BP2_help.txt";
echo $text_help_file."<br />";
$html_help_file = "BP2_help.html";
echo $html_help_file."<br />";
// I think it is better not to rely on "DOCUMENT_ROOT" (I don't think we need to
// know it at all) and wherever possible on str_replace for path manipulation.
// I'll try to suggest fixes in a PR
?>
