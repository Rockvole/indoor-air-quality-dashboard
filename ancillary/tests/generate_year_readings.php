<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
$ip="foodaversions.com/dht22";
//$ip="davidlub/dht22";

$response = file_get_contents("http://$ip/initialize_core.php?core_id=68e07a&name=fake_data_2013&tz=America/Los_Angeles");
echo $response."\n";

echo "now=".Carbon::now()->toDateTimeString()."\n";
echo "tz=".date_default_timezone_get()."\n";
$curr_ts = Carbon::create(2013, 1, 1, 0, 0, 0);
$curr_temp=rand(0, 45)-10;
$curr_hum=rand(0,100);

do {
  $curr_ts_utc = $curr_ts->format('U');
  echo "curr_ts=".$curr_ts."||utc=".$curr_ts_utc."\n";
  $curr_temp=$curr_temp+(rand(0,4)-2);
  if($curr_temp<-10) $curr_temp=-10;
  if($curr_temp>35) $curr_temp=35;
  $curr_hum=$curr_hum+(rand(0,6)-3);
  if($curr_hum<0) $curr_hum=0;
  if($curr_hum>100) $curr_hum=100;

  $response = file_get_contents("http://$ip/get_reading.php?core_id=68e07a&temp=$curr_temp&hum=$curr_hum&unix_time=$curr_ts_utc");  
  echo $response."\n";
  
  $curr_ts->addHour();	
  $curr_year=$curr_ts->format('Y');  
} while($curr_year<2014);

?>
