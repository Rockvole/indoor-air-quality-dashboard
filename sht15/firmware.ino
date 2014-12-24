#include <queue>
#include "ReadingSync.h"
#include "HttpClient.h"
#include "SHT1x.h" 
/**
 * ReadSHT1xValues
 *
 * Read temperature and humidity values from an SHT1x-series (SHT10,
 * SHT11, SHT15) sensor.
 *
 * Copyright 2009 Jonathan Oxer <jon@oxer.com.au>
 * www.practicalarduino.com
 *
 * Ported to Spark Core by Anurag Chugh (https://github.com/lithiumhead) on 2014-10-15
 */
// -----------------
// Read temperature & humidity and send to server
// -----------------
#define INTERVAL_MINS 10
#define DATA_PIN                 D0
#define CLOCK_PIN                D1

ReadingSync rs (INTERVAL_MINS, 0, Time.now());

struct reading {
    int    reading_time = 0;    
    double temperature = 0;
    double humidity = 0;
};

std::queue<reading> q;
reading sample;
int unix_time = 0;
int stage=0;
int uptime_start=0;

// --------------------------------------------------------------------- SHT15
SHT1x sht1x(DATA_PIN, CLOCK_PIN);

// --------------------------------------------------------------------- HTTP
HttpClient http;
char url[200];
http_request_t request;
http_response_t response;
char hostname[] = "foodaversions.com";
char ip_display[16];

void setup()
{
  request.ip = {0,0,0,0}; // Fill in if you dont want to resolve host
  //request.ip = {192, 168, 1, 130}; // davidlub
  request.port = 80;  
  resolveHost();

  // Register Spark variables
  Spark.variable("ip", &ip_display, STRING);   
  Spark.variable("temperature", &sample.temperature, DOUBLE);
  Spark.variable("humidity", &sample.humidity, DOUBLE);
  Spark.variable("url", &url, STRING); 
  Spark.variable("stage", &stage, INT);
    
  //Serial.begin(9600); // Open serial connection to report values to host    
}

void loop()
{
  unix_time=Time.now();
  if(uptime_start<1000000000) uptime_start = unix_time;  
  stage=rs.getStage(unix_time);  

  switch(stage) {
    case rs.SAMPLING:
      {
        sample.reading_time = unix_time;   
        read_ht();
        rs.setSamplingComplete();
        q.push(sample);
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
            sprintf(url, "/dht22/get_reading.php?unix_time=%i&temp=%.2f&hum=%.2f&core_id=%s&uptime=%i", 
                         curr_sample.reading_time,  
                         curr_sample.temperature, curr_sample.humidity, 
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
        }
        rs.setReadingSent();      
      }
      break;
    default:
      break;
  }
  delay(1000);
}

void read_ht() {
  sample.humidity = sht1x.readHumidity();
  sample.temperature = sht1x.readTemperatureC();
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

