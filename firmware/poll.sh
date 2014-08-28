#! /bin/bash
echo ------------------------------------------------------------------- polling
while true
do
  echo
  echo ------------------------------
  date
  echo temperature
  spark get rockvole3 temperature
  
  echo humidity
  spark get rockvole3 humidity

  echo url
  spark get rockvole3 url
  
  sleep 30
done


