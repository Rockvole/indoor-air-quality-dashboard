<html>
  <head>
    <title>Welcome Page</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">    
    <style>
      th {text-align:right;}
      td {padding: 4px 14px 4px 14px}
    </style>
  </head>
  <script>
    function click_button(id, action) {
      if(action==null)
        document.aq.action = "year_cal.php";
      else document.aq.action = action;
      
      document.aq.id.value=id;
      document.aq.submit();
    }
  </script>
  <body>
<?php
include 'globals.php';

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
$result=mysqli_query($conn,"SELECT * from cores");

echo "<table border=0 width='100%'><tr>";
echo "<td><h2>Choose Sensor</h2></td>";
echo "<td><input type='button' value='Add New Sensor' onclick='click_button(null,\"add_new_sensor.php\");'></td></tr>";
echo "</tr></table>\n";
echo "<br/>";

echo "<form action='year_cal.php' method='get' name='aq'>\n";
echo "<input type='hidden' name='id' value='0'>\n";

// Icons from thenounproject
echo "<table border=0 >\n";
while($row = mysqli_fetch_array($result)) {
  echo "<tr>\n";
  echo "<th>".$row['name']."</th>\n";
  echo "<td><img src='html/calendar.png' onclick='click_button(".$row['id'].");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "<td><img src='html/graph.png' onclick='click_button(".$row['id'].",\"dashboard.php\");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "<td><img src='html/barchart.png' onclick='click_button(".$row['id'].",\"events/event_monthly.php\");' height=40 width=40 style='cursor:pointer;'></td>";
  echo "</tr>\n";
}
echo "</table>\n";
echo "</form>\n";
   
mysqli_close($conn);   
?>
  </body>  
</html>


