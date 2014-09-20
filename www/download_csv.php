<?php
include 'globals.php';

if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=file.csv");
header("Pragma: no-cache");
header("Expires: 0");

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
$result=mysqli_query($conn,"SELECT * from readings where core_id=$id order by ts");
echo "temperature,humidity,ozone,chlorine,sewer,unix_time\n";
while($row = mysqli_fetch_array($result)) {
  echo $row['temperature'].",".$row['humidity'].",".$row['ozone'].",".$row['chlorine'].",".$row['sewer'].",".$row['ts']."\n";
}
mysqli_close($conn);
?>

