<?php
include '../globals.php';
require_once ("jpgraph/jpgraph.php");
require_once ("jpgraph/jpgraph_line.php");
require_once ("jpgraph/jpgraph_date.php");

if(!isset($_GET["id"])) {
	error_log("Must specify id parameter");
	exit();
}
if(!isset($_GET["width"])) {
	error_log("Must specify width parameter");
	exit();
}
if(!isset($_GET["height"])) {
	error_log("Must specify height parameter");
	exit();
}
if(!isset($_GET["start_ts"])) {
	error_log("Must specify start_ts parameter");
	exit();
}
if(!isset($_GET["end_ts"])) {
	error_log("Must specify end_ts parameter");
	exit();
}

$id = htmlspecialchars($_GET["id"]);
$width = htmlspecialchars($_GET["width"]);
$height = htmlspecialchars($_GET["height"]);
$start_ts = htmlspecialchars($_GET["start_ts"]);
$end_ts = htmlspecialchars($_GET["end_ts"]);

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 

$font_size=11;
$line_fill_color='white@0.65';
?>
