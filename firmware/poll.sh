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

  echo unix_time
  spark get rockvole4 unix_time

  echo stage
  spark get rockvole4 stage

  echo url
  spark get rockvole4 url
  
  sleep 30
done


