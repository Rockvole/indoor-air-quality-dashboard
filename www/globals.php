<?php
  define('TTF_DIR', '/usr/share/fonts/truetype/msttcorefonts/');
  $param_date_format='Y-m-d';
  $user_date_format='l, F jS Y H:i';
  $db_name = 'iaq';
  
  if(isset($_GET["id"])) {
    $conn=mysqli_connect("", "", "", $db_name);
    if (mysqli_connect_errno()) {
      exit('Failed to connect to MySQL: ' . mysqli_connect_error());
    } 
    $id = htmlspecialchars($_GET["id"]);  
    $result=mysqli_query($conn,"SELECT * FROM groups WHERE id=$id");
    $row = mysqli_fetch_array($result);
    $user_timezone=$row['tz'];
    $sensor_name=$row['name'];  
    $sensor_sewer=$row['sewer'];  
    $sensor_type_name=get_sensor_type_name($sensor_type);
  }
  
  function get_sensor_type_name($sensor_type) {
    switch($sensor_type) {
      case 0:
        return "Indoor Air Quality";
	break;
      case 1:
        return "Temperature & Humidity";
	break;
      case 2:
	return "Dust";
	break;
      case 3:
        return "Sewer";
	break;		
      case 4:
        return "Formaldehyde";
	break;
      default:
        exit("Unknown sensor type: $sensor_type");
    }    
  }
?>
