# include "ReadingSync.h"
// Determine if its time to take a reading 
// Determine if its time to send a reading

int ReadingSync::getStartOfDayUnixTime(int currentTime) {
	int daysSinceEpoch=currentTime / (SECS_IN_DAY);
	return (daysSinceEpoch * SECS_IN_DAY);
}

int ReadingSync::getMinsSinceStartOfDay(int currentTime) {
	return (currentTime - getStartOfDayUnixTime(currentTime)) / 60;
}

bool ReadingSync::isTimeToTakeReading(int currentTime) {
	int minsSinceStartOfDay = getMinsSinceStartOfDay(currentTime);
	int remainingMins = minsSinceStartOfDay % mins_between_readings;
	bool timeToTakeReading = (remainingMins == 0) && (minsSinceStartOfDay!=last_read_mins);
	if(timeToTakeReading) {
		last_read_mins = minsSinceStartOfDay;
		next_send_mins = minsSinceStartOfDay + (rand() % (mins_between_readings-2));
	}
	return timeToTakeReading;
}

bool ReadingSync::isTimeToSendReading(int currentTime) {
    return getMinsSinceStartOfDay(currentTime) >= next_send_mins;
}

void ReadingSync::setReadingSent() {
	next_send_mins = C_MAX_INT;
}
