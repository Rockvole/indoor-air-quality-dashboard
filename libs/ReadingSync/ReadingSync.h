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
      enum Stage                 // What Stage are we currently at
      {
        CONTINUE,                  // 0 Continue looping - no action required
        PRE_HEATING,               // 1 Turn on heaters to pre-heat sensors
        SAMPLING,                  // 2 We are currently sampling from sensors
        SEND_READING,              // 3 Send sensor readings to remote
        CALIBRATING,               // 4 User has pressed the calibrate button - so calibrate in clean air
        PRE_HEAT_CALIBRATING,      // 5 User has pressed the calibrate button so now we need to pre-heat first
        PRE_HEAT_USER_SAMPLING,    // 6 User has pressed the take sample button - so pre-heat first
        USER_SAMPLING              // 7 User has pressed the take sample button - so start sampling
      };       
      int secs_between_readings; // seconds between taking a reading
      int pre_heat_secs;         // total number of seconds we want to pre-heat for
      int last_read_secs;        // Last time a reading was taken (secs from start of day)
      int next_send_secs;        // When we will next send a reading (secs from start of day)

      // mins_between_readings = number of minutes to wait until you start sampling readings
      // phs = number of seconds for pre heating to take place
      // currentTime = current time in unix time
      ReadingSync(int mins_between_readings, int phs, int currentTime) {
        secs_between_readings=mins_between_readings * 60;
        pre_heat_secs=phs;
        last_read_secs = C_MAX_INT;
        next_send_secs = C_MAX_INT;
        srand(currentTime);
        _is_first_sampling_loop=true;
        _is_first_pre_heat_loop=true;
      }
      int getStartOfDayUnixTime(int currentTime);
      void startCalibrating(int currentTime);
      void startUserSampling(int currentTime);
      void setReadingSent();
      void setSamplingComplete();
      void setCalibratingComplete();
      Stage getStage(int currentTime);
      bool isFirstPreHeatLoop();
      bool isFirstSamplingLoop();
    private:
      ReadingSync() { }
      Stage _stage;
      bool _is_first_sampling_loop;
      bool _is_first_pre_heat_loop;
      int calibration_start_time;
      int user_sampling_start_time;
      int getSecsSinceStartOfDay(int currentTime);
      bool isTimeToPreHeat(int currentTime);
      bool isTimeToSample(int currentTime); 
      bool isTimeToSendReading(int currentTime);
      int getRemainingSecsUntilSample(int currentTime);
};
