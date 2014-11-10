<html>
<head>
  <title>Manage Event</title>
  <link rel="stylesheet" href="../html/stylesheet.css" type="text/css" >
</head>
  <script>
    function change_event(op) {
      document.event.op.value=op;
      document.event.name.value=document.getElementById('name').value;
      document.event.submit();      
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
$hour = filter_input(INPUT_GET, 'hour', FILTER_VALIDATE_INT);
$op = filter_input(INPUT_GET, 'op', FILTER_VALIDATE_INT);
$name = htmlspecialchars($_GET["name"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);

$dt = Carbon::createFromFormat($param_date_format, $start_date_param);
$dt->startOfDay()->addHours($hour);
$dt_utc = $dt->format('U');

if(strlen($ts)>0) {
  if($op==2) { // Delete event
    $sql = "DELETE from events where ts='$dt_utc'";
    if(!mysqli_query($conn,$sql)) {
       exit('Error: '.mysqli_error($conn));
    }
    echo "<div class='alerthead'>Event Deleted</div>";
  } else {
    if($op==1) {
      if(strlen($name)<=0) exit('Please enter Name');
      $sql_name="'$name'";
    } else {
      $sql_name="NULL";
    }
    
    $sql = "INSERT into events (name, core_id, ts) VALUES ($sql_name, '$id', '$dt_utc')";
    if(!mysqli_query($conn,$sql)) {
       exit('Error: '.mysqli_error($conn));
    }
    if($op==1) echo "<div class='alerthead'>".$name." added</div>";
      else echo "<div class='alerthead'>Event Finished</div>";
  }
}

echo "<h2>Manage Event (".$dt->format($user_date_format).")</h2>";
echo "<table border=0 class='form_table'>";
echo "<tr>";
echo "<td></td>";
echo "<td><b>Please name your event :</b></td>";
echo "<td width=100%></td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right style='vertical-align:top'>Name:</th>";
echo "<td style='vertical-align:top'><input type='text' name='name' maxlength=40 size=40 id='name' value='$name'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Sealed Bathroom door<br/>";
echo "e.g. Sealed sink with tape";
echo "</td>";
echo "</tr>";
echo "<tr><td>&nbsp;</td>";

echo "<td colspan=2>";
echo "<input type='button' value='Add Event' onclick='change_event(1);'>";
echo "&nbsp;&nbsp;";
echo "<input type='button' value='Delete Event' onclick='change_event(2);'>";
echo "&nbsp;&nbsp;";
echo "<input type='button' value='Finish Previous Event' onclick='change_event(3);'>";
echo "</td>";
echo "</tr>";

echo "</table>";  

// ------------------------------------------------------------------- Form
echo "<form action='manage_event.php' method='get' name='event'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='hour' value='$hour'>";
echo "<input type='hidden' name='name' value=''>";
echo "<input type='hidden' name='op' value=''>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "<input type='hidden' name='ts' value='$dt_utc'>";
echo "</form>";

echo "</body>\n";
echo "</html>\n";
mysqli_close($conn);  
?>    
