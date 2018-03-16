import yaml
from pyfoobot import Foobot
from datetime import datetime

def read_config_file(config_file):
	with open(config_file, 'r') as ymlfile:
		cfg = yaml.load(ymlfile)
	cfg['foobot']['end_apikey']=cfg['foobot']['apikey'][-24:]
	return cfg['foobot']

def request_foobot_readings(start_timestamp, end_timestamp, config):
	index=config.get('index', 0)
	apikey=config['apikey']
	end_apikey=config['end_apikey']
	email=config['email']
	password=config['password']
	print("apikey=",apikey)
	print("end_apikey=",end_apikey)
	print("email=",email)
	print("password=",password)

	fb = Foobot(apikey, email, password)
	devices = fb.devices()
	print("devices=",devices)

	# Devices is a list, in case you have more than one foobot
	device = devices[index]
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
	if len(range_data['datapoints'])==0:
		raise ImportError("No data found for ",start_timestamp.strftime("%a, %d %b %Y"))
	return range_data

def get_intervals_shifted(range_data):
	# Make hashmap key the unix timestamp rounded down to 10 minutes. Ignore additional readings in the same 10 minutes
	sensor_data=dict()
	for datapoints in range_data['datapoints']:
		sd=dict()
		print("------------------------------------------")
		print("datapoints=",datapoints)
		for pos in range(len(range_data['sensors'])):
			sd[range_data['sensors'][pos]]=datapoints[pos]
		unix_time=datetime.fromtimestamp(sd['time'],tz=None)
		round_time=datetime(unix_time.year,unix_time.month,unix_time.day,unix_time.hour,round_down(unix_time.minute,10),0)
		#print("unix_time=",unix_time.strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",unix_time,"||",unix_time.strftime('%s'))
		#print("round_time=",round_time.strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",round_time,"||",round_time.strftime('%s'))
		if round_time.strftime('%s') not in sensor_data:
			sensor_data[round_time.strftime('%s')]=sd

	print("------------------------------------------")	
	return sensor_data

def validate_sensors(range_data):
	# Move list of sensors into map of sensor names => units
	units=dict()
	for pos in range(len(range_data['sensors'])):
		units[range_data['sensors'][pos]]=range_data['units'][pos]
	if(units['tmp']!='C'):
		print("Temperature must be in C to graph")
		exit()

def round_down(num, divisor):
    return num - (num%divisor)
    
