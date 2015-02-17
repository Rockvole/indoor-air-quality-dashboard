#! /bin/bash
echo ------------------------------------------------------------------- polling
echo $1
while true
do
  echo
  echo ------------------------------
  date
  echo temperature
  spark get $1 temperature
  
  echo humidity
  spark get $1 humidity

  echo unix_time
  spark get $1 unix_time

  echo stage
  spark get $1 stage

  echo wsp2110
  spark get $1 wsp2110

  echo tgs2602
  spark get $1 tgs2602

  echo url
  spark get $1 url

  echo ip
  spark get $1 ip
  
  sleep 60
done


