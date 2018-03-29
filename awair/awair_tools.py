import os
import sys
import yaml
import pytz
from datetime import datetime, timedelta
import dateutil.parser
sys.path.append(os.path.abspath("../../pyawair/"))
from awair import awair
	
def request_awair_readings(start_timestamp, end_timestamp, config):
	api =  awair(config['email'], config['password'])
	devices = api.devices()
	device = devices[0]
	print(device)
	range_data = api.timeline(device['id'], start_timestamp.isoformat(), end_timestamp.isoformat())
	return range_data

def normalize_readings(range_data):
	# Make hashmap of the data
	normalized_data=[]
	for datapoints in range_data['data']:
		nd=dict()
		ts=dateutil.parser.parse(datapoints['timestamp'])
		#print("ts=",ts.isoformat(),"||",ts.strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",ts.strftime('%s'))
		ts2=ts-timedelta(hours=8)
		#print("ts2=",ts2.isoformat(),"||",ts2.strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",ts2.strftime('%s'))
		nd['time']=ts2.strftime('%s')
		nd['allpollu']=datapoints['score']
		nd['pm']=datapoints['sensor']['dust']
		nd['co2']=datapoints['sensor']['co2']
		nd['tmp']=datapoints['sensor']['temp']
		nd['hum']=datapoints['sensor']['humid']
		nd['voc']=datapoints['sensor']['voc']
		normalized_data.append(nd)
	return normalized_data	
