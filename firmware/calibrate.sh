#! /bin/bash
echo ------------------------------------------------------------------- calibrate
echo $1

spark call $1 calibrate $2
  
