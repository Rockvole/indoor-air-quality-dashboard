<?php
include 'globals.php';

if(!isset($_GET["core_id"])) exit("Must specify core_id parameter");
if(!isset($_GET["unix_time"])) exit("Must specify unix_time parameter");

$core_id = htmlspecialchars($_GET["core_id"]);
$unix_time = htmlspecialchars($_GET["unix_time"]);
$dust = htmlspecialchars($_GET["dust"]);
$temp = htmlspecialchars($_GET["temp"]);
$hum = htmlspecialchars($_GET["hum"]);
$sewer = htmlspecialchars($_GET["sewer"]);
$hcho = htmlspecialchars($_GET["hcho"]);
$co = htmlspecialchars($_GET["co"]);
$co2 = htmlspecialchars($_GET["co2"]);
$error=false;
$conn=mysqli_connect("", $db_user, $db_pass, $db_name);

// Check connection
if (mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
  $error=true;
} else {
  // ------------------------------------------------------------------- RETRIEVE CORE INFO
  $result=mysqli_query($conn,"SELECT * from cores where core_id='".$core_id."'");	
  if(mysqli_errno()) {
    exit('Error: '.mysqli_error($conn));
    $error=true;
  }
  $num_rows = mysqli_num_rows($result);
  if($num_rows<=0) {
    exit("core '$core_id' not found");
    $error=true;
  }
  $row = mysqli_fetch_array($result);
  $id=$row['id'];
  mysqli_free_result($result);
  // ------------------------------------------------------------------- Temperature / Humidity
  if(strlen($temp)>0 && strlen($hum)>0) { 
    $result=mysqli_query($conn,"SELECT id from groups where temp_hum=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      insert_empty_reading($group_id, $unix_time);
      if($temp < -100) $temp="NULL";
      if($hum  < 0)    $hum ="NULL";
      $sql = "UPDATE readings SET temperature=$temp, humidity=$hum WHERE group_id=$group_id AND ts=$unix_time";
      $result=mysqli_query($conn,$sql);
    }    
  }
  // ------------------------------------------------------------------- Dust
  if(strlen($dust)>0) {
    $result=mysqli_query($conn,"SELECT id from groups where dust=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      if(($dust > 1) && ($dust < 70000)) { // Dust sensor is connected
	insert_empty_reading($group_id, $unix_time);
        $sql = "UPDATE readings SET dust=$dust WHERE group_id=$group_id AND ts=$unix_time";
        $result=mysqli_query($conn,$sql);
      }
    }    
  }
  // ------------------------------------------------------------------- Sewer
  if(strlen($sewer)>0) {
    $result=mysqli_query($conn,"SELECT id from groups where sewer=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      insert_empty_reading($group_id, $unix_time);
      $sql = "UPDATE readings SET sewer=$sewer WHERE group_id=$group_id AND ts=$unix_time";
      $result=mysqli_query($conn,$sql);
    }    
  }
  // ------------------------------------------------------------------- HCHO
  if(strlen($hcho)>0) {
    $result=mysqli_query($conn,"SELECT id from groups where hcho=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      insert_empty_reading($group_id, $unix_time);
      $sql = "UPDATE readings SET hcho=$hcho WHERE group_id=$group_id AND ts=$unix_time";
      $result=mysqli_query($conn,$sql);
    }    
  }
  // ------------------------------------------------------------------- CO
  if(strlen($co)>0) {
    $result=mysqli_query($conn,"SELECT id from groups where co=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      insert_empty_reading($group_id, $unix_time);
      $sql = "UPDATE readings SET co=$co WHERE group_id=$group_id AND ts=$unix_time";
      $result=mysqli_query($conn,$sql);
    }    
  }
  // ------------------------------------------------------------------- CO2
  if(strlen($co2)>0) {
    $result=mysqli_query($conn,"SELECT id from groups where co2=".$id);	   
    if(mysqli_errno()) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }    
    if(mysqli_num_rows($result)>0) {
      $row = mysqli_fetch_array($result);
      $group_id=$row['id'];
      insert_empty_reading($group_id, $unix_time);
      $sql = "UPDATE readings SET co2=$co2 WHERE group_id=$group_id AND ts=$unix_time";
      $result=mysqli_query($conn,$sql);
    }    
  }
    
  mysqli_close($conn);
  if($error!=true) {
    echo $unix_time;
  }
}

// --------------------------------------------------------------------- INSERT EMPTY READING
function insert_empty_reading($group_id, $unix_time) {
  global $conn;
  global $error;

  $result=mysqli_query($conn,"SELECT * FROM readings WHERE group_id=".$group_id." AND ts=$unix_time");
  $num_rows = mysqli_num_rows($result);
  if($num_rows<=0) {
    $sql = "INSERT into readings (temperature, humidity, dust, hcho, sewer, group_id, ts) VALUES (NULL, NULL, NULL, NULL, NULL, '$group_id', '$unix_time')";
    if(!mysqli_query($conn,$sql)) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
    }        
  }
}
?>
