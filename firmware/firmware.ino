#include <queue>
#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "SimpleEeprom.h"
#include "PietteTech_DHT.h"
#include "TGS2602.h"
#include "WSP2110.h"
#include "ShinyeiPPD42NS.h"
#include "function_pulseIn.h"

#define INTERVAL_MINS 60
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5      // Number of times to sample sensor
#define SAMPLING_INTERVAL_MS 50   // Number of ms between samples

#define SENSOR_TGS2602          A0
#define SENSOR_WSP2110          A3
#define BUZZER_PIN              A4
#define RED_LED                 A5
#define GREEN_LED               A6
#define BLUE_LED                A7

#define DUST_PIN                D1
#define DHT_PIN                 D2
#define USER_SAMPLING_BTN       D3
#define CALIBRATE_BTN           D4

ReadingSync rs (INTERVAL_MINS, PRE_HEAT_SECS, Time.now());
SimpleEeprom flash;

struct reading {
    int    reading_time = 0;    
    double temperature = 0;
    double humidity = 0;
    int    tgs2602_sewer = 0; 
    int    wsp2110_hcho = 0;
    float  dust_concentration = 0;    
};

std::queue<reading> q;
reading sample;
union float2bytes {float f; char b[sizeof(float)]; };
int unix_time = 0;
int delay_ms = 0;
int heating_count=0;
int calibration_count=0;
int stage=0;
bool first_sample=true;
bool acquired_ip=true;
int uptime_start=0;

// --------------------------------------------------------------------- RGB LED
RgbLedControl rgbLed (RED_LED, GREEN_LED, BLUE_LED);
RgbLedControl::Color color;

// --------------------------------------------------------------------- DHT22
void dht_wrapper();
PietteTech_DHT DHT(DHT_PIN, DHT22, dht_wrapper);

// --------------------------------------------------------------------- Shinyei PPD42NS
#define DUST_SAMPLE_INTERVAL_MS 30000
ShinyeiPPD42NS dust(DUST_SAMPLE_INTERVAL_MS);

