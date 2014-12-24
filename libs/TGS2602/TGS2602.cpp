#include "TGS2602.h"

/*****************************  getChlorineGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int TGS2602::getSewerGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,H2S_Curve);
}

int TGS2602::getTolueneGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,C7H8_Curve);
}

int TGS2602::getEthanolGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,C2H5OH_quarCurve);
}

int TGS2602::getAmmoniaGasPercentage(float rs_ro_ratio, float ro)
{
  return getPercentage(rs_ro_ratio,ro,NH3_Curve);
}

float TGS2602::calibrateInCleanAir(int raw_adc) {
  SensorBase::calibrateInCleanAir(raw_adc, 1, C7H8_Curve);
}
