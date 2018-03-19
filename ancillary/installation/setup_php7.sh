# NOTE : Use PHP7.2 or above (7.0 does not work)

# Installation on Ubuntu 16.04
sudo apt-get install apache2
sudo add-apt-repository ppa:ondrej/php
sudo add-apt-repository ppa:ondrej/apache2
sudo apt-get update

# install microsoft truetype fonts
sudo apt-get install ttf-mscorefonts-installer

# download Carbon from git
sudo cp -r /work/git/web/Carbon/src/Carbon /usr/share/php/

sudo apt-get install php7.2 php-pear php7.2-curl php7.2-dev php7.2-gd php7.2-mbstring php7.2-zip php7.2-mysql php7.2-xml

sudo a2dismod php7.0 ; sudo a2enmod php7.2 ; sudo service apache2 restart

# Use either jpgraph 3.0.7 with workaround to allow php7.2
https://groups.google.com/forum/#!topic/jpgraph/ZgFLjda26fY

# Or use jpgraph 4.2.0 (unfortunately background images do not appear on graphs)
# download jpgraph from sourceforge
tar -xvf jpgraph-4.2.0.tar.bz2

# copy to your php include path
sudo cp -r jpgraph-4.2.0 /usr/share/php

cd /usr/share/php
sudo mv jpgraph-4.2.0 /usr/share/php/
sudo ln -s jpgraph-4.2.0/src jpgraph
