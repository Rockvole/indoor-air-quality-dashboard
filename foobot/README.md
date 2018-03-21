Getting Started
===============

1. Follow website install instructions (ancillary/installation directory)
 
2. Create the mysql database (ancillary/database directory)

3. Check the website is working

4. Request a foobot api key [Foobot Website](https://api.foobot.io/apidoc/index.html)
 
5. On front page of website press [Add New Sensor Group] button

6. Fill in form to add your foobot

# Request Readings

1. Create a .yaml file with your connection details (see blankbot.yaml for an example)

2. To fill in the graphs you can run the request_readings.py file

3. Create a script to run each day and collect readings automatically.

# Examples

    python request_readings.py yesterday foobot.yaml
    
This will collect yesterdays readings from the foobot and send it to the website

    python request_readings.py 2018-03-01 2018-03-02 foobot.yaml
    
To collect the readings through a range of dates
    
# crontab

    00 10 * * * python request_readings.py yesterday foobot.yaml

Set-up crontab to run each day at 10am and request the previous days readings.
