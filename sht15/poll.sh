#! /bin/bash
echo ------------------------------------------------------------------- polling
while true
do
  echo
  echo ------------------------------ $1
  date
  echo temperature
  particle get $1 temperature
  
  echo humidity
  particle get $1 humidity

  echo unix_time
  particle get $1 unix_time

  echo stage
  particle get $1 stage

  echo url
  particle get $1 url

  echo ip
  particle get $1 ip
  
  sleep 60
done


