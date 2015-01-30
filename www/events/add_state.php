<html>
  <head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../html/stylesheet.css">
  </head>
  <script> 
    function add_state() {
      document.state.name.value=document.getElementById('name').value;	
      document.state.state_on.value=document.getElementById('state_on').value;
      document.state.state_off.value=document.getElementById('state_off').value;
      document.state.op.value=1;
      document.state.submit();      
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
if(!isset($_GET["location_id"])) exit("Must specify location_id parameter");
$id = htmlspecialchars($_GET["id"]);
$op = filter_input(INPUT_GET, 'op', FILTER_VALIDATE_INT);
$location_id = htmlspecialchars($_GET["location_id"]);
$name = htmlspecialchars($_GET["name"]);
$state_on = htmlspecialchars($_GET["state_on"]);
$state_off = htmlspecialchars($_GET["state_off"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$size_param = htmlspecialchars($_GET["size"]);

if(strlen($op)>0) {
  $sql = "INSERT into state_type (location_id, name, state_on, state_off) VALUES ($location_id, '$name', '$state_on', '$state_off')";
  if(!mysqli_query($conn,$sql)) {
    exit('Error: '.mysqli_error($conn));
  }
  echo "<div class='alerthead'>".$name." added</div>";
}
// --------------------------------------------------------------------- HTML STATE
echo "<table border=0 width=100%>";
echo "<tr>";
echo "<td><h2>Add State</h2></td>";
echo "<td align=right><img src='../images/back.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>";
echo "</table>";
echo "<table border=0 class='form_table'>";

echo "<tr>";
echo "<th align=right style='vertical-align:top'>Name:</th>";
echo "<td style='vertical-align:top'><input type='text' name='name' maxlength=40 size=40 id='name' value='$name'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Window</td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Fan</td>";
echo "</tr>";    

echo "<tr>";
echo "<th align=right style='vertical-align:top'>On State:</th>";
echo "<td style='vertical-align:top'><input type='text' name='state_on' maxlength=20 size=20 id='state_on' value='$state_on'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Open</td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. On</td>";
echo "</tr>";  

echo "<tr>";
echo "<th align=right style='vertical-align:top'>Off State:</th>";
echo "<td style='vertical-align:top'><input type='text' name='state_off' maxlength=20 size=20 id='state_off' value='$state_off'></td>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Closed<br/>";
echo "<td style='font-size:110%;font-style:italic;'>e.g. Off<br/>";
echo "</tr>";  

echo "<tr><td>&nbsp;</td>";

echo "<td colspan=2>";
echo "<input type='button' value='Add State' onclick='add_state();'>";
echo "</td>";
echo "</tr>";

echo "</table>";

// ------------------------------------------------------------------- Form
echo "<form action='add_state.php' method='get' name='state'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='location_id' value='$location_id'>";
echo "<input type='hidden' name='name' value=''>";
echo "<input type='hidden' name='state_on' value=''>";
echo "<input type='hidden' name='state_off' value=''>";
echo "<input type='hidden' name='op' value=''>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";
// ------------------------------------------------------------------- Back
echo "<form action='../dashboard.php' method='get' name='back'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='size' value='$size_param'>";
echo "<input type='hidden' name='start_date' value='$start_date_param'>";
echo "</form>";

mysqli_close($conn); 
?>
  </body>  
</html>
