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
    $all_numeric=true;
    $num_fields = count($data);
    if($row==1) {
      if(($num_fields==3) && (strcmp($data[0],"temperature")==0)) { // - TEMPERATURE / HUMIDITY
        $type=99;
      } else if(($num_fields==6) && (strcmp($data[0],"temperature")==0)) { // INDOOR AIR QUALITY
        $type=0;
      } else if(strcmp($data[0],"name")==0) { // ----------------------- EVENTS
        $type=1;
      } else if(strcmp($data[0],"location_name")==0) { // -------------- LOCATIONS
        $type=2;
      } else {
        exit("File type not recognized");
      }
    } else { // After the first row
      for ($i=0; $i < $num_fields; $i++) {
        if(!is_numeric($data[$i])) {
	  $all_numeric=false;
        }
        echo $data[$i].",";
      }
      echo "\n";
      if($all_numeric && $type==99 && $num_fields==3) { // --------------------------- TEMPERATURE / HUMIDITY
	$ts=$data[2];
        $sql = "SELECT * from readings where core_id = $core_id and ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
          $sql = "INSERT into readings (temperature, humidity, core_id, ts) VALUES ($data[0], $data[1], $core_id, $data[2])";
          echo "NOT IMPLEMENTED: ".$sql."\n";	
        } else {
          $sql = "   UPDATE readings set temperature=$data[0], humidity=$data[1] ".
	         "    WHERE core_id=$core_id and ts=$ts";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        }
      }
      if($all_numeric && $type==0 && $num_fields==6) { // ---------------------------- INDOOR AIR QUALITY
	$ts=$data[5];
        $sql = "SELECT * from readings where core_id = $core_id and ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
          $sql = "INSERT into readings (temperature, humidity, dust, sewer, hcho, core_id, ts) ".
	         "VALUES ($data[0], $data[1], $data[2], $data[3], $data[4], $core_id, $ts)";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        } else {
          echo "CANNOT UPDATE INDOOR AIR QUALITY TABLE\n";
        }
      } 
      if(!$all_numeric && $type==1 && $num_fields==2) { // ---------------------------- EVENTS
	$ts=$data[1];
        $sql = "SELECT * from events where core_id = $core_id and ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
	  if(strlen($data[0])>0) $event_name="\"".$data[0]."\"";
	    else $event_name="NULL";	  
          $sql = "INSERT into events (name, core_id, ts) ".
	         "VALUES ($event_name, $core_id, $ts)";
          if($result=mysqli_query($conn,$sql)) {
            echo "SUCCESS: ".$sql."\n";		  
          }
        } else {
          echo "CANNOT UPDATE EVENT TABLE\n";
        }
      }
      if(!$all_numeric && $type==2 && $num_fields==3) { // ---------------------------- LOCATIONS
	$ts=$data[2];
        $sql = "SELECT * from locations WHERE type = 1 AND core_id = $core_id AND ts = $ts";
        echo $sql."\n";	      
      
        $result=mysqli_query($conn,$sql);
        $num_rows=mysqli_num_rows($result);
        if($num_rows==0) {
	  if(strlen($data[1])>0) {
	    $name="\"".$data[1]."\"";
            $sql = "INSERT into locations (type, name, core_id, ts) ".
	           "VALUES (1, $name, $core_id, $ts)";
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
