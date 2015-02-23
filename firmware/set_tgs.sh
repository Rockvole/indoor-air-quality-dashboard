#! /bin/bash
echo set_tgs $1 $2

spark call $1 setTgsCalib $2
  
