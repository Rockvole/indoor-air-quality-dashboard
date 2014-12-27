<html>
<head>
  <title>Manage Location</title>
  <link rel="stylesheet" href="../html/stylesheet.css" type="text/css" >
</head>
  <script>
    function change_room(op) {
      document.event.op.value=op;
      document.event.room_name.value=document.getElementById('room_name').value;
      document.event.type.value=1;
      document.event.submit();      
    }
    function change_position(op) {
      document.event.op.value=op;
      document.event.position.value=document.getElementById('position').value;
      document.event.type.value=2;
      document.event.submit();      
    }    
    function back_button() {
      document.back.submit();
    }    
  </script>
<body>
<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
include '../globals.php';

$conn=mysqli_connect("", "", "", $db_name);

// Check connection
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
if(!isset($_GET["id"])) exit("Must specify id parameter");
$id = htmlspecialchars($_GET["id"]);
$ts = htmlspecialchars($_GET["ts"]);
$op = filter_input(INPUT_GET, 'op', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_GET, 'type', FILTER_VALIDATE_INT);
$room_name = htmlspecialchars($_GET["room_name"]);
$position = htmlspecialchars($_GET["position"]);
$event_ts = Carbon::createFromTimeStamp($ts);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$size_param = htmlspecialchars($_GET["size"]);
$finish_event=false;

if(strlen($op)>0) {
  if($op==2) { // ------------------------------------------------------ DELETE
    $sql = "DELETE from locations where ts=$ts AND group_id=$id AND type=$type";
    if(!mysqli_query($conn,$sql)) {
       exit('Error: '.mysqli_error($conn));
    }
    if($type==2) echo "<div class='alerthead'>Position Deleted</div>";
      else echo "<div class='alerthead'>Room Deleted</div>";
  } else { // ---------------------------------------------------------- ADD
    if($type==2) {
      if(strlen($position)<=0) exit('Please enter Position');
      $name = $position;
    } else {
      if(strlen($room_name)<=0) exit('Please enter Room Name');
      $name = $room_name;
    }
    $sql = "INSERT into locations (type, name, group_id, ts) VALUES ($type, '$name', $id, $ts)";
    if(!mysqli_query($conn,$sql)) {
       exit('Error: '.mysqli_error($conn));
    }
    if(strlen($room_name)>0)
      echo "<div class='alerthead'>Room '$room_name' added</div>";
    else echo "<div class='alerthead'>Position '$position' added</div>";
  }
}
// Check if there is an existing Room
$result=mysqli_query($conn,"SELECT name FROM locations WHERE ts=$ts AND type=1 AND group_id=$id");
$row = mysqli_fetch_array($result);
$room_name=$row['name'];
if(strlen($room_name)>0) $delete_room=true;

// Retrieve previous Room events
$result=mysqli_query($conn,"SELECT name FROM locations WHERE ts=".
                             "  (SELECT max(ts) FROM locations where ts<$ts AND type=1 AND group_id=$id)".
			     "  AND type=1 AND core_id=$id");
$row = mysqli_fetch_array($result);
$prev_room_name=$row['name'];
if(!isset($prev_room_name)) $prev_room_name="&lt;NONE&gt;";

// Check if there is an existing Position
$result=mysqli_query($conn,"SELECT name FROM locations WHERE ts=$ts AND type=2 AND group_id=$id");
$row = mysqli_fetch_array($result);
$position=$row['name'];
if(strlen($position)>0) $delete_position=true;

// Retrieve previous Position events
$result=mysqli_query($conn,"SELECT name FROM locations WHERE ts=".
                             "  (SELECT max(ts) FROM locations WHERE ts<$ts AND type=2 AND group_id=$id)".
			     "  AND type=2 AND core_id=$id");
$row = mysqli_fetch_array($result);
$prev_position=$row['name'];
if(!isset($prev_position)) $prev_position="&lt;NONE&gt;";

echo "<table border=0 width=100% >";
echo "<tr>";
echo "<td><h2>Manage Location (".$event_ts->format($user_date_format).")</h2></td>";
echo "<td align=right><img src='../images/back.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>";
echo "</table>";

echo "<table border=0 class='form_table' style='float:left;margin:10px;'>";
if(!$delete_room) {
  echo "<tr>";
  echo "<th align=right style='vertical-align:top'>Previous Room:</th>";
  echo "<td style='vertical-align:top'>$prev_room_name</td>";
  echo "</tr>";
}
echo "<tr>";
echo "<th align=right style='vertical-align:top'>Room Name:</th>";
echo "<td style='vertical-align:top'><input type='text' name='room_name' maxlength=40 size=40 id='room_name' value='$room_name'></td>";
echo "</tr>";
if(!$delete_room) {
  echo "<tr>";
  echo "<td>";
  echo "<td style='font-size:110%;font-style:italic;'>e.g. Main Kitchen<br/>";
  echo "e.g. Basement Bathroom<br/>";
  echo "</td>";    
  echo "</tr>";
}
echo "<tr><td>&nbsp;</td>";
echo "<td colspan=2>";
if($delete_room) {
  echo "<input type='button' value='Delete Room' onclick='change_room(2);'>";
} else {
  echo "<input type='button' value='Change Room' onclick='change_room(1);'>";
}
echo "</td>";
echo "</tr>";
echo "</table>";  

echo "<table border=0 class='form_table' style='float:left;margin:10px;'>";
if(!$delete_position) {
  echo "<tr>";
  echo "<th align=right style='vertical-align:top'>Previous Position:</th>";
  echo "<td style='vertical-align:top'>$prev_position</td>";
  echo "</tr>";
}
echo "<tr>";
echo "<th align=right style='vertical-align:top'>Position:</th>";
echo "<td style='vertical-align:top'><input type='text' name='position' maxlength=40 size=40 id='position' value='$position'></td>";
echo "</tr>";
if(!$delete_position) {
  echo "<tr>";
  echo "<td>";
  echo "<td style='font-size:110%;font-style:italic;'>e.g. By Window<br/>";
  echo "e.g. By Door<br/>";
  echo "</td>";    
  echo "</tr>";
}
echo "<tr><td>&nbsp;</td>";
echo "<td colspan=2>";
if($delete_position) {
  echo "<input type='button' value='Delete Position' onclick='change_position(2);'>";
} else {
  echo "<input type='button' value='Change Position' onclick='change_position(1);'>";
}
echo "</td>";
echo "</tr>";
echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='manage_location.php' method='get' name='event'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='room_name' value=''>";
echo "<input type='hidden' name='position' value=''>";
echo "<input type='hidden' name='op' value=''>";
echo "<input type='hidden' name='ts' value='$ts'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='type' value=''>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";
// ------------------------------------------------------------------- Back
echo "<form action='../dashboard.php' method='get' name='back'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    
