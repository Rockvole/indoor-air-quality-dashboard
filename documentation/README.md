# documentation

Discussion of the Sensors and Components on the Indoor Air Quality Shield.

<p align="center">
  <img src="iaq-mod-cropped-text.jpg"/>
  <br/>
  Indoor Air Quality shield - also showing proposed orientation inside enclosure.
</p>

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
## Components
#### Pair Digital Grove Ports
The hardware designer used a pair of these ports which share the 2 data lines. This allows for either one sensor requiring 2 data lines, or 2 sensors each requiring 1 data line. This is a good solution which makes the best use of available data lines.

<i>The Grove sockets used should be reviewed since they require trimming of the Grove Connectors before the Connectors will fit.</i>
#### Pair Analog Grove Ports
I propose adding a pair of analog Grove Ports in the same data line configuration as the existing digital ports. 

If these ports are added I recommend placing them in the same location as the exisiting grove ports, and moving the digital grove ports to the other side of the board. I recommend placing these ports apart from each other since the grove sensors are supplied with short cables and it would be tricky to put 4 sensors on 1 side of the board.
#### Switches
I plan to put the IAQ shield into an enclosure, I could not find a simple way to press the switches once the shield is in an enclosure. Replacing the switches with right-angled long actuator switches would enable the buttons to stick out the top of the enclosure. 

<a href="http://www.digikey.com/product-detail/en/TL1105SF160Q/EG1839-ND/13532">Right-Angled Long Actuator Switches</a>
#### RGB LED
The RGB LED could be seen if a transparent enclosure is used, but there is a smaller selection of these enclosures off-the-shelf. I recommend replacing the RGB LED with one which supports a right-angle light pipe - this would allow the LED to poke out the top of the enclosure.

<a href="http://www.digikey.ca/Web%20Export/Supplier%20Content/Dialight_350/PDF/dialight-sg-surface-mount-leds.pdf?redirected=1">LEDs for Light-Pipes</a>

## Data Line Shortages
In the case where we run short on data lines for the hardware design these are the components I would recommend dropping. Start of list is first component to lose.

1. MQ Sockets - Many of the MQ sensors are not sensitive enough to notice anything in a home environment. There are a few with reasonable sensitivity - of which a couple can be purchased as grove sensors, so I place the analog Grove ports higher in the priority list.
2. MICS-4514 - this sensor is definitely useful in an indoor air quality monitor, but I believe that the other sensors are complimentary to each other and to the locations and events system in the dashboard which is used to locate the sources of problems. Carbon Monoxide (CO) is in a different category where as soon as you have a reading, finding the source is typically more straightforward. CO could be something to visit in a separate shield. This chip has 2 sensors in one package, so if only 1 data line is needed the MICS-5524 could be used instead as it does not have the NOx sensor.
3. Buzzer - for me, the buzzer is tied to the CO and an emergency situation, so if the CO sensor is gone it would be fine to lose the buzzer.
4. RGB LED - It would be fine to downgrade to 2 LEDs instead if we are short on lines.
