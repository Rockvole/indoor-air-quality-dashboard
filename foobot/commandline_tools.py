from datetime import datetime, timedelta

C_MAX_DAYS = 10

def syntax_message():
	print "Syntax:"
	print "  python request_readings.py <Start Date> <config.yaml>"
	print "  python request_readings.py today <config.yaml>"
	print "  python request_readings.py yesterday <config.yaml>"
	print ""
	print "  python request_readings.py <Start Date> <End Date> <config.yaml>"
	print "  Note: Date format is yyyy-mm-dd"
	
def parse_args(args):
	arg_len=len(args)
	if arg_len < 3 or arg_len > 4:
		syntax_message()
		exit()
	start_date=args[1]
	end_timestamp=None

	if start_date == "today":
		start_timestamp=datetime.now()
		start_timestamp=datetime(start_timestamp.year,start_timestamp.month,start_timestamp.day,0,0,0)
	elif start_date == "yesterday":
		start_timestamp=datetime.now()-timedelta(hours=24)
		start_timestamp=datetime(start_timestamp.year,start_timestamp.month,start_timestamp.day,0,0,0)
	else:	
		try:
			start_timestamp=datetime.strptime(start_date, '%Y-%m-%d')
		except ValueError:
			print("---------------- Invalid <Start Date>")
			syntax_message()
			exit()

		if(arg_len==4):
			end_date=sys.argv[2]
			try:
				end_timestamp=datetime.strptime(end_date, '%Y-%m-%d')
			except ValueError:
				print("---------------- Invalid <Start Date>")
				syntax_message()
				exit()
	return (start_timestamp, end_timestamp)		
			
