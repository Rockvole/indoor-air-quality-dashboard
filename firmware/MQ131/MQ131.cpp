#include "MQ131.h"


/****************** getResistanceCalculation ****************************************
Input:   raw_adc - raw value read from adc, which represents the voltage
Output:  the calculated sensor resistance
Remarks: The sensor and the load resistor forms a voltage divider. Given the voltage
         across the load resistor and its resistance, the resistance of the sensor
         could be derived.
************************************************************************************/ 
float MQ131::getResistanceCalculation(int raw_adc)
{
  return ( ((float)RL_VALUE*(1023-raw_adc)/raw_adc));
}

/***************************** calibrateInCleanAir ****************************************
Input:   mq_pin - analog channel
Output:  Ro of the sensor
Remarks: This function assumes that the sensor is in clean air. It use  
         MQResistanceCalculation to calculates the sensor resistance in clean air 
         and then divides it with RO_CLEAN_AIR_FACTOR. RO_CLEAN_AIR_FACTOR is about 
         10, which differs slightly between different sensors.
************************************************************************************/ 
float MQ131::calibrateInCleanAir(int mq_pin) {
  int i;
  float val=0;
 
  for (i=0;i<CALIBRATION_SAMPLE_TIMES;i++) {            //take multiple samples
    val += getResistanceCalculation(analogRead(mq_pin));
    delay(CALIBRATION_SAMPLE_INTERVAL);
  }
  val = val/CALIBRATION_SAMPLE_TIMES;                   //calculate the average value
 
  val = val/RO_CLEAN_AIR_FACTOR;                        //divided by RO_CLEAN_AIR_FACTOR yields the Ro 
                                                        //according to the chart in the datasheet 
 
  return val;	
}

/*****************************  getChlorineGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int MQ131::getChlorineGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,CL2Curve);
}

/*****************************  getOzoneGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int MQ131::getOzoneGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,O3Curve);
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
int MQ131::getPercentage(float rs_ro_ratio, float ro, float *pcurve)
{
  return (double)(pcurve[0] * pow(((double)rs_ro_ratio/ro), pcurve[1]));
}
