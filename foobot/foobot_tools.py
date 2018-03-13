import yaml
from pyfoobot import Foobot

def request_foobot_readings(start_timestamp, end_timestamp, config_file):
	global end_apikey
	
	with open(config_file, 'r') as ymlfile:
		cfg = yaml.load(ymlfile)
	apikey=cfg['foobot']['apikey']
	end_apikey=apikey[-24:]

	email=cfg['foobot']['email']
	password=cfg['foobot']['password']
	print("apikey=",apikey)
	print("end_apikey=",end_apikey)
	print("email=",email)
	print("password=",password)

	fb = Foobot(apikey, email, password)
	devices = fb.devices()
	print("devices=",devices)

	# Devices is a list, in case you have more than one foobot
	device = devices[0]
	print("device=",device)

	# Get the most recent sample
	#latest_data = device.latest()
	#print("latest_data=",latest_data)

	# Get data from the last hour
	#last_hour_data = device.data_period(3600, 600)
	#print("last_hour_data=",last_hour_data)

	# Get data for a data range
	range_data = device.data_range(start=start_timestamp.strftime('%s'),
								   end=end_timestamp.strftime('%s'),
                                   sampling=0)
	return range_data
