#include <queue>
#include "IntervalTiming.h"
#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "SimpleEeprom.h"
#include "PietteTech_DHT.h"
#include "TGS2602.h"
#include "Adafruit_SSD1306.h"
#include "images.h"

#define INTERVAL_MINS 10
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5      // Number of times to sample sensor
#define SAMPLING_INTERVAL_MS 50   // Number of ms between samples

#define SENSOR_TGS2602          A0
#define RED_LED                 A4
#define GREEN_LED               A5
#define BLUE_LED                A7

#define DHT_PIN                 D2 // D0 // use NOT_CONNECTED if needed 

ReadingSync rs (INTERVAL_MINS, PRE_HEAT_SECS, Time.now());
SimpleEeprom flash;

struct reading_struct {
    int    reading_time = 0;    
    double temperature = 0;
    double humidity = 0;
    int    tgs2602_sewer = 0; 
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
int temp_int;
bool first_sampling_loop=true;
int display_sewer = 0;

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

// --------------------------------------------------------------------- OLED
#define OLED_RESET D4
Adafruit_SSD1306 oled(OLED_RESET);
IntervalTiming oledInterval(2000);
bool first_display_loop = true;

// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;
char hostname[] = "foodaversions.com";
char ip_display[16];

void setup()
{
  temp_float = flash.readFloat(4);
  if(temp_float==temp_float) // Valid Number
    tgs2602_Ro = temp_float;

  request.ip = {0,0,0,0}; // Fill in if you dont want to resolve host
  //request.ip = {192, 168, 1, 110}; // david-mint
  request.port = 80;  
  resolveHost();
  oled.begin(SSD1306_SWITCHCAPVCC, 0x3C);
  oled.display(); 
  
  // Register Particle variables
  Particle.variable("ip", ip_display, STRING);  
  Particle.variable("temperature", &reading.temperature, DOUBLE);
  Particle.variable("humidity", &reading.humidity, DOUBLE);
  Particle.variable("unix_time", &unix_time, INT);
  Particle.variable("stage", &stage, INT);
  Particle.variable("url", url, STRING);  
  
  sprintf(tgs2602_display,"%.2f",tgs2602_Ro);
  Particle.variable("tgs2602", tgs2602_display, STRING);    
  
  // Register Spark Functions
  Particle.function("calibrate", calibrate);
  Particle.function("sample", sample);
  Particle.function("set2602Calib", set2602Calib);
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
  unsigned long current_ms = millis();
  
  // ------------------------------------------------------------------- TGS2602
  if(first_sampling_loop) {
    first_sampling_loop = false;
    tgs2602.startSampling(current_ms);
  }
  if(!tgs2602.isSamplingComplete() && tgs2602.isTimeToRead(current_ms)) {
    tgs2602.setAnalogRead(analogRead(SENSOR_TGS2602), current_ms);
  }       
  if(tgs2602.isSamplingComplete()) {
    first_sampling_loop = true;
    display_sewer = tgs2602.getSewerGasPercentage(tgs2602_Ro);
  }
  // ------------------------------------------------------------------- OLED DISPLAY
  if(first_display_loop) {
    first_display_loop = false;
    oledInterval.startIntervalTime(current_ms);
    oled.clearDisplay();
    oled.setTextColor(WHITE);
    // ------------------------ TEMPERATURE
    oled.setTextSize(2);
    oled.setCursor(0,0);
    temp_int = reading.temperature;
    oled.print(temp_int);
    oled.print("C");
    // ------------------------ HUMIDITY
    oled.setTextSize(2);
    oled.setCursor(90,0);
    temp_int = reading.humidity;    
    oled.print(temp_int);
    oled.print("%");
    // ------------------------ SEWER    
    oled.setTextSize(3);
    oled.setCursor(0,30);
    oled.print(display_sewer);
    // ------------------------ EMOTICON
    if(display_sewer>=400)
        oled.drawBitmap(100, 30, face_frown, 24, 24, WHITE);
    else if(display_sewer>=200)
        oled.drawBitmap(100, 30, face_ok, 24, 24, WHITE);
    else 
        oled.drawBitmap(100, 30, face_smile, 24, 24, WHITE);
    // ------------------------ DISPLAY
    oled.display();
  }
  if(oledInterval.isIntervalTimeComplete(current_ms)) {
    first_display_loop = true;
  }
  // ------------------------------------------------------------------- STAGES
  delay_ms=SAMPLING_INTERVAL_MS;
  switch(stage) {
    case rs.USER_SAMPLING:
    case rs.SAMPLING:
      {
        if(rs.isFirstSamplingLoop()) {
          reading.reading_time = unix_time;
        }
        if(tgs2602.isSamplingComplete()) {
          reading.tgs2602_sewer = tgs2602.getSewerGasPercentage(tgs2602_Ro);            
          rs.setSamplingComplete();
          q.push(reading);
        }
      }
      break;
    case rs.SEND_READING:
      {
        if(resolveHost()) {
          reading_struct curr_reading;
          bool reading_sent;
          do {
            reading_sent=false;
            curr_reading = q.front();
            sprintf(url, "/iaq/get_reading.php?unix_time=%i&temp=%.2f&hum=%.2f&hcho=0&sewer=%i&dust=0&core_id=%s&uptime=%i", 
                         curr_reading.reading_time,
                         curr_reading.temperature, curr_reading.humidity,  
                         curr_reading.tgs2602_sewer, 
                         Spark.deviceID().c_str(), (unix_time-uptime_start));  
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
        }
        tgs2602_Ro = tgs2602.calibrateInCleanAir(analogRead(SENSOR_TGS2602));
        if(calibration_count==CALIBRATION_SAMPLE_FREQUENCY) { // Calibration Complete
          rs.setCalibratingComplete();
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
  rgbLed.setLedColor(delay_ms, 100, 3000, color);  
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

bool resolveHost() {
  if((request.ip[0]+request.ip[1]+request.ip[2]+request.ip[3])==0) {
#if PLATFORM_ID == 0 // CORE
    uint32_t ip_addr = 0; 
    gethostbyname(hostname, strlen(hostname), &ip_addr);
    request.ip = {BYTE_N(ip_addr, 3),BYTE_N(ip_addr, 2),BYTE_N(ip_addr, 1),BYTE_N(ip_addr, 0)};
#elif PLATFORM_ID == 6 // PHOTON   
    request.ip = WiFi.resolve(hostname);   
#endif    
    sprintf(ip_display,"%d.%d.%d.%d",request.ip[0],request.ip[1],request.ip[2],request.ip[3]);
    if((request.ip[0]+request.ip[1]+request.ip[2]+request.ip[3])==0) return false;
  }
  return true;
}

int calibrate(String command) {
  rs.startCalibrating(unix_time);
  return 1;
}

int sample(String command) {
  rs.startUserSampling(unix_time);
  return 1;
}

int set2602Calib(String value) {
    char buf[20];
    value.toCharArray(buf,20);
    float f = atof(buf);
    flash.writeFloat(f, 4);
    sprintf(tgs2602_display,"%.2f",f); 
    tgs2602_Ro=f;
    return value.length();
}
