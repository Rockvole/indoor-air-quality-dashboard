<?php
include '../globals.php';
$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 

$filename = $argv[1];
$group_id = $argv[2];
if(!is_string($filename) || !is_numeric($group_id)) {
  exit("Command Line Syntax:\n".
       "upload_csv.sh <file to upload> <group_id to upload to>\n");  
}

$row = 1;
if (($handle = fopen($filename, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num_fields = count($data);
    if($row==1) {
      if(($num_fields==6) && (strcmp($data[0],"temperature")==0)) { // INDOOR AIR QUALITY
	echo "Found Readings\n";	
        $type=0;
      } else if(strcmp($data[0],"name")==0) { // ----------------------- EVENTS
	echo "Found Events\n";	
        $type=1;
      } else if(strcmp($data[0],"type")==0) { // ----------------------- LOCATIONS
	echo "Found Locations\n";	
        $type=2;
      } else {
        exit("File type not recognized");
      }
    } else { // After the first row
      echo "---------- Processing Row: $row\n";      
      for ($i=0; $i < $num_fields; $i++) {
	if(strlen($data[$i])<=0) {
	  $data[$i]="NULL";
        }
        echo $data[$i].",";
      }
      echo "\n";
      if($type==0 && $num_fields==6) { // ---------------------------- INDOOR AIR QUALITY
	$ts=$data[5];
        $sql = "SELECT * from readings where group_id = $group_id and ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
          $sql = "INSERT into readings (temperature, humidity, dust, sewer, hcho, group_id, ts) ".
	         "VALUES ($data[0], $data[1], $data[2], $data[3], $data[4], $group_id, $ts)";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        } else {
          echo "CANNOT UPDATE INDOOR AIR QUALITY TABLE\n";
        }
      } 
      if($type==1 && $num_fields==2) { // ---------------------------- EVENTS
	$ts=$data[1];
        $sql = "SELECT * from events where group_id = $group_id and ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
	  if(strcmp($data[0],"NULL")==0) $event_name=$data[0];
	    else $event_name="\"".$data[0]."\"";
          $sql = "INSERT into events (name, group_id, ts) ".
	         "VALUES ($event_name, $group_id, $ts)";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        } else {
          echo "CANNOT UPDATE EVENT TABLE\n";
        }
      }
      if($type==2 && $num_fields==3) { // ---------------------------- LOCATIONS
	$ts=$data[2];
        $sql = "SELECT * from locations WHERE type = ".$data[0]." AND group_id = $group_id AND ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
	  if(strlen($data[1])>0) {
	    $sql_type=$data[0];
	    $name="\"".$data[1]."\"";
            $sql = "INSERT into locations (type, name, group_id, ts) ".
	           "VALUES ($sql_type, $name, $group_id, $ts)";
            if($result=mysqli_query($conn,$sql)) {
              echo "SUCCESS: ".$sql."\n";		  
	    }
          }
        } else {
          echo "CANNOT UPDATE LOCATION TABLE\n";
        }
      }	
    }
    $row++;
  } // while
  fclose($handle);
}
?>
