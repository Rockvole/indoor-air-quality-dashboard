#! /bin/bash
echo ------------------------------------------------------------------- polling
while true
do
  echo
  echo ------------------------------
  date
  echo temperature
  spark get rockvole5 temperature
  
  echo humidity
  spark get rockvole5 humidity

  echo unix_time
  spark get rockvole5 unix_time

  echo stage
  spark get rockvole5 stage

  echo tgs2602
  spark get rockvole5 tgs2602

  echo mq131
  spark get rockvole5 mq131

  echo url
  spark get rockvole5 url

  echo ip
  spark get rockvole5 ip
  
  sleep 60
done


