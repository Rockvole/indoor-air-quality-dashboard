<html>
  <head>
    <title>Welcome Page</title>
    <link rel="stylesheet" type="text/css" href="html/stylesheet.css">    
  </head>
  
  <body>
<?php
include 'globals.php';

$conn=mysqli_connect("", "", "", $db_name);
if (mysqli_connect_errno()) {
  exit('Failed to connect to MySQL: ' . mysqli_connect_error());
} 
$result=mysqli_query($conn,"SELECT * from cores");

echo "<table border=0 width='100%'><tr>";
echo "<td><h2>Choose Core</h2></td>";
echo "<td><input type='button' value='Add New Core' onclick='location.href=\"add_new_core.php\"'></td></tr>";
echo "</tr></table>";

echo '<form action="year_cal.php" method="get" name="aq">';
echo '<input type="hidden" name="id" value="0">';
while($row = mysqli_fetch_array($result)) {
  echo '<input type="button" value="' . $row['name'] . '" onClick="document.aq.id.value='.$row['id'].';document.aq.submit();">';
  echo "<br/>";
}
echo '</form>';
   
mysqli_close($conn);   
?>
  </body>  
</html>


