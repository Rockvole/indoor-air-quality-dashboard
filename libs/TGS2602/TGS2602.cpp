#include "TGS2602.h"

/*****************************  getChlorineGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int TGS2602::getSewerGasPercentage(float ro)
{
  return getPercentage(ro,H2S_Curve);
}

int TGS2602::getTolueneGasPercentage(float ro)
{
  return getPercentage(ro,C7H8_Curve);
}

int TGS2602::getEthanolGasPercentage(float ro)
{
  return getPercentage(ro,C2H5OH_quarCurve);
}

int TGS2602::getAmmoniaGasPercentage(float ro)
{
  return getPercentage(ro,NH3_Curve);
}

float TGS2602::calibrateInCleanAir(int raw_adc) {
  SensorBase::calibrateInCleanAir(raw_adc, 1, C7H8_Curve);
}
