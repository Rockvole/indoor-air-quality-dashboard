#! /bin/bash
echo set_wsp $1 $2
date
spark call $1 setWspCalib $2
  
