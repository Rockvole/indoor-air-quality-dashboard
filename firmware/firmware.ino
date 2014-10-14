#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "SimpleEeprom.h"
#include "idDHT22.h"
#include "TGS2602.h"
#include "MQ131.h"
#include "ShinyeiPPD42NS.h"
#include "function_pulseIn.h"

#define INTERVAL_MINS 10
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5      // Number of times to sample sensor
#define SAMPLING_INTERVAL_MS 50   // Number of ms between samples

#define SENSOR_TGS2602          A0
#define SENSOR_MQ131            A3
#define BUZZER_PIN              A4
#define RED_LED                 A5
#define GREEN_LED               A6
#define BLUE_LED                A7

#define DUST_PIN                D1
#define READING_BTN             D3
#define CALIBRATE_BTN           D4

ReadingSync rs (INTERVAL_MINS, PRE_HEAT_SECS, Time.now());

SimpleEeprom flash;

union float2bytes {float f; char b[sizeof(float)]; };
int unix_time = 0;
int delay_ms = 0;
int reading_time = 0;
int heating_count=0;
int calibration_count=0;
int stage=0;
bool first_sample=true;

// --------------------------------------------------------------------- RGD LED
RgbLedControl rgbLed (RED_LED, GREEN_LED, BLUE_LED);
RgbLedControl::Color color;

// --------------------------------------------------------------------- DHT22
#define DUST_SAMPLE_INTERVAL_MS 30000
void dht22_wrapper();
idDHT22 DHT22(D2, dht22_wrapper);
double temperature = 0;
double humidity = 0;

// --------------------------------------------------------------------- Shinyei PPD42NS
ShinyeiPPD42NS dust(DUST_SAMPLE_INTERVAL_MS);
float dust_concentration = 0;

// --------------------------------------------------------------------- TGS2602
TGS2602 tgs2602(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float tgs2602_Ro = 2.511;
int   tgs2602_sample_avg = 0;
int   tgs2602_sewer = 0;
char  tgs2602_display[10];

// --------------------------------------------------------------------- MQ131
MQ131 mq131(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float mq131_Ro = 2.501;
int   mq131_sample_avg = 0;
int   mq131_ozone = 0;
int   mq131_chlorine = 0;
char  mq131_display[10];

// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;


void setup()
{
  mq131_Ro = flash.readFloat(0);
  tgs2602_Ro = flash.readFloat(4);
  // Register a Spark variable here
  Spark.variable("temperature", &temperature, DOUBLE);
  Spark.variable("humidity", &humidity, DOUBLE);
  Spark.variable("unix_time", &unix_time, INT);
  Spark.variable("stage", &stage, INT);
  //Spark.variable("tgs2602", &tgs2602_sample_avg, INT);
  sprintf(tgs2602_display,"%.3f",tgs2602_Ro);
  Spark.variable("tgs2602", &tgs2602_display, STRING);  
  //Spark.variable("mq131", &mq131_sample_avg, INT);
  sprintf(mq131_display,"%.3f",mq131_Ro);
  Spark.variable("mq131", &mq131_display, STRING);  
  Spark.variable("url", &url, STRING);

  pinMode(CALIBRATE_BTN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT); 
  pinMode(DUST_PIN, INPUT);   
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
	  {
	    unsigned long current_ms = millis();	
	    if(first_sample) {
		  first_sample=false;  
          tgs2602.startSampling(current_ms);
          mq131.startSampling(current_ms);
          dust.startSampling(current_ms);
          reading_time = unix_time;
	    }
        if(!tgs2602.isSamplingComplete()) {
		  tgs2602_sample_avg = tgs2602.getResistanceCalculationAverage(analogRead(SENSOR_TGS2602), current_ms);
        }	    
	    if(!mq131.isSamplingComplete()) {
	      mq131_sample_avg = mq131.getResistanceCalculationAverage(analogRead(SENSOR_MQ131), current_ms);
	    }
	    if(!dust.isSamplingComplete()) {
          unsigned long duration = pulseIn(DUST_PIN, LOW);
          dust_concentration = dust.getConcentration(duration, current_ms);          
		}
	    if(mq131.isSamplingComplete() && dust.isSamplingComplete() && tgs2602.isSamplingComplete()) {
	      tgs2602_sewer = tgs2602.getSewerGasPercentage(tgs2602_sample_avg, tgs2602_Ro);			
		  mq131_ozone = mq131.getOzoneGasPercentage(mq131_sample_avg, mq131_Ro);
		  mq131_chlorine = mq131.getChlorineGasPercentage(mq131_sample_avg, mq131_Ro);			
		  rs.setSamplingComplete();			
		}
	    delay_ms=0;
      }
	  break;
	case rs.SEND_READING:
	  {
	    sprintf(url, "/iaq/get_reading.php?core_id=%s&temp=%2f&hum=%2f&ozone=%i&chlorine=%i&sewer=%i&dust=%2f&unix_time=%i", Spark.deviceID().c_str(), temperature, humidity, mq131_ozone, mq131_chlorine, tgs2602_sewer, dust_concentration, reading_time);  
        request.path = url;
        http.get(request, response);	
	    rs.setReadingSent();
      }
	  break;	    
	case rs.CALIBRATING:
	  {
	    delay_ms=CALIBRATION_SAMPLE_INTERVAL;
        calibration_count++;
	    if(calibration_count<=1) {
		  tgs2602.startCalibrating();			
		  mq131.startCalibrating();
	    }
	    tgs2602_Ro = tgs2602.calibrateInCleanAir(analogRead(SENSOR_TGS2602));
	    mq131_Ro = mq131.calibrateInCleanAir(analogRead(SENSOR_MQ131));
	    if(calibration_count==CALIBRATION_SAMPLE_FREQUENCY) { // Calibration Complete
		  beep(200);
		  rs.setCalibratingComplete();
	      sprintf(mq131_display,"%.3f",mq131_Ro);		  
		  flash.writeFloat(mq131_Ro, 0);
		  flash.writeFloat(tgs2602_Ro, 4);
	    }
	    color=rgbLed.BLUE;
      }
	  break;	  	  
	case rs.PRE_HEAT_CALIBRATING:
	  calibration_count=0;
	case rs.PRE_HEATING:
	  {
	    color=rgbLed.ORANGE;
	    if(heating_count==0) {  // Take ambient temperature before pre-heating
	      read_dht22();
	    }
	    heating_count++;
      }
	  break;
	case rs.BUTTON_SAMPLING:
	  break;	  
	case rs.CONTINUE:
	  {
	    first_sample=true;
	    heating_count=0;
      }
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

