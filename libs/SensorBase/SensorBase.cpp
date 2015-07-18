#include "SensorBase.h"

void SensorBase::startSampling(unsigned long start_time_ms) 
{
  _start_time_ms = start_time_ms;
  _sample_sum = 0;
  _sampling_count = 0;
  _is_sampling_complete = false;
  _sampling_average = 0;
}

bool SensorBase::isTimeToRead(unsigned long current_time_ms)
{
    if((current_time_ms - _start_time_ms) > _sampling_interval_ms)
    {
		return true;
	}	
	return false;
}

void SensorBase::setAnalogRead(int raw_adc, unsigned long current_time_ms)
{
  if(!_is_sampling_complete) 
  {
    if(isTimeToRead(current_time_ms))
    {
      _sample_sum += getResistanceCalculation(raw_adc);
      _start_time_ms = current_time_ms;
      _sampling_count++;      
      _sampling_average = _sample_sum / _sampling_count;
      if(_sampling_count >= _sampling_frequency) _is_sampling_complete = true;
    }
  }
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
#define MAX_ADC_VALUE 4096 // Maximum value from the ADC (Arduino = 1024 / Spark = 4096)
float SensorBase::getResistanceCalculation(int raw_adc)
{
  return (MAX_ADC_VALUE * 1000 * _rl_value) / (raw_adc - _rl_value);
}

/*****************************  getPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
         pcurve      - pointer to the curve of the target gas
Output:  ppm of the target gas
Remarks: By using the slope and a point of the line. The x(logarithmic value of ppm) 
         of the line could be derived if y(rs_ro_ratio) is provided. As it is a 
         logarithmic coordinate, power of 10 is used to convert the result to non-logarithmic 
         value.
************************************************************************************/
int SensorBase::getPercentage(float ro, float *pcurve)
{
  return (double)(pcurve[0] * pow(((double)_sampling_average/ro), pcurve[1]));
}

void SensorBase::startCalibrating() {
    calibration_count=0;
    calibration_total=0;
}

/***************************** calibrateInCleanAir ****************************************
Input:   mq_pin - analog channel
Output:  Ro of the sensor
Remarks: This function assumes that the sensor is in clean air. It use  
         MQResistanceCalculation to calculates the sensor resistance in clean air 
         and then divides it with RO_CLEAN_AIR_FACTOR. RO_CLEAN_AIR_FACTOR is about 
         10, which differs slightly between different sensors.
************************************************************************************/ 
float SensorBase::calibrateInCleanAir(int raw_adc, int ppm, float *pcurve) {
  float calibration_avg;
  
  calibration_count++; 
  calibration_total += getResistanceCalculation(raw_adc);
  calibration_avg = calibration_total/calibration_count;
 
  return (long)calibration_avg * exp((log(pcurve[0]/ppm)/pcurve[1]));   
}
