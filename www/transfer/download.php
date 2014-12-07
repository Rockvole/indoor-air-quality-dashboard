<html>
  <head>
    <title>Download</title>
    <link rel="stylesheet" type="text/css" href="../html/stylesheet.css">    
    <style>
      th {text-align:right;}
      td {padding: 4px 14px 4px 14px}
    </style>
  </head>
  <script>
    function home_button() {
      document.dl.action = '../index.php';
      document.dl.submit();
    }
    function download_csv() {
      document.dl.action = "download_csv.php";
      document.dl.start_time.value=document.getElementById('start_time').value;
      document.dl.end_time.value=document.getElementById('end_time').value;      
      document.dl.submit();
    }
  </script>
  <body>
<?php
include '../globals.php';

echo "<table border=0 width='100%' >";
echo "<tr>";
echo "<td colspan=2><h2>Download Csv</h2></td>";
echo "</td><td width='400' style='vertical-align:top'>";
echo "<span style='padding:4px 10px 4px 10px;font-size:20px;font-weight:bold;color:#CC6666;vertical-align:top;'>";
echo $sensor_name;  
echo "</span>";
echo "</td>";
echo "<td width=400 align=right><img src='../images/home.png' onclick='home_button();' height=30 width=30 style='cursor:pointer;'></td>\n";
echo "</tr>";
echo "</table>";

echo "<table border=0 class='form_table'>";
echo "<tr>";
echo "<th align=right>Start Unix time:</th>";
echo "<td><input type='text' name='start_time' maxlength=10 size=10 id='start_time' value='$start_time'></td>";
echo "<td style='font-size:110%;font-style:italic;'>Unix time to start downloading at e.g. 1388502000<br/>Leave blank for all</td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right style='vertical-align:top'>End Unix Time:</th>";
echo "<td style='vertical-align:top'><input type='text' name='end_time' maxlength=10 size=10 id='end_time' value='$end_time'></td>";
echo "<td style='font-size:110%;font-style:italic;'>Unix time to end downloading at e.g. 1388556000<br/>Leave blank for all";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right style='vertical-align:top'></th>";
echo "<td style='vertical-align:top'></td>";
echo "<td style='font-size:110%;font-style:italic;'>You may use the upload_csv.php utility to upload";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<th align=right style='vertical-align:top'></th>";
echo "<td style='vertical-align:top'><input type='button' value='Download CSV' onclick='download_csv();'></td>";
echo "<td style='font-size:110%;font-style:italic;'>";
echo "</td>";
echo "<td align=right></td>\n";
echo "</tr>";
echo "</table>";

echo "<form action='download_csv.php' method='get' name='dl'>\n";
echo "<input type='hidden' name='id' value='$id'>\n";
echo "<input type='hidden' name='start_time' value='$start_time'>\n";
echo "<input type='hidden' name='end_time' value='$end_time'>\n";
echo "</form>\n";
   
?>
  </body>  
</html>
