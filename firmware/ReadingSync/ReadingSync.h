#include <cstdlib>

#define SECS_IN_DAY 86400
#define C_MAX_INT   2147483647
/**
 * This class helps us to synchronize taking readings
 * We can take a reading each time an interval is reached
 * Then we wait a random amount of time until we send
 * that reading.
 * 
 * This is to reduce load on the server since we dont
 * dont want all synchronized readings to be sent at
 * that exact time.
 */
class ReadingSync {
	public:
	  int mins_between_readings;	// minutes between taking a reading
	  int last_read_mins;           // Last time a reading was taken (mins from start of day)
	  int next_send_mins;           // When we will next send a reading (mins from start of day)

      ReadingSync(int mbr, int currentTime) {
		mins_between_readings=mbr;
		last_read_mins = C_MAX_INT;
	    next_send_mins = C_MAX_INT;	  		
	    srand(currentTime);		   
      }
	  int getStartOfDayUnixTime(int currentTime);
	  bool isTimeToTakeReading(int currentTime); 
	  bool isTimeToSendReading(int currentTime);
	  void setReadingSent();
	private:
      ReadingSync() { }	
	  int getMinsSinceStartOfDay(int currentTime);
};
