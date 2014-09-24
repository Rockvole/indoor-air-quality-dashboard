#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "idDHT22.h"
#include "MQ131.h"

#define INTERVAL_MINS 10
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5   // Number of times to sample sensor
#define BUZZER_PIN A4
#define CALIBRATE_BTN D4
#define READING_BTN D3
#define RED_LED A5
#define GREEN_LED A6
#define BLUE_LED A7

ReadingSync rs (INTERVAL_MINS, PRE_HEAT_SECS, Time.now());
RgbLedControl rgbLed (RED_LED, GREEN_LED, BLUE_LED);
HttpClient http;
void dht22_wrapper();
// DHT instantiate
idDHT22 DHT22(D2, dht22_wrapper);
MQ131 mq131;

int unix_time = 0;
int delay_ms = 0;
int reading_time = 0;
int sampling_count=0;
int heating_count=0;
int calibration_count=0;
int stage=0;

double temperature = 0;
double humidity = 0;

float mq131_Ro = 2.501;
float mq131_sample_sum = 0;
int   mq131_sample_avg = 0;
int   mq131_ozone = 0;
int   mq131_chlorine = 0;

int sewer = 0;
char url[200];

RgbLedControl::Color color;
http_request_t request;
http_response_t response;

void setup()
{
  // Register a Spark variable here
  Spark.variable("temperature", &temperature, DOUBLE);
  Spark.variable("humidity", &humidity, DOUBLE);
  Spark.variable("unix_time", &unix_time, INT);
  Spark.variable("stage", &stage, INT);
  Spark.variable("url", &url, STRING);

  pinMode(CALIBRATE_BTN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT);  
  //request.hostname = "foodaversions.com";
  request.ip = {192,168,1,130}; // davidlub
  request.port = 80;
  //Serial.begin(9600);
}

void dht22_wrapper() {
	DHT22.isrCallback();
}

void loop()
{
  color=rgbLed.OFF;
  unix_time=Time.now();
  delay_ms=200;
  stage=rs.getStage(unix_time);
  switch(stage) {  
	case rs.SAMPLING:
	  if(sampling_count==0) {
        sewer = analogRead(A0);		  
	  }
	  mq131_sample_sum  += mq131.getResistanceCalculation(analogRead(A3));
	  
	  sampling_count++;	  
	  mq131_sample_avg = mq131_sample_sum/sampling_count;

	  if(sampling_count>=SAMPLING_FREQUENCY) { // End of sampling - do final calculations
		  mq131_ozone = mq131.getOzoneGasPercentage(mq131_sample_avg, mq131_Ro);
		  mq131_chlorine = mq131.getChlorineGasPercentage(mq131_sample_avg, mq131_Ro);
		  rs.setSamplingComplete();
	  }
	  delay_ms=50;
	  break;
	case rs.SEND_READING:
	  sprintf(url, "/iaq/get_reading.php?core_id=%s&temp=%2f&hum=%2f&ozone=%i&chlorine=%i&sewer=%i&unix_time=%i", Spark.deviceID().c_str(), temperature, humidity, mq131_ozone, mq131_chlorine, sewer, reading_time);  
      request.path = url;
      http.get(request, response);	
	  rs.setReadingSent();
	  break;	    
	case rs.CALIBRATING:
	  calibration_count++;
	  if(calibration_count==CALIBRATION_SAMPLE_TIMES) { // Calibration Complete
		  beep(200);
		  rs.setCalibratingComplete();
	  }
	  color=rgbLed.BLUE;
	  break;	  	  
	case rs.PRE_HEAT_CALIBRATING:
	  calibration_count=0;
	case rs.PRE_HEATING:
	  color=rgbLed.ORANGE;
	  if(heating_count==0) {  // Take ambient temperature before pre-heating
	    read_dht22();
	  }
	  heating_count++;
	  break;
	case rs.BUTTON_SAMPLING:
	  break;	  
	case rs.CONTINUE:
	  sampling_count=0;
	  heating_count=0;
	  break;		    
	default:  
	  delay(1);
  }  
  rgbLed.setLedColor(delay_ms, 100, 3000, color);
  if(digitalRead(CALIBRATE_BTN)==LOW) {
	  rs.startCalibrating(unix_time);
  }
  delay(delay_ms);  
}

void read_dht22() {
  DHT22.acquire();
  while (DHT22.acquiring());	
	
  humidity = DHT22.getHumidity();
  temperature = DHT22.getCelsius();	
}

void beep(int delay_ms) {
	analogWrite(BUZZER_PIN, 255);
	delay(delay_ms);
	analogWrite(BUZZER_PIN, 0);
}
