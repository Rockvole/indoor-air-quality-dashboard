#include <application.h>

class RgbLedControl {
	public:
	  enum Color 
	  {
		OFF,
		YELLOW,
		ORANGE,
		BLUE
	  };
	  RgbLedControl(int red_pin, int green_pin, int blue_pin) {
		  pinMode(red_pin, OUTPUT);
		  pinMode(green_pin, OUTPUT);
		  pinMode(blue_pin, OUTPUT);
		  _red_pin = red_pin;
		  _green_pin = green_pin;
		  _blue_pin = blue_pin;
	  }
	  void setLedColor(int delay_ms, int led_ms_on, int led_ms_off, Color color);
	private:
	  RgbLedControl() { }
	  int _red_pin;
	  int _green_pin;
	  int _blue_pin;
	  bool led_state=false;
	  int current_ms=0;
};
