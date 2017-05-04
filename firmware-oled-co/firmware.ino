#include <queue>
#include "IntervalTiming.h"
#include "ReadingSync.h"
#include "RgbLedControl.h"
#include "HttpClient.h"
#include "SimpleEeprom.h"
#include "PietteTech_DHT.h"
#include "TGS2600.h"
#include "Adafruit_SSD1306.h"
#include "smileys.h"

SYSTEM_MODE(SEMI_AUTOMATIC);
SYSTEM_THREAD(ENABLED);

#define INTERVAL_MINS 10
#define PRE_HEAT_SECS 100
#define CALIBRATION_SAMPLE_FREQUENCY 50
#define CALIBRATION_SAMPLE_INTERVAL 500
#define SAMPLING_FREQUENCY 5      // Number of times to sample sensor
#define SAMPLING_INTERVAL_MS 50   // Number of ms between samples

#define SENSOR_TGS2600          A0
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
    int    tgs2600_co = 0; 
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
int temp_int;
bool first_sampling_loop=true;
int display_co = 0;

// --------------------------------------------------------------------- RGB LED
RgbLedControl rgbLed (RED_LED, GREEN_LED, BLUE_LED);
RgbLedControl::Color color;

// --------------------------------------------------------------------- DHT22
void dht_wrapper();
PietteTech_DHT DHT(DHT_PIN, DHT22, dht_wrapper);

// --------------------------------------------------------------------- TGS2600
TGS2600 tgs2600(SAMPLING_FREQUENCY, SAMPLING_INTERVAL_MS);
float tgs2600_Ro = 60000.0;
char  tgs2600_display[20];


// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;
char hostname[] = "www.foodaversions.com";
char ip_display[16];

// --------------------------------------------------------------------- OLED
#define OLED_RESET D4
Adafruit_SSD1306 oled(OLED_RESET);
IntervalTiming oledInterval(2000);
bool first_display_loop = true;

void setup()
{
  temp_float = flash.readFloat(4);
  if(temp_float==temp_float) // Valid Number
    tgs2600_Ro = temp_float;

  //request.ip = {192, 168, 1, 110}; // david-mint
  request.hostname = "www.foodaversions.com";
  request.port = 80;
  oled.begin(SSD1306_SWITCHCAPVCC, 0x3C);
  oled.display(); 

  // Register Particle variables
  Particle.variable("ip", ip_display, STRING);  
  Particle.variable("temperature", &reading.temperature, DOUBLE);
  Particle.variable("humidity", &reading.humidity, DOUBLE);
  Particle.variable("url", url, STRING);
  Particle.variable("stage", &stage, INT);
  Particle.variable("unix_time", &unix_time, INT);
  
  sprintf(tgs2600_display,"%.2f",tgs2600_Ro);
  Particle.variable("tgs2600", tgs2600_display, STRING);
  
  // Register Particle Functions
  Particle.function("calibrate", calibrate);
  Particle.function("sample", sample);
  Particle.function("set2600Calib", set2600Calib);

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
  unsigned long current_ms = millis();
  
  // ------------------------------------------------------------------- TGS2600
  if(first_sampling_loop) {
    first_sampling_loop = false;
    tgs2600.startSampling(current_ms);
  }
  if(!tgs2600.isSamplingComplete() && tgs2600.isTimeToRead(current_ms)) {
    tgs2600.setAnalogRead(analogRead(SENSOR_TGS2600), current_ms);
  }       
  if(tgs2600.isSamplingComplete()) {
    first_sampling_loop = true;
    display_co = tgs2600.getHydrogenGasPercentage(tgs2600_Ro);
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
    oled.print(display_co);
    // ------------------------ EMOTICON
    if(display_co>=400)
        oled.drawBitmap(100, 30, face_frown, 24, 24, WHITE);
    else if(display_co>=200)
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
        if(tgs2600.isSamplingComplete()) {
          reading.tgs2600_co = tgs2600.getHydrogenGasPercentage(tgs2600_Ro);            
          rs.setSamplingComplete();
          q.push(reading);
        }
      }
      break;
    case rs.SEND_READING:
      {
        if(Particle.connected()) {
          reading_struct curr_reading;
          do {
            reading_sent=false;
            curr_reading = q.front();
            sprintf(url, "/iaq/get_reading.php?unix_time=%i&temp=%.2f&hum=%.2f&hcho=0&sewer=0&co=%i&dust=0&core_id=%s&uptime=%i", 
                       curr_reading.reading_time,
                       curr_reading.temperature, curr_reading.humidity,  
                       curr_reading.tgs2600_co, 
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
          tgs2600.startCalibrating();
        }
        tgs2600_Ro = tgs2600.calibrateInCleanAir(analogRead(SENSOR_TGS2600));
        if(calibration_count==CALIBRATION_SAMPLE_FREQUENCY) { // Calibration Complete
          rs.setCalibratingComplete();
          sprintf(tgs2600_display,"%.2f",tgs2600_Ro);         
          flash.writeFloat(tgs2600_Ro, 4);
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

int set2600Calib(String value) {
    char buf[20];
    value.toCharArray(buf,20);
    float f = atof(buf);
    flash.writeFloat(f, 4);
    sprintf(tgs2600_display,"%.2f",f); 
    tgs2600_Ro=f;
    return value.length();
}
