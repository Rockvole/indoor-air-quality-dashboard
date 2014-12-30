## Sensors
#### DHT22 (Temperature and Humidity)
It is important to get accurate and reliable humidity and temperature (HT) readings as a measure of indoor air quality. This is also useful for making deductions about the cause of indoor air quality issues in conjunction with other sensors.

I am concerned with the consistency and long term reliability of the DHT22 sensors and the quality seems to vary depending on source. There are more accurate and reliable sensors available such as the SHT15 (5 times the price of the DHT22). 

An alternative to a more expensive sensor would be to allow the DHT22 to be user replaceable so that a poor sensor can be cheaply replaced. 
I suggest a 0.1" 4-pin female header such as :
<a href="http://www.pololu.com/category/50/0.100-in-2.54-mm-female-headers">0.1" Female Headers</a>

The main issue with reading HT on the board is that the other components generate heat causing the PCB to heat up and give inaccurate readings.

To improve the HT readings I suggest :

1. Air Gap around HT sensor - this is used in the <a href="https://acrobotic.com/smart-citizen">Smart Citizen Project</a>

2. Raise HT sensor away from the PCB - this is used in V1 of the Smart Citizen Project and the <a href="http://shop.wickeddevice.com/resources/air-quality-egg/">Air Quality Egg</a>. Achieved with the 4-pin female header.

3. Switchable power to the sensors on the board - many of the sensors have heaters so the ability to programmatically turn off the heaters should reduce heat. The sensors would be turned back on a few minutes before taking readings.

<p align="center">
  <img src="DHT22-heat.png"/>
  <br/>
  Diagram showing DHT22 oriented away from the PCB
</p>

#### TGS-2602 (Sewer Gas and VOCs)
This sensor is where the idea for the indoor air quality monitor began. I am pleased with the performance of this sensor and it appears to detect the presence of VOCs from mould which is a common problem in households.
#### WSP-2110 (Formaldehyde)
I propose to add the WSP-2110 to the board. Originally I planned to use the MQ-138 sensor in the IAQ shield. Unfortunately the prototype version of the shield did not have the ground connected to the  heater so I never obtained readings from this sensor. I later discovered the WSP-2110 which is half the price and more sensitive. It correlates well with my handheld Formaldehyde detector.
#### MICS-4514 (Carbon Monoxide and Vehicle Exhaust Fumes)
The original design called for the MICS-5525 sensor, but this was discontinued. The MICS-4514 looks like a good alternative and also detects Nitrogen Dioxide.
