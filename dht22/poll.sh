#! /bin/bash
echo ------------------------------------------------------------------- polling
while true
do
  echo
  echo ------------------------------
  date
  
  echo ip
  spark get rockvole3 ip
  
  echo temperature
  spark get rockvole3 temperature
  
  echo humidity
  spark get rockvole3 humidity

  echo url
  spark get rockvole3 url

  echo stage
  spark get rockvole3 stage
  
  sleep 300
done