// --------------------------------------------------------------------- TGS2602
TGS2602 tgs2602(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float tgs2602_Ro = 10152.715;
int   tgs2602_sample_avg = 0;
char  tgs2602_display[10];

// --------------------------------------------------------------------- WSP2110
WSP2110 wsp2110(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float wsp2110_Ro = 97073.305;
int   wsp2110_sample_avg = 0;
char  wsp2110_display[10];

// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;
char hostname[] = "foodaversions.com";
char ip_display[16];

void setup()
{
  wsp2110_Ro = flash.readFloat(0);
  tgs2602_Ro = flash.readFloat(4);
  
  pinMode(CALIBRATE_BTN, INPUT_PULLUP);
  pinMode(USER_SAMPLING_BTN, INPUT_PULLUP);
  pinMode(BUZZER_PIN, OUTPUT); 
  pinMode(DUST_PIN, INPUT);
  pinMode(DHT_PIN, INPUT_PULLUP);   

  request.ip = {0,0,0,0}; // Fill in if you dont want to resolve host
  //request.ip = {192, 168, 1, 130}; // davidlub
  request.port = 80;  
  resolveHost();
  
  // Register Spark variables
  Spark.variable("ip", &ip_display, STRING);  
  Spark.variable("temperature", &sample.temperature, DOUBLE);
  Spark.variable("humidity", &sample.humidity, DOUBLE);
  Spark.variable("unix_time", &unix_time, INT);
  Spark.variable("stage", &stage, INT);
  //Spark.variable("tgs2602", &tgs2602_sample_avg, INT);
  sprintf(tgs2602_display,"%.3f",tgs2602_Ro);
  Spark.variable("tgs2602", &tgs2602_display, STRING);  
  //Spark.variable("mq131", &mq131_sample_avg, INT);
  sprintf(wsp2110_display,"%.3f",wsp2110_Ro);
  Spark.variable("wsp2110", &wsp2110_display, STRING);  
  Spark.variable("url", &url, STRING);

  //Serial.begin(9600);
}

void dht_wrapper() {
    DHT.isrCallback();
}

void loop()
{
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
        if(first_sample) {
          first_sample=false;  
          tgs2602.startSampling(current_ms);
          wsp2110.startSampling(current_ms);
          dust.startSampling(current_ms);
          sample.reading_time = unix_time;
        }
        if(!tgs2602.isSamplingComplete()) {
          tgs2602_sample_avg = tgs2602.getResistanceCalculationAverage(analogRead(SENSOR_TGS2602), current_ms);
        }       
        if(!wsp2110.isSamplingComplete()) {
          wsp2110_sample_avg = wsp2110.getResistanceCalculationAverage(analogRead(SENSOR_WSP2110), current_ms);
        }
        if(!dust.isSamplingComplete()) {
          unsigned long duration = pulseIn(DUST_PIN, LOW);
          sample.dust_concentration = dust.getConcentration(duration, current_ms);          
        }
        if(wsp2110.isSamplingComplete() && dust.isSamplingComplete() && tgs2602.isSamplingComplete()) {
          sample.tgs2602_sewer = tgs2602.getSewerGasPercentage(tgs2602_sample_avg, tgs2602_Ro);            
          sample.wsp2110_hcho = wsp2110.getFormaldehydeGasPercentage(wsp2110_sample_avg, wsp2110_Ro);
          rs.setSamplingComplete();
          q.push(sample);
        }
        delay_ms=0;
      }
      break;
    case rs.SEND_READING:
      {
        if(resolveHost()) {
          reading curr_sample;
          bool reading_sent;
          do {
            reading_sent=false;
            curr_sample = q.front();
            sprintf(url, "/iaq/get_reading.php?unix_time=%i&temp=%.2f&hum=%.2f&hcho=%i&sewer=%i&dust=%.2f&core_id=%s&uptime=%i", 
                         curr_sample.reading_time,  
                         curr_sample.temperature, curr_sample.humidity, curr_sample.wsp2110_hcho, 
                         curr_sample.tgs2602_sewer, curr_sample.dust_concentration,
                         Spark.deviceID().c_str(), (unix_time-uptime_start));  
            request.path = url;
            http.get(request, response);
            char read_time_chars[12];
            sprintf(read_time_chars, "%d", curr_sample.reading_time);
            String read_time_str(read_time_chars);
            if(read_time_str.equals(response.body)) {
              q.pop();
              reading_sent=true;
            }
          } while(reading_sent && !q.empty());
        } else { // Cant resolve host
            color=rgbLed.RED;
        }
        rs.setReadingSent();        
      }
      break;        
    case rs.CALIBRATING:
      {
        delay_ms=CALIBRATION_SAMPLE_INTERVAL;
        calibration_count++;
        if(calibration_count<=1) {
          tgs2602.startCalibrating();           
          wsp2110.startCalibrating();
        }
        tgs2602_Ro = tgs2602.calibrateInCleanAir(analogRead(SENSOR_TGS2602));
        wsp2110_Ro = wsp2110.calibrateInCleanAir(analogRead(SENSOR_WSP2110));
        if(calibration_count==CALIBRATION_SAMPLE_FREQUENCY) { // Calibration Complete
          beep(200);
          rs.setCalibratingComplete();
          sprintf(wsp2110_display,"%.3f",wsp2110_Ro);         
          flash.writeFloat(wsp2110_Ro, 0);
          sprintf(tgs2602_display,"%.3f",tgs2602_Ro);         
          flash.writeFloat(tgs2602_Ro, 4);
        }
        color=rgbLed.BLUE;
      }
      break;
    case rs.PRE_HEAT_CALIBRATING:
      calibration_count=0;
    case rs.PRE_HEAT_USER_SAMPLING:
    case rs.PRE_HEATING:
      {
        color=rgbLed.ORANGE;
        if(heating_count==0) {  // Take ambient temperature before pre-heating
          read_dht22();
        }
        heating_count++;
      }
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

  if(digitalRead(CALIBRATE_BTN)==LOW) {
    rs.startCalibrating(unix_time);
  }
  if(digitalRead(USER_SAMPLING_BTN)==LOW) {
    first_sample=true;
    heating_count=0;
    rs.startUserSampling(unix_time);
  }  

  rgbLed.setLedColor(delay_ms, 100, 3000, color);  
  delay(delay_ms);  
}

void read_dht22() {
  DHT.acquire();
  while (DHT.acquiring());
    
  sample.humidity = DHT.getHumidity();
  sample.temperature = DHT.getCelsius(); 
}

bool resolveHost() {
  if((request.ip[0]+request.ip[1]+request.ip[2]+request.ip[3])==0) {
    uint32_t ip_addr = 0;
    gethostbyname(hostname, strlen(hostname), &ip_addr);
    request.ip = {BYTE_N(ip_addr, 3),BYTE_N(ip_addr, 2),BYTE_N(ip_addr, 1),BYTE_N(ip_addr, 0)};    
    sprintf(ip_display,"%d.%d.%d.%d",request.ip[0],request.ip[1],request.ip[2],request.ip[3]);
    if((request.ip[0]+request.ip[1]+request.ip[2]+request.ip[3])==0) return false;
  }
  return true;
}

void beep(int delay_ms) {
    analogWrite(BUZZER_PIN, 255);
    delay(delay_ms);
    analogWrite(BUZZER_PIN, 0);
}

