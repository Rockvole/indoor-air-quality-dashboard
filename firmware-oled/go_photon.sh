#! /bin/bash
echo ------------------------------------------------------------------- build
particle compile p . --saveTo firmware.bin

echo ------------------------------------------------------------------- check usb
sudo dfu-util -l

echo ------------------------------------------------------------------- flash
sudo particle flash --usb firmware.bin
rm firmware.bin


