#include "SimpleEeprom.h"

void SimpleEeprom::writeFloat(float value, int offset) {
    float2bytes f2b;
    f2b.f = value;
    for(int i=0; i< sizeof(float); i++)
      EEPROM.write(offset+i, f2b.b[i]);
}

float SimpleEeprom::readFloat(int offset) {
    float2bytes f2b;
    for(int i=0; i< sizeof(float); i++)
      f2b.b[i] = EEPROM.read(offset+i);
    return f2b.f;  
}

