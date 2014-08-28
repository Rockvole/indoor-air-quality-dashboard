#include "ReadingSync.h"
#include "HttpClient.h"
// -----------------
// Read temperature & humidity and send to server
// -----------------
#define INTERVAL_MINS 4

ReadingSync rs (INTERVAL_MINS, Time.now());
HttpClient http;
int unix_time = 0;
int reading_time = 0;
int voltage = 0;
int temperature = 0;
int humidity = 0;
char url[200];

http_request_t request;
http_response_t response;

void setup()
{
  // Register a Spark variable here
  Spark.variable("temperature", &temperature, INT);
  Spark.variable("humidity", &humidity, INT);
  Spark.variable("url", &url, STRING);

  // Connect the temperature sensor to A7 and configure it
  // to be an input
  pinMode(A7, INPUT);
  request.hostname = "foodaversions.com";
  //request.ip = {192,168,1,130};
  request.port = 80;
}

void loop()
{
  unix_time=Time.now();

  if(rs.isTimeToTakeReading(unix_time)) {
	reading_time = unix_time;
	voltage = (analogRead(A7) * 3.3)/4095;
    temperature = (voltage - 0.5) * 100;
    humidity = 84;
  } else if (rs.isTimeToSendReading(unix_time)) {
	sprintf(url, "/dht22/get_reading.php?core_id=%s&temp=%i&hum=%i&unix_time=%i", Spark.deviceID().c_str(), temperature, humidity, reading_time);  
    request.path = url;
    http.get(request, response);
    rs.setReadingSent();
  }
  delay(1000);
}
