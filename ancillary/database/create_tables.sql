create database iaq;
grant all on iaq.* to ''@'localhost';
use iaq;

create table cores (id INT NOT NULL AUTO_INCREMENT, core_name VARCHAR(24), name VARCHAR(20), tz VARCHAR(40), 
UNIQUE(core_name), UNIQUE(name), PRIMARY KEY (id));

create table readings (id INT NOT NULL AUTO_INCREMENT, temperature INT, humidity INT, 
dust INT, sewer INT, ozone INT, chlorine INT,
core_id INT, ts INT UNSIGNED, PRIMARY KEY (id), UNIQUE(core_id, ts));

create table locations (id INT NOT NULL AUTO_INCREMENT, 
location_name VARCHAR(40), 
room_name VARCHAR(60) NOT NULL,
core_id INT NOT NULL, 
ts INT UNSIGNED NOT NULL, 
UNIQUE(core_id, ts), PRIMARY KEY (id));

create table events (id INT NOT NULL AUTO_INCREMENT, 
name VARCHAR(40), 
core_id INT NOT NULL, 
ts INT UNSIGNED NOT NULL, 
UNIQUE(core_id, ts), PRIMARY KEY (id));

# ---------------------------- change time zone to UTC
# in /etc/mysql/my.cnf [mysqld] section
# add :
# default-time-zone = '+00:00'
# restart mysql
# sudo service mysql restart
#
# select @@global.time_zone, @@session.time_zone;
# +--------------------+---------------------+
# | @@global.time_zone | @@session.time_zone |
# +--------------------+---------------------+
# | +00:00             | +00:00              |
# +--------------------+---------------------+
