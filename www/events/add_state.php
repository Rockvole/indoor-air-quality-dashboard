<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include '../globals.php';

if(strlen($id)<=0) exit("Must specify id parameter");
if(strlen($size)<=0) exit("Must specify size parameter");
$state_type_id = htmlspecialchars($_GET["state_type_id"]);
if(strlen($state_type_id)<=0) exit("Must specify state_type_id parameter");
$location_id = htmlspecialchars($_GET["location_id"]);
if(strlen($location_id)<=0) exit("Must specify location_id parameter");
$ts = htmlspecialchars($_GET["ts"]);
if(strlen($ts)<=0) exit("Must specify ts parameter");
$start_date = htmlspecialchars($_GET["start_date"]);

$url= "http://" . $_SERVER['HTTP_HOST'];  
$url.= "/iaq/dashboard.php?id=$id";       
$url.="&start_date=$start_date";
$url.="&size=$size";  

$sql = "SELECT * FROM state_changes where location_id=$location_id and state_type_id=$state_type_id AND ts=$ts";
$result=mysqli_query($conn,$sql);
$row = mysqli_fetch_array($result);
if(mysqli_num_rows($result)>0) {
  $sql = "DELETE FROM state_changes where location_id=$location_id and state_type_id=$state_type_id AND ts=$ts"; 
  if(!mysqli_query($conn,$sql)) {
    exit('Error: '.mysqli_error($conn));
  } 
  header('Location: ' . $url, true, 302); 
  die();
} else {
  $curr_state=1;
  $sql = "SELECT * FROM state_changes where id=".
	"         (SELECT id from state_changes where location_id=$location_id and state_type_id=$state_type_id AND ts<$ts)";	
  $result=mysqli_query($conn,$sql);
  if(mysqli_num_rows($result)>0) { // We found a previous entry
    $row = mysqli_fetch_array($result);  
    if($row['state']==1) $curr_state=0;
  }
  $sql = "INSERT into state_changes (location_id, state_type_id, state, ts) VALUES ($location_id, $state_type_id, $curr_state, $ts)";
  if(!mysqli_query($conn,$sql)) {
    exit('Error: '.mysqli_error($conn));
  }
  header('Location: ' . $url, true, 302); 
  die();  
}
?>
