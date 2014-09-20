#! /bin/bash
echo ------------------------------------------------------------------- polling
while true
do
  echo
  echo ------------------------------
  date
  echo temperature
  spark get rockvole4 temperature
  
  echo humidity
  spark get rockvole4 humidity

  echo sewer
  spark get rockvole4 sewer

  echo url
  spark get rockvole4 url
  
  sleep 30
done


