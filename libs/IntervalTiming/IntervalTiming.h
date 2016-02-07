#ifndef Included_IntervalTiming_H
#define Included_IntervalTiming_H

class IntervalTiming {
  public:
    IntervalTiming(int timing_interval_ms) {
		_timing_interval_ms = timing_interval_ms;
	}	
	void startIntervalTime(unsigned long start_time_ms);
	bool isIntervalTimeComplete(unsigned long current_time_ms);
  protected:
    int _timing_interval_ms;
    unsigned long _start_time_ms;
};

#endif //Included_IntervalTiming_H
