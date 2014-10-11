#include "MQ131.h"

void MQ131::startCalibrating() {
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
float MQ131::calibrateInCleanAir(int mq_pin) {
  float calibration_avg;
  
  calibration_count++; 
  calibration_total += getResistanceCalculation(analogRead(mq_pin));
  calibration_avg = calibration_total/calibration_count;
 
  return (long)calibration_avg * exp((log(O3Curve[0]/10)/O3Curve[1]));	
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
