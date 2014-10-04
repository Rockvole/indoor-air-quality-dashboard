#include "ShinyeiPPD42NS.h"

float ShinyeiPPD42NS::getConcentration(unsigned long duration, unsigned long current_time_ms) {
	if(!_is_sampling_complete) 
	{
	  _low_pulse_occupancy = _low_pulse_occupancy + duration;
	  if((current_time_ms - _start_time_ms) > _sampling_interval_ms) 
	  {
	    float ratio = _low_pulse_occupancy / (_sampling_interval_ms * 10);
	    _concentration = 1.1 * pow(ratio,3) - 3.8 * pow(ratio,2) + 520 * ratio + 0.62; // using spec sheet curve
	    _is_sampling_complete = true;
      }
    }
	return _concentration;
}

void ShinyeiPPD42NS::startSampling(unsigned long start_time_ms) {
	_start_time_ms = start_time_ms;
	_low_pulse_occupancy = 0;
	_concentration = 0;
	_is_sampling_complete = false;
}

bool ShinyeiPPD42NS::isSamplingComplete() {
	return _is_sampling_complete;
}
