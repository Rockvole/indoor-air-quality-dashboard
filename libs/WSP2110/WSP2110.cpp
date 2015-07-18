#include "WSP2110.h"

/*****************************  getFormaldehydeGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int WSP2110::getFormaldehydeGasPercentage(float ro)
{
  return getPercentage(ro,HCHOCurve);
}

/*****************************  getFormaldehydeGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int WSP2110::getHydrogenGasPercentage(float ro)
{
  return getPercentage(ro,H2Curve);
}

float WSP2110::calibrateInCleanAir(int raw_adc) {
  return SensorBase::calibrateInCleanAir(raw_adc, 10, HCHOCurve);
}
