#! /bin/bash
echo ------------------------------------------------------------------- build
g++ -g -c -Wall -I../SensorBase/ -std=c++11 -fPIC WSP2110.cpp -o WSP2110.so

rm -f WSP2110.so

