<?php
echo "<table border=0 width=100%>";
echo "<tr>";
for($i=0;$i<24;$i++) {
  echo "<td style='text-align:center;font-size:11px;cursor:pointer;background-color:#CC6666;' ";
  echo "onclick='location.href=\"events/manage_event.php?id=$id&start_date=$start_date_param&hour=$i\"'>";
  echo sprintf("%1$02d",$i);
  echo "</td>";
  
}
echo "</tr>";
echo "</table>";

?>
