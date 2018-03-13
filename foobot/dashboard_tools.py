from datetime import datetime
import requests

def send_requests(sensor_data, config):
	# ---------------------------------------------------------------------- REQUESTS
	for round_time in sorted(sensor_data.iterkeys()):
		readings=sensor_data[round_time]
		url = '{}/iaq/get_reading.php?unix_time={}&temp={}&hum={}&hcho=&sewer={}&dust={}&core_id={}&uptime={}' \
		.format(config['domain'],round_time, readings['tmp'], readings['hum'], readings['voc'], readings['pm'], config['end_apikey'], 0)
		print("url=",url)
		print("round_time=",datetime.fromtimestamp(float(round_time),tz=None).strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",round_time, \
			  "unix_time=",datetime.fromtimestamp(float(readings['time']),tz=None).strftime("%a, %d %b %Y %H:%M:%S +0000"),"||",readings['time'])
		print("readings=",readings)
		r = requests.get(url)
		#print r.status_code
		#print r.headers
		print("response=",r.content)
		
