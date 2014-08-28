#! /bin/bash
echo ------------------------------------------------------------------- build
rm -f ReadingSync.so
g++ -g -c -Wall -fPIC ReadingSync.cpp -o ReadingSync.so

rm -f UnitTest
g++ -Wall -W -Werror -I. UnitTest.cpp ReadingSync.so -o UnitTest

echo ------------------------------------------------------------------- run
./UnitTest


