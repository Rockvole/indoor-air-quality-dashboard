<?php
require_once ("Carbon/Carbon.php");
use Carbon\Carbon;
$ip="foodaversions.com/iaq";
//$ip="davidlub/iaq";

$response = file_get_contents("http://$ip/initialize_core.php?core_id=68e07a&name=fake_data_2013&tz=America/Los_Angeles");
echo $response."\n";

echo "now=".Carbon::now()->toDateTimeString()."\n";
echo "tz=".date_default_timezone_get()."\n";
$curr_ts = Carbon::create(2013, 1, 1, 0, 0, 0);
$curr_temp=rand(5, 40);
$curr_hum=rand(0,100);
$curr_dust=rand(0,40000);
$curr_ozone=rand(0,10);
$curr_chlorine=rand(0,10);
$curr_sewer=rand(0,100);

do {
  $curr_ts_utc = $curr_ts->format('U');
  echo "curr_ts=".$curr_ts."||utc=".$curr_ts_utc."\n";
  // Temperature
  $curr_temp=$curr_temp+(rand(0,4)-2);
  if($curr_temp<5) $curr_temp=5;
  if($curr_temp>40) $curr_temp=40;
  // Humidity
  $curr_hum=$curr_hum+(rand(0,6)-3);
  if($curr_hum<0) $curr_hum=0;
  if($curr_hum>100) $curr_hum=100;
  // Dust
  $curr_dust=$curr_dust+(rand(0,500)-250);
  if($curr_dust<0) $curr_dust=0;
  if($curr_dust>40000) $curr_dust=40000;  
  // Ozone
  $curr_ozone=$curr_ozone+(rand(0,2)-1);
  if($curr_ozone<0) $curr_ozone=0;
  if($curr_ozone>10) $curr_ozone=10;  
  // Chlorine
  $curr_chlorine=$curr_chlorine+(rand(0,2)-1);
  if($curr_chlorine<0) $curr_chlorine=0;
  if($curr_chlorine>10) $curr_chlorine=10;    
  // Sewer
  $curr_sewer=$curr_sewer+(rand(0,6)-3);
  if($curr_sewer<0) $curr_sewer=0;
  if($curr_sewer>100) $curr_sewer=100;

  $response = file_get_contents("http://$ip/get_reading.php?core_id=68e07a&temp=$curr_temp&hum=$curr_hum&dust=$curr_dust&ozone=$curr_ozone&chlorine=$curr_chlorine&sewer=$curr_sewer&unix_time=$curr_ts_utc");  
  echo $response."\n";
  
  $curr_ts->addHour();	
  $curr_year=$curr_ts->format('Y');  
} while($curr_year<2014);

?>
