#include <application.h>
/**
 * Simple class for reading and writing
 * to Eeprom
 */

class SimpleEeprom {
    public:
      void writeFloat(float value, int offset);
      float readFloat(int offset);
    private:
      union float2bytes {float f; char b[sizeof(float)]; };
};
