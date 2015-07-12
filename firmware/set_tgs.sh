#! /bin/bash
echo set_tgs $1 $2
date
particle call $1 setTgsCalib $2
  
