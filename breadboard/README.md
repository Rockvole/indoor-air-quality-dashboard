# breadboard

Breadboard schematics to make an indoor air quality monitor with the most important sensors.

<p align="center">
  <img src="breadboard-iaq.png"/>
  <br/>
  Indoor Air Quality Monitor Breadboard.
</p>

# protoboard

<p align="center">
  <img src="proto-iaq-side.jpg"/>
  <br/>
  Indoor Air Quality Monitor Protoboard Side View
</p>

<p align="center">
  <img src="proto-iaq-top.jpg"/>
  <br/>
  Indoor Air Quality Monitor Protoboard Top View
</p>

### Parts List
|Part                                        |Source  |Price   |Qty  |Note       |
|--------------------------------------------|--------|--------|-----|-----------|
|Spark Core                                  |AF      |$40     |     |           |
|*(alternatively new Spark Photon = $20)*    |        | -      |     |           |
|Adafruit Perma-Proto Full Size breadboard   |AF      |$6.50   |     |           |
|Micro USB B Female Breakout Board	         |EB      |$3      |     |           |
|Figaro SR6 Socket                           |FI      |$6.50   |2    |(optional) |
|*(alternatively Figaro SR7 Socket = $1.50)* |        | -      |     |           |
|4-pin 2.54mm Pitch Female Single Row Header |EB      | -      |     |(optional) |
|12-pin 2.54mm Pitch Female Single Row Header|EB      | -      |2    |           |
|Grove Universal 4-pin connector             |SS      | -      |     |(optional) |
|RGB LED                                     |        | -      |     |           |
|1K resistor                                 |        | -      |4    |           |
|10K resistor                                |        | -      |     |           |
|4.7K resistor                               |        | -      |     |           |
|Assorted 22 AWG solid jumper wire           |        | -      |     |           |
|Genuine Apple USB Wall Charger              |AP      |$20     |     |           |

<i>Optional items - solder components directly instead. If you wish to use a solderless breadboard then the optional items are not needed</i>

### Sensors
|Name               |Type                   |Source  |Price   |
|-------------------|-----------------------|--------|--------|
|Figaro TGS2602     |VOCs / Sewer Gas       |AE      |$6.50   |
|WSP2110            |Formaldehyde           |AE      |$15     |
|Grove Dust Sensor  |Dust                   |SS      |$16     |
|DHT22              |Temperature / Humidity |AE      |$3.50   |

``AF = Adafruit    EB = Ebay    FI = Figaro    AE = AliExpress    SS = Seeedstudio    AP = Apple``

### Files
|File                        |Description                                                              |
|----------------------------|-------------------------------------------------------------------------|
|indoor_air_quality.fzz      |Fritzing file for the indoor air quality monitor breadboard              |
|WSP-2110.fzpz               |Fritzing file for the WSP-2110 HCHO sensor (same as Grove HCHO sensor)   |

#### Tips
* A good quality power supply is essential. If this is not used the dust sensor will give meaningless results, the temperature / humidity sensor will have poor accuracy and the results from the WSP2110 and TGS2602 will be full of spikes.
* Use the same power supply the entire time. If you need to change power supply, re-calibration will be necessary.
