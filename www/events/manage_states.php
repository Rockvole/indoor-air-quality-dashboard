<html>
  <head>
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../html/stylesheet.css">
  </head>
  <script> 
    function alter_state(name,state_on,state_off,op,state_id) {
      if(name==undefined)
        document.state.name.value=document.getElementById('name').value;	
      else document.state.name.value=name;
      if(state_on==undefined)
        document.state.state_on.value=document.getElementById('state_on').value;
      else document.state.state_on.value=state_on;
      if(state_off==undefined)
        document.state.state_off.value=document.getElementById('state_off').value;
      else document.state.state_off.value=state_off;
      document.state.state_id.value=state_id;
      if(op==undefined) op=1;
      document.state.op.value=op;
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
$state_id = filter_input(INPUT_GET, 'state_id', FILTER_VALIDATE_INT);
$op = filter_input(INPUT_GET, 'op', FILTER_VALIDATE_INT);
$location_id = htmlspecialchars($_GET["location_id"]);
$name = htmlspecialchars($_GET["name"]);
$state_on = htmlspecialchars($_GET["state_on"]);
$state_off = htmlspecialchars($_GET["state_off"]);
$start_date_param = htmlspecialchars($_GET["start_date"]);
$size_param = htmlspecialchars($_GET["size"]);
if(strlen($op)>0) {
  if(strlen($state_on)<=0) exit("Must specify On State parameter");
  if(strlen($state_off)<=0) exit("Must specify Off State parameter");
}

if(strlen($op)>0) {
  if($op==1) { // Add
    $sql = "INSERT into state_type (location_id, name, state_on, state_off) VALUES ($location_id, '$name', '$state_on', '$state_off')";
    if(!mysqli_query($conn,$sql)) {
      exit('Error: '.mysqli_error($conn));
    }
    echo "<div class='alerthead'>".$name." added</div>";
  } else if($op==2) { // Delete
    $sql = "DELETE from state_changes where state_type_id=$state_id";
    if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
    }    
    
    $sql = "DELETE from state_type where id=$state_id";
    if(!mysqli_query($conn,$sql)) {
     exit('Error: '.mysqli_error($conn));
    }

    echo "<div class='alerthead'>".$name." deleted</div>";
  }
}
// --------------------------------------------------------------------- HTML STATE
echo "<table border=0 width=100%>";
echo "<tr>";
echo "<td>&nbsp;</td>";
echo "<td align=right><img src='../images/back.png' onclick='back_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>";
echo "</table>";

echo "<div class='container'>";
echo "<table border=0 class='form_table' style='padding:0px 30px 0px 0px;'>";
echo "<tr>";
echo "<td colspan=4><h2>Add State</h2></td>";
echo "</tr>";

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
echo "<input type='button' value='Add State' onclick='alter_state();'>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<th align=right style='vertical-align:top'></th>";
echo "<th><b style='color:mediumorchid'>Note: The state applies to the Room location at the beginning of the day.</b></th>";
echo "</tr>"; 

echo "</table>";
echo "</div>";
// --------------------------------------------------------------------- COMMON HTML
echo "<div class='container'>";
echo "<table border=0>";
echo "<tr>";
echo "<td colspan=4><h2>Common States</h2></td>";
echo "</tr>";
echo "<tr>";
echo "<th style='text-align:left;width:100px;'>Name</th>";
echo "<th style='text-align:left;width:100px;'>On State</th>";
echo "<th style='text-align:left;width:100px;'>Off State</th>";
echo "<tr>";
echo "<td>Window</td><td>Open</td><td>Closed</td><td><input type='button' value='Add State' onclick='alter_state(\"Window\",\"Open\",\"Closed\");'></td>";
echo "</tr><tr>";
echo "<td>Door</td><td>Open</td><td>Closed</td><td><input type='button' value='Add State' onclick='alter_state(\"Door\",\"Open\",\"Closed\");'></td>";
echo "</tr><tr>";
echo "<td>Fan</td><td>On</td><td>Off</td><td><input type='button' value='Add State' onclick='alter_state(\"Fan\",\"On\",\"Off\");'></td>";
echo "</tr><tr>";
echo "<td>Dehumidifier</td><td>On</td><td>Off</td><td><input type='button' value='Add State' onclick='alter_state(\"Dehumidifier\",\"On\",\"Off\");'></td>";
echo "</tr>";
echo "</tr>";
echo "</table>";
echo "</div>";
// --------------------------------------------------------------------- DELETE HTML
echo "<hr/>";
echo "<div class='container'>";
echo "<table border=0>";
echo "<tr>";
echo "<td colspan=4><h2>Delete States</h2></td>";
echo "</tr>";
echo "<tr>";
echo "<th></th>";
echo "<th style='text-align:left;width:100px;'>Name</th>";
echo "<th style='text-align:left;width:100px;'>On State</th>";
echo "<th style='text-align:left;width:100px;'>Off State</th>";
echo "<tr>";
$result=mysqli_query($conn,"SELECT * from state_type where location_id=$location_id order by name");
while($row = mysqli_fetch_array($result)) {  
  echo "<tr>";
  echo "<td>\n";
  echo "<img src='../images/delete.gif' onclick='alter_state(\"".$row['name']."\",\"".$row['state_on']."\",\"".$row['state_off']."\",2,",$row['id'].")' height=30 width=30 style='cursor:pointer;'>\n";
  echo "</td>";  
  echo "<td>".$row['name']."</td>";
  echo "<td>".$row['state_on']."</td>";
  echo "<td>".$row['state_off']."</td>";
  echo "</tr>";
}

echo "</table>";
echo "</div>";
// ------------------------------------------------------------------- Form
echo "<form action='manage_states.php' method='get' name='state'>";
echo "<input type='hidden' name='id' value='$id'>";
echo "<input type='hidden' name='location_id' value='$location_id'>";
echo "<input type='hidden' name='name' value=''>";
echo "<input type='hidden' name='state_on' value=''>";
echo "<input type='hidden' name='state_off' value=''>";
echo "<input type='hidden' name='op' value=''>";
echo "<input type='hidden' name='state_id' value=''>";
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
