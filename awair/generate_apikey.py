import string
import random

def id_generator(size=24, chars=string.ascii_uppercase + string.digits):
	return ''.join(random.choice(chars) for _ in range(size))

print("An apikey is not needed for Awair, here we generate a 24 digit apikey.")
print("Add this generated key to the yaml file to identify this device when sending its readings.")
print("This same key would be used to register the device on the dashboard website in the [Add New Sensor Group] button (under Core Id).")
print("")
print(id_generator())
