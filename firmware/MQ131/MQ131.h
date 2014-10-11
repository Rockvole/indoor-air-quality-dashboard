/*
  MQ131 Ozone Air Quality Sensor for the Spark Core

  connect the sensor as follows :

  A H A   >>> 5V
  B	  >>> A0
  H       >>> GND
  B       >>> 10K ohm >>> GND
  
  Refactored into C++ class: David Warden Thomson
  Contribution: epierre
  Based on David Gironi http://davidegironi.blogspot.fr/2014/01/cheap-co2-meter-using-mq135-sensor-with.html

  License: Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
 
*/
#include "SensorBase.h"

#define RL_VALUE                     50    //define the load resistance on the board, in kilo ohms

class MQ131: public SensorBase {
  public:
    MQ131(int sampling_frequency, int sampling_interval_ms)
    : SensorBase(sampling_frequency, sampling_interval_ms, RL_VALUE)
    {
	}  
    int getOzoneGasPercentage(float rs_ro_ratio, float ro);
    int getChlorineGasPercentage(float rs_ro_ratio, float ro);
    float calibrateInCleanAir(int raw_adc);    
  private:	
    float CL2Curve[2] =  {56.01727602, -1.359048399}; 
    float O3Curve[2]  =  {42.84561841, -1.043297135}; 
    MQ131() {};    
};
