<?php
include '../globals.php';

if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_INT);
$start_time = filter_input(INPUT_GET, 'start_time', FILTER_VALIDATE_INT);
$end_time = filter_input(INPUT_GET, 'end_time', FILTER_VALIDATE_INT);
if($type==1) $filename="events";
  else if($type==2) $filename="locations";
    else $filename="readings";

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=$filename.csv");
header("Pragma: no-cache");
header("Expires: 0");

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
if(is_numeric($start_time)) {
  $start_sql = " AND ts >= $start_time ";
}
if(is_numeric($end_time)) {
  $end_sql = " AND ts <= $end_time ";
}

if($type==1) { // ------------------------------------------------------ EVENTS
  $result=mysqli_query($conn,"SELECT * from events where group_id=$id $start_sql $end_sql order by ts");
  echo "name,unix_time\n";
  while($row = mysqli_fetch_array($result)) {
    echo $row['name'].",".$row['ts']."\n";
  }    
} else if($type==2) { // ----------------------------------------------- LOCATIONS
  $result=mysqli_query($conn,"SELECT * from locations where group_id=$id $start_sql $end_sql order by ts");
  echo "type,name,unix_time\n";
  while($row = mysqli_fetch_array($result)) {
    echo $row['type'].",".$row['name'].",".$row['ts']."\n";
  }      
} else { // ------------------------------------------------------------ READINGS
  $result=mysqli_query($conn,"SELECT * from readings where group_id=$id $start_sql $end_sql order by ts");
  echo "temperature,humidity,dust,sewer,hcho,unix_time\n";
  while($row = mysqli_fetch_array($result)) {
    echo $row['temperature'].",".$row['humidity'].",".$row['dust'].",".$row['sewer'].",".$row['hcho'].",".$row['ts']."\n";
  }  
}
mysqli_close($conn);
?>

