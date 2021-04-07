#check that current user is in the group "staff" (so that you can install pip modules without sudo)
getent group staff
#If not add your user to the group staff
sudo adduser <your user> staff
reboot machine to ensure group takes effect

# pip install (dont install as sudo)
pip install requests
pip install pyfoobot

sudo apt-get install python-yaml

