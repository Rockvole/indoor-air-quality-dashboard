#! /bin/bash
echo set_wsp $1 $2
date
particle call $1 setWspCalib $2
  
