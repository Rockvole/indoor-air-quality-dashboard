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
#include <cmath>
#include "spark_wiring_tcpclient.h"

#define RL_VALUE                     50    //define the load resistance on the board, in kilo ohms
#define RO_CLEAN_AIR_FACTOR          9.83  //RO_CLEAR_AIR_FACTOR=(Sensor resistance in clean air)/RO,

#define CALIBRATION_SAMPLE_TIMES     50    //define how many samples you are going to take in the calibration phase
#define CALIBRATION_SAMPLE_INTERVAL  500   //define the time interval(in milliseconds) between each sample in the calibration phase

class MQ131 {
  public:
    MQ131() {};
    float calibrateInCleanAir(int mq_pin);
    float getResistanceCalculation(int raw_adc);    
    int getOzoneGasPercentage(float rs_ro_ratio, float ro);
    int getChlorineGasPercentage(float rs_ro_ratio, float ro);
    void startCalibrating();
  private:	
  	int calibration_count;
	float calibration_total;
    float CL2Curve[2] =  {56.01727602, -1.359048399}; 
    float O3Curve[2]  =  {42.84561841, -1.043297135}; 
	int getPercentage(float rs_ro_ratio, float ro, float *pcurve);
};
