<?php
include 'globals.php';

if(!isset($_GET["core_id"])) exit("Must specify core_id parameter");
if(!isset($_GET["unix_time"])) exit("Must specify unix_time parameter");
if(!isset($_GET["temp"])) exit("Must specify temp parameter");
if(!isset($_GET["hum"])) exit("Must specify hum parameter");

$core_id = htmlspecialchars($_GET["core_id"]);
$unix_time = htmlspecialchars($_GET["unix_time"]);
$dust = htmlspecialchars($_GET["dust"]);
$temp = htmlspecialchars($_GET["temp"]);
$hum = htmlspecialchars($_GET["hum"]);
$hcho = htmlspecialchars($_GET["hcho"]);
$sewer = htmlspecialchars($_GET["sewer"]);
$error=false;
$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  echo 'Failed to connect to MySQL: ' . mysqli_connect_error();
  $error=true;
} else {
  // ------------------------------------------------------------------- RETRIEVE CORE INFO
  $result=mysqli_query($conn,"SELECT * from cores where core_name='".$core_id."'");	
  if(mysql_errno()) {
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
  // ------------------------------------------------------------------- INSERT READING
  $sql = "INSERT into readings (temperature, humidity, dust, hcho, sewer, core_id, ts) VALUES ('$temp', '$hum', '$dust', '$hcho', '$sewer', '$id', '$unix_time')";
  if(!mysqli_query($conn,$sql)) {
      exit('Error: '.mysqli_error($conn));
      $error=true;
  }
  mysqli_close($conn);
  if($error!=true) {
      echo $unix_time;
  }
}
?>
