#! /bin/bash
echo ------------------------------------------------------------------- build
rm ReadingSync

g++ -Wall -W -Werror ReadingSync.cpp -o ReadingSync

echo ------------------------------------------------------------------- run
./ReadingSync


