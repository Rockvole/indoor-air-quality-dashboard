<?php
include '../globals.php';
$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 

$filename = $argv[1];
$core_id = $argv[2];
if(!is_string($filename) || !is_numeric($core_id)) {
  exit("Command Line Syntax:\n".
       "upload_csv.sh <file to upload> <core_id to upload to>\n");  
}

$row = 1;
if (($handle = fopen($filename, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $valid_row=true;
    $num_fields = count($data);
    if($num_fields==3) {
      $row++;
      for ($i=0; $i < $num_fields; $i++) {
        if(!is_numeric($data[$i])) $valid_row=false;
        echo $data[$i].",";
      }
      echo "\n";
      if($valid_row) {
        $sql = "SELECT * from readings where core_id = $core_id and ts = $data[2]";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
          $sql = "INSERT into readings (temperature, humidity, core_id, ts) VALUES ($data[0], $data[1], $core_id, $data[2])";
          echo "NOT IMPLEMENTED: ".$sql."\n";	
        } else {
          $sql = "   UPDATE readings set temperature=$data[0], humidity=$data[1] ".
	         "    WHERE core_id=$core_id and ts=$data[2]";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        }
      }
    }
  }
  fclose($handle);
}
?>
