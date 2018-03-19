# Installation on Ubuntu 16.04
sudo apt-get install apache2
sudo add-apt-repository ppa:ondrej/php
sudo add-apt-repository ppa:ondrej/apache2

# install microsoft truetype fonts
sudo apt-get install ttf-mscorefonts-installer

# download Carbon from git
sudo cp -r /work/git/web/Carbon/src/Carbon /usr/share/php/

sudo apt-get install php7.2 php5.6 php5.6-mysql php5.6-gd php-gettext php5.6-mbstring php-xdebug libapache2-mod-php5.6 libapache2-mod-php7.2

# Switch from php5.6 to php7.2:
#sudo a2dismod php5.6 ; sudo a2enmod php7.2 ; sudo service apache2 restart

# Switch from php7.2 to php5.6:
sudo a2dismod php7.2 ; sudo a2enmod php5.6 ; sudo service apache2 restart

# download jpgraph from sourceforge
tar -xvf jpgraph-3.0.7.tar.bz2

# copy to your php include path
sudo cp -r src /usr/share/php

cd /usr/share/php
sudo mv src jpgraph-3.0.7
sudo ln -s jpgraph-3.0.7 jpgraph

