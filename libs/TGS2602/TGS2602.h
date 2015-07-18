/*
  MQ131 Ozone Air Quality Sensor for the Spark Core

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

class TGS2602: public SensorBase {
  public:
    TGS2602(int sampling_frequency, int sampling_interval_ms)
    : SensorBase(sampling_frequency, sampling_interval_ms, 1)
    {
    }
    int getSewerGasPercentage(float ro);
    int getTolueneGasPercentage(float ro);
    int getEthanolGasPercentage(float ro); 
    int getAmmoniaGasPercentage(float ro);
    float calibrateInCleanAir(int raw_adc);    
  private:
    float C7H8_Curve[2]    =  {37.22590719,   2.078062258}; //TGS2602     (0.3;1)( 0.8;10) (0.4;30)
    float H2S_Curve[2]    =  {0.05566582614,-2.954075758}; //TGS2602     (0.8,0.1) (0.4,1) (0.25,3)
    float C2H5OH_quarCurve[2]  =  {0.5409499131,-2.312489623}; //TGS2602   (0.75,1) (0.3,10) (0.17,30)  
    float NH3_Curve[2]  =  {0.585030495,  -3.448654502  }; //TGS2602    (0.8,1) (0.5,10) (0.3,30) 
    TGS2602() {};
};
