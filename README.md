indoor-air-quality-dashboard
==============================

LAMP based Indoor Air Quality Web Dashboard for the Spark Core

### Consists of:
1. PHP pages to add a device and view graphs and a calendar showing when air quality is poor
2. Firmware to send the sensor data to the web pages
3. Scripts and instructions to set-up the website and install on Ubuntu 14.04

### Supported Sensors
|Sensor         |Type                                  |
|---------------|--------------------------------------|
|DHT22          |Temperature/Humidity                  |
|Shinyei PPD42NS|Dust Particles                        |
|Figaro TGS2602 |VOCs and Hydrogen Sulfide (Sewer Gas) |
|WSP2110        |Formaldehyde                          |

### Directories
|Directory           |Description                                                      |
|--------------------|-----------------------------------------------------------------|
|ancillary           |Installation and setup instructions                              |
|breadboard          |Diagram to make your own IAQ Monitor on a breadboard             |
|dht22               |Spark Core firmware for dht22 temperature / humidity sensor      |
|documentation       |Documentation regarding hardware recommendations for V2 of the indoor air quality shield      |
|firmware            |Spark Core firmware supporting full suite of indoor air quality sensors |
|firmware-mini       |Spark Core firmware supporting cut down hardware with TGS2602 & DHT22 only |
|firmware-oled       |Particle Photon firmware supporting TGS2602, DHT22 & IIC I2C 128X64 OLED LCD Display Module Arduino/STM32 |
|firmware-oled-co    |Particle Photon firmware supporting TGS2600, DHT22 & IIC I2C 128X64 OLED LCD Display Module Arduino/STM32 |
|hardware            |Details about the indoor air quality shield hardware             |
|libs                |firmware libraries for the sensors                               |
|sht15               |Spark Core firmware for sht15 temperature / humidity sensor      |
|www                 |PHP Web pages to view graphs of collected data                   |

### History
<a href="http://community.spark.io/t/custom-shield-indoor-air-quality-monitor/121" title="Development evolution of the hardware"><img src="spark.jpg"/></a>
