#include <queue>
#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "SimpleEeprom.h"
#include "PietteTech_DHT.h"
#include "TGS2602.h"
#include "WSP2110.h"
#include "ShinyeiPPD42NS.h"

SYSTEM_MODE(SEMI_AUTOMATIC);
SYSTEM_THREAD(ENABLED);

#define INTERVAL_MINS 10
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5      // Number of times to sample sensor
#define SAMPLING_INTERVAL_MS 50   // Number of ms between samples

#define SENSOR_TGS2602          A0
#define SENSOR_WSP2110          A3
#define BUZZER_PIN              A4
#define RED_LED                 A5
#define GREEN_LED               A4
#define BLUE_LED                A7

#define DUST_PIN                D1
#define DHT_PIN                 D2 // D0 // use NOT_CONNECTED if needed  
#define USER_SAMPLING_BTN       D3
#define CALIBRATE_BTN           D4

ReadingSync rs (INTERVAL_MINS, PRE_HEAT_SECS, Time.now());
SimpleEeprom flash;

struct reading_struct {
    int    reading_time = 0;    
    double temperature = 0;
    double humidity = 0;
    int    tgs2602_sewer = 0; 
    int    wsp2110_hcho = 0;    
    float  dust_concentration = 0;    
};

std::queue<reading_struct> q;
reading_struct reading;
int unix_time = 0;
int delay_ms = 0;
int calibration_count=0;
int stage=0;
bool acquired_ip=true;
int uptime_start=0;
float temp_float;
bool reading_sent=false;

// --------------------------------------------------------------------- RGB LED
RgbLedControl rgbLed (RED_LED, GREEN_LED, BLUE_LED);
RgbLedControl::Color color;

// --------------------------------------------------------------------- DHT22
void dht_wrapper();
PietteTech_DHT DHT(DHT_PIN, DHT22, dht_wrapper);

