#include "ReadingSync.h"
#include "HttpClient.h"
#include "idDHT22.h"
// -----------------
// Read temperature & humidity and send to server
// -----------------
#define INTERVAL_MINS 60

ReadingSync rs (INTERVAL_MINS, Time.now());
HttpClient http;
void dht22_wrapper();
// DHT instantiate
idDHT22 DHT22(D2, dht22_wrapper);

int unix_time = 0;
int reading_time = 0;
double temperature = 0;
double humidity = 0;
char url[200];

http_request_t request;
http_response_t response;

void setup()
{
  // Register a Spark variable here
  Spark.variable("temperature", &temperature, DOUBLE);
  Spark.variable("humidity", &humidity, DOUBLE);
  Spark.variable("url", &url, STRING);

  // Connect the temperature sensor to A7 and configure it
  // to be an input
  pinMode(A7, INPUT);
  request.hostname = "foodaversions.com";
  //request.ip = {192,168,1,130};
  request.port = 80;
}

void dht22_wrapper() {
	DHT22.isrCallback();
}

void loop()
{
  unix_time=Time.now();

  if(rs.isTimeToTakeReading(unix_time)) {
	reading_time = unix_time;
	DHT22.acquire();
	while (DHT22.acquiring());	
	
	humidity = DHT22.getHumidity();
    temperature = DHT22.getCelsius();
  } else if (rs.isTimeToSendReading(unix_time)) {
	sprintf(url, "/dht22/get_reading.php?core_id=%s&temp=%2f&hum=%2f&unix_time=%i", Spark.deviceID().c_str(), temperature, humidity, reading_time);  
    request.path = url;
    http.get(request, response);
    rs.setReadingSent();
  }
  delay(1000);
}
