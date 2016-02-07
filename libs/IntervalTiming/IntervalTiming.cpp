#include "IntervalTiming.h"

void IntervalTiming::startIntervalTime(unsigned long start_time_ms)
{
	_start_time_ms = start_time_ms;
}

bool IntervalTiming::isIntervalTimeComplete(unsigned long current_time_ms)
{
    if((current_time_ms - _start_time_ms) > _timing_interval_ms)
    {
		return true;
	}
	return false;	
}
