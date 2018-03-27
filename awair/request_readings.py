import os
import sys
sys.path.append(os.path.abspath("../foobot/"))
import commandline_tools
from datetime import datetime, timedelta
import awair_tools

C_MAX_DAYS = 10

(start_timestamp, end_timestamp) = commandline_tools.parse_args(sys.argv)

print("start_timestamp=",start_timestamp.isoformat())
print("st+24=",(start_timestamp+timedelta(hours=24)-timedelta(seconds=1)).isoformat())
#print("||end_timestamp=",end_timestamp.isoformat())

file_name=sys.argv[len(sys.argv)-1]
config = awair_tools.read_config_file(file_name)
print("config=",config)
# ----------------------------------- TIMELINE  
print("Timeline from yesterday to today")

range_data=awair_tools.request_awair_readings(start_timestamp, start_timestamp+timedelta(hours=24)-timedelta(seconds=1), config)
print(range_data)
