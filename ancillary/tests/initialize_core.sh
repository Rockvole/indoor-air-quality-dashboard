#! /bin/bash
source globals.sh

curl "http://$ip/initialize_core.php?core_id=43Re3&name=fake_day_readings&sensors=0&tz=America/Los_Angeles"

curl "http://$ip/initialize_core.php?core_id=67Tf9&name=fake_day_readings_ht&sensors=1&tz=America/Los_Angeles"
