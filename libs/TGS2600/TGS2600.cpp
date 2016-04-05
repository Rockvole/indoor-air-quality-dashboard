#include "TGS2600.h"

/*****************************  getHydrogenGasPercentage **********************************
Input:   rs_ro_ratio - Rs divided by Ro
Output:  ppm of the target gas
Remarks: This function passes different curves to the getPercentage function which 
         calculates the ppm (parts per million) of the target gas.
************************************************************************************/ 
int TGS2600::getHydrogenGasPercentage(float ro)
{
  return getPercentage(ro,H2_terCurve);
}

int TGS2600::getButaneGasPercentage(float ro)
{
  return getPercentage(ro,C4H10_Curve);
}

int TGS2600::getEthanolGasPercentage(float ro)
{
  return getPercentage(ro,C2H5OH_secCurve);
}

float TGS2600::calibrateInCleanAir(int raw_adc) {
  SensorBase::calibrateInCleanAir(raw_adc, 1, H2_terCurve);
}
