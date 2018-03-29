Getting Started
===============

1. Follow website install instructions (ancillary/installation directory)
 
2. Create the mysql database (ancillary/database directory)

3. Check the website is working

4. Generate an api key to use (this is simply a random identifier string for the awair)

    python generate_apikey.py
 
5. On front page of website press [Add New Sensor Group] button

6. Fill in form to add your awair (use the key you generated above as the core id)

# Request Readings

1. Create a .yaml file with your connection details (see blankbot.yaml for an example)

2. To fill in the graphs you can run the request_readings.py file

3. If your graph is shifted (todays reading should end very close to the current time) you can adjust this with the timeshift parameter in your yaml file.

4. Create a script to run each day and collect readings automatically.

# Examples

    python request_readings.py yesterday awair.yaml
    
This will collect yesterdays readings from the awair and send it to the website

    python request_readings.py 2018-03-01 2018-03-02 awair.yaml
    
To collect the readings through a range of dates
    
# crontab

    00 10 * * * python request_readings.py yesterday awair.yaml

Set-up crontab to run each day at 10am and request the previous days readings.
