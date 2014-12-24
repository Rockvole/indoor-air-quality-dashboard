#! /bin/bash
echo ------------------------------------------------------------------- build
spark compile . --saveTo firmware.bin

echo ------------------------------------------------------------------- check usb
sudo dfu-util -l

echo ------------------------------------------------------------------- flash
sudo spark flash --usb firmware.bin
rm firmware.bin


