#include "RgbLedControl.h"

void RgbLedControl::setLedColor(int delay_ms, int led_ms_on, int led_ms_off, Color color) {
    int red_rgb=0;
    int green_rgb=0;
    int blue_rgb=0;     
    current_ms = current_ms + delay_ms;
    
    if(led_ms_on>0) {
      if(led_state) {
        if(current_ms>=led_ms_on) {
            led_state=false;
            current_ms=0;
        }
      } else {
        if(current_ms>=led_ms_off) {
            led_state=true;
            current_ms=0;
        }
      }
      if(led_state) {
        switch(color) {
          case OFF:
            break;
          case YELLOW:
            red_rgb=255;
            green_rgb=255;
            //Serial.println("yellow");
            break;
          case ORANGE:
            red_rgb=237;
            green_rgb=120;
            blue_rgb=6;
            //Serial.println("orange");
            break;
          case BLUE:
            blue_rgb=255;
            //Serial.println("blue");
            break;        
          case RED:
            red_rgb=255;
            //Serial.println("red");
            break; 
          case INTERNAL:
            // Onboard LED
            break;                
        }
      }
    }
    if(color==INTERNAL) {  
      digitalWrite(D7, led_state ? HIGH : LOW);
    } else {
      analogWrite(_red_pin, red_rgb);
      analogWrite(_green_pin, green_rgb);
      analogWrite(_blue_pin, blue_rgb);   
    }
}
