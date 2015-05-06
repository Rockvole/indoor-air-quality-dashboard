<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include 'globals.php';

$start_date_param = htmlspecialchars($_GET["start_date"]);
$direction_param = htmlspecialchars($_GET["direction"]);
$zoom_type = htmlspecialchars($_GET["zoom_type"]);

$today_ts=get_ts_today($start_date_param,$direction_param);

if(!isset($today_ts)) {
  echo "No records found";
} else {
  $date = Carbon::createFromTimeStamp($today_ts);
  $start_day_utc = $date->startOfDay()->format('U');
  $end_day_utc = $date->endOfDay()->format('U');  
  $row = get_current_geographical($end_day_utc);  
  
  error_log("||referer=".$_SERVER['HTTP_REFERER']);
  error_log("||today_ts=".$today_ts);
  error_log("||eod=".$end_day_utc);
  error_log("||zoom_type=$zoom_type");
  error_log("||direction=".$direction_param);
  error_log("||rs=".$row['ts']);


  switch($zoom_type) {
    case 1: // Humidity
      $field_name="zoom_temp_hum";
      $field_value=$row['zoom_temp_hum'] == 1 ? 0 : 1;
      break;
    case 3: // Sewer
      $field_name="zoom_sewer";
      $field_value=$row['zoom_sewer'] == 1 ? 0 : 1;
      break;
  }
  $sql = "   UPDATE geographical set ".$field_name."=".$field_value .
	 "    WHERE group_id=$id AND ts=".$row['ts'];	 
  $result=mysqli_query($conn,$sql);

  header('Location: '.$_SERVER['HTTP_REFERER']);
  die();
}
?>