// --------------------------------------------------------------------- TGS2602
TGS2602 tgs2602(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float tgs2602_Ro = 340000.0;
char  tgs2602_display[20];

// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;
char hostname[] = "www.foodaversions.com";
char ip_display[16];

// --------------------------------------------------------------------- Shinyei PPD42NS
#define DUST_SAMPLE_INTERVAL_MS 30000
ShinyeiPPD42NS dust(DUST_SAMPLE_INTERVAL_MS);

// --------------------------------------------------------------------- WSP2110
WSP2110 wsp2110(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float wsp2110_Ro = 300000.0;
char  wsp2110_display[20];

void setup()
{
  temp_float = flash.readFloat(0);
  if(temp_float==temp_float) // Valid Number
    wsp2110_Ro = temp_float;
  temp_float = flash.readFloat(4);
  if(temp_float==temp_float) // Valid Number
    tgs2602_Ro = temp_float;
  
  pinMode(D7, OUTPUT);
  pinMode(CALIBRATE_BTN, INPUT_PULLUP);
  pinMode(USER_SAMPLING_BTN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT); 
  pinMode(DUST_PIN, INPUT);

  //request.ip = {192, 168, 1, 110}; // david-mint
  request.hostname = "www.foodaversions.com";
  request.port = 80;

  // Register Particle variables
  Particle.variable("ip", ip_display, STRING);  
  Particle.variable("temperature", &reading.temperature, DOUBLE);
  Particle.variable("humidity", &reading.humidity, DOUBLE);
  Particle.variable("url", url, STRING);
  Particle.variable("stage", &stage, INT);
  Particle.variable("unix_time", &unix_time, INT);
  
  sprintf(tgs2602_display,"%.2f",tgs2602_Ro);
  Particle.variable("tgs2602", tgs2602_display, STRING);
  sprintf(wsp2110_display,"%.2f",wsp2110_Ro);
  Particle.variable("wsp2110", wsp2110_display, STRING);    
  
  // Register Particle Functions
  Particle.function("calibrate", calibrate);
  Particle.function("sample", sample);
  Particle.function("setWspCalib", setWspCalib);
  Particle.function("setTgsCalib", setTgsCalib);

  //Serial.begin(9600);
  Particle.connect();
}

void dht_wrapper() {
    DHT.isrCallback();
}

void loop()
{
  Particle.process();
  if(color!=rgbLed.RED) color=rgbLed.OFF;
  unix_time=Time.now();
  if(uptime_start<1000000000) uptime_start = unix_time;  
  stage=rs.getStage(unix_time);
  delay_ms=200;  

  switch(stage) {
    case rs.USER_SAMPLING:
    case rs.SAMPLING:
      {
        unsigned long current_ms = millis();
        if(rs.isFirstSamplingLoop()) {
          wsp2110.startSampling(current_ms);
          tgs2602.startSampling(current_ms);
          dust.startSampling(current_ms);
          reading.reading_time = unix_time;
        }
        if(!wsp2110.isSamplingComplete() && wsp2110.isTimeToRead(current_ms)) {
          wsp2110.setAnalogRead(analogRead(SENSOR_WSP2110), current_ms);
        }        
        if(!tgs2602.isSamplingComplete() && tgs2602.isTimeToRead(current_ms)) {
          tgs2602.setAnalogRead(analogRead(SENSOR_TGS2602), current_ms);
        }       
        if(!dust.isSamplingComplete()) {
          unsigned long duration = pulseIn(DUST_PIN, LOW);
          reading.dust_concentration = dust.getConcentration(duration, current_ms);          
        }
        if(wsp2110.isSamplingComplete() && dust.isSamplingComplete() && tgs2602.isSamplingComplete()) {
          reading.wsp2110_hcho = wsp2110.getFormaldehydeGasPercentage(wsp2110_Ro);            
          reading.tgs2602_sewer = tgs2602.getSewerGasPercentage(tgs2602_Ro);            
          rs.setSamplingComplete();
          q.push(reading);
        }
        delay_ms=0;
      }
      break;
    case rs.SEND_READING:
      {
		if(Particle.connected()) {
          reading_struct curr_reading;
          do {
            reading_sent=false;
            curr_reading = q.front();
            sprintf(url, "/iaq/get_reading.php?unix_time=%i&temp=%.2f&hum=%.2f&hcho=%i&sewer=%i&dust=%.2f&core_id=%s&uptime=%i", 
                       curr_reading.reading_time,
                       curr_reading.temperature, curr_reading.humidity, curr_reading.wsp2110_hcho, 
                       curr_reading.tgs2602_sewer, curr_reading.dust_concentration,
                       Particle.deviceID().c_str(), (unix_time-uptime_start));  
            request.path = url;
            response.body = "";
            http.get(request, response);
            char read_time_chars[12];
            sprintf(read_time_chars, "%d", curr_reading.reading_time);
            String read_time_str(read_time_chars);
            if(read_time_str.equals(response.body)) {
              q.pop();
              reading_sent=true;
            }
          } while(reading_sent && !q.empty());
        }
        rs.setReadingSent();      
      }
      break;        
    case rs.CALIBRATING:
      {
        delay_ms=CALIBRATION_SAMPLE_INTERVAL;
        calibration_count++;
        if(calibration_count<=1) {
          wsp2110.startCalibrating();            
          tgs2602.startCalibrating();
        }
        wsp2110_Ro = wsp2110.calibrateInCleanAir(analogRead(SENSOR_WSP2110));        
        tgs2602_Ro = tgs2602.calibrateInCleanAir(analogRead(SENSOR_TGS2602));
        if(calibration_count==CALIBRATION_SAMPLE_FREQUENCY) { // Calibration Complete
          beep(200);
          rs.setCalibratingComplete();
          sprintf(wsp2110_display,"%.2f",wsp2110_Ro);         
          flash.writeFloat(wsp2110_Ro, 0);
          sprintf(tgs2602_display,"%.2f",tgs2602_Ro);         
          flash.writeFloat(tgs2602_Ro, 4);
        }
        color=rgbLed.INTERNAL;
      }
      break;
    case rs.PRE_HEAT_CALIBRATING:
      calibration_count=0;
    case rs.PRE_HEAT_USER_SAMPLING:
    case rs.PRE_HEATING:
      {
        color=rgbLed.ORANGE;
        if(rs.isFirstPreHeatLoop()) { // Take ambient temperature before pre-heating
          read_dht22();
        }
      }
      break;
    case rs.CONTINUE:
      break;            
    default:  
      delay(1);
  }  

  if(digitalRead(CALIBRATE_BTN)==LOW) {
    calibrate("");
  }
  if(digitalRead(USER_SAMPLING_BTN)==LOW) {
    sample("");
  }  

  rgbLed.setLedColor(delay_ms, 100, 3000, color);
  if(!Particle.connected() && reading_sent) color=rgbLed.RED;
  delay(delay_ms);  
}

void read_dht22() {
  DHT.acquire();
  int cnt=0;
  while (DHT.acquiring())
  {
    if(cnt>100) break;
    delay(30);
    cnt++;
  }
  reading.humidity = DHT.getHumidity();
  reading.temperature = DHT.getCelsius(); 
}

int calibrate(String command) {
  rs.startCalibrating(unix_time);
  return 1;
}

int sample(String command) {
  rs.startUserSampling(unix_time);
  return 1;
}

void beep(int delay_ms) {
    analogWrite(BUZZER_PIN, 255);
    delay(delay_ms);
    analogWrite(BUZZER_PIN, 0);
}

int setWspCalib(String value) {
    char buf[20];
    value.toCharArray(buf,20);
    float f = atof(buf);
    flash.writeFloat(f, 0);
    sprintf(wsp2110_display,"%.2f",f);
    wsp2110_Ro=f;
    return value.length();
}

int setTgsCalib(String value) {
    char buf[20];
    value.toCharArray(buf,20);
    float f = atof(buf);
    flash.writeFloat(f, 4);
    sprintf(tgs2602_display,"%.2f",f); 
    tgs2602_Ro=f;
    return value.length();
}
