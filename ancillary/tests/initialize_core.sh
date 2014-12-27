#! /bin/bash
source globals.sh

echo ---- All Sensor Group
curl "http://$ip/initialize_core.php?name=All_Sensor_Group&tz=America/Los_Angeles&core_id_1=TH_Sensor&temp_hum=1&core_id_2=Remaining_Sensors&dust=2&sewer=2&hcho=2&core_id_3=&core_id_4="

