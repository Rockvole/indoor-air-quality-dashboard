/*
  Sensor Base Class
  
  Refactored into C++ class: David Warden Thomson
  Contribution: epierre
  Based on David Gironi http://davidegironi.blogspot.fr/2014/01/cheap-co2-meter-using-mq135-sensor-with.html

  License: Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
 
*/
#include <cmath>

class SensorBase {
  public:
    SensorBase(int sampling_frequency, int sampling_interval_ms, int rl_value) {
	  _sampling_frequency = sampling_frequency;
	  _sampling_interval_ms = sampling_interval_ms;
	  _rl_value = rl_value;
	  _is_sampling_complete = true;
	}  
    void startSampling(unsigned long start_time_ms);
    bool isSamplingComplete();
    float getResistanceCalculation(int raw_adc);
    float getResistanceCalculationAverage(int raw_adc, unsigned long current_time_ms); 
    void startCalibrating();
    float calibrateInCleanAir(int raw_adc, int ppm, float *pcurve);       
  protected:
    bool _is_sampling_complete;
    int _sampling_interval_ms;    
    int _sampling_frequency;      
    int _sampling_count;    
    int _rl_value;
	float _sample_sum;  
	unsigned long _start_time_ms;
  	int calibration_count;	
	float calibration_total;  		
    SensorBase() {};
	int getPercentage(float rs_ro_ratio, float ro, float *pcurve);    
};
