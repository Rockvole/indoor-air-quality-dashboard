#! /bin/bash
# Ubuntu 14.04
# install gd library needed by jpgraph
sudo apt-get install php5-gd
sudo service apache2 restart

# download jpgraph from sourceforge
tar -xvf jpgraph-3.0.7.tar.bz2

# copy to your php include path
sudo cp -r src /usr/share/php

sudo mv src jpgraph-3.0.7
sudo ln -s jpgraph-3.0.7 jpgraph

# install microsoft truetype fonts
sudo apt-get install ttf-mscorefonts-installer

# download Carbon from git

