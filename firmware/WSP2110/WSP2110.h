/*
  WSP2110 Formaldehyde Air Quality Sensor for the Spark Core

  connect the sensor as follows :

  A H A   >>> 5V
  B   >>> A0
  H       >>> GND
  B       >>> 10K ohm >>> GND
  
  Refactored into C++ class: David Warden Thomson
  Contribution: epierre
  Based on David Gironi http://davidegironi.blogspot.fr/2014/01/cheap-co2-meter-using-mq135-sensor-with.html

  License: Attribution-NonCommercial-ShareAlike 3.0 Unported (CC BY-NC-SA 3.0)
 
*/
#include "SensorBase.h"

class WSP2110: public SensorBase {    
  public:     
    WSP2110(int sampling_frequency, int sampling_interval_ms)
    : SensorBase(sampling_frequency, sampling_interval_ms, 6)
    {
    }  
    int getFormaldehydeGasPercentage(float rs_ro_ratio, float ro);
    int getHydrogenGasPercentage(float rs_ro_ratio, float ro);
    float calibrateInCleanAir(int raw_adc);
  private:
    float HCHOCurve[2] =    {1.478772974, -2.224808489}; 
    float H2Curve[2] =      {2.452065204, -2.282530712}; 
    float BenzolCurve[2] =  {5.59434996,  -6.062729607}; 
    float TolueneCurve[2] = {4.798168577, -8.100009624}; 
    float AlcoholCurve[2] = {5.4671937,   -6.114841859}; 
    WSP2110() {};     
};
