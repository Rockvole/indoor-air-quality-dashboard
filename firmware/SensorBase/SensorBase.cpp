#include "SensorBase.h"

void SensorBase::startSampling(unsigned long start_time_ms) 
{
  _start_time_ms = start_time_ms;
  _sample_sum = 0;
  _sampling_count = 0;
  _is_sampling_complete = false;
}

float SensorBase::getResistanceCalculationAverage(int raw_adc, unsigned long current_time_ms)
{
  float sampling_average = 0;
  if(!_is_sampling_complete) 
  {
	if((current_time_ms - _start_time_ms) > _sampling_interval_ms)
	{
	  _sample_sum += getResistanceCalculation(raw_adc);
	  _start_time_ms = current_time_ms;
	  _sampling_count++;	  
	  sampling_average = _sample_sum / _sampling_count;
	  if(_sampling_count >= _sampling_frequency) _is_sampling_complete = true;
    }
  }
  return sampling_average;
}

bool SensorBase::isSamplingComplete() {
  return _is_sampling_complete;
}

/****************** getResistanceCalculation ****************************************
Input:   raw_adc - raw value read from adc, which represents the voltage
Output:  the calculated sensor resistance
Remarks: The sensor and the load resistor forms a voltage divider. Given the voltage
         across the load resistor and its resistance, the resistance of the sensor
         could be derived.
************************************************************************************/ 
float SensorBase::getResistanceCalculation(int raw_adc)
{
  return (1024 * 1000 * _rl_value) / (raw_adc - _rl_value);
}
