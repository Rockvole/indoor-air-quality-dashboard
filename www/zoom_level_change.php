<?php
include 'globals.php';

$zoom_type = htmlspecialchars($_GET["zoom_type"]);

error_log("tada".$_SERVER['HTTP_REFERER']);
error_log("||||zoom_type=$zoom_type");

switch($zoom_type) {
  case 1: // Humidity
  
    break;
  case 3: // Sewer
  
    break;     
}

header('Location: '.$_SERVER['HTTP_REFERER']);
die();

?>

