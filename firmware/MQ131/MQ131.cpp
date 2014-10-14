#include "MQ131.h"

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

float MQ131::calibrateInCleanAir(int raw_adc) {
  SensorBase::calibrateInCleanAir(raw_adc, 10, O3Curve);
}
