#! /bin/bash
echo ------------------------------------------------------------------- build
particle compile p . --saveTo firmware.bin

if [ "$?" = "0" ]; then
  echo ------------------------------------------------------------------- check usb
  sudo dfu-util -l

  echo ------------------------------------------------------------------- flash
  sudo particle flash --usb firmware.bin
  rm firmware.bin
else
  echo build failed
fi

