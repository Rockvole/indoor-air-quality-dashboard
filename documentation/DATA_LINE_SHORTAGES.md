## Data Line Shortages
In the case where we run short on data lines for the hardware design these are the components I would recommend dropping. Start of list is first component to lose.

1. MQ Sockets - Many of the MQ sensors are not sensitive enough to notice anything in a home environment. There are a few with reasonable sensitivity - of which a couple can be purchased as grove sensors, so I place the analog Grove ports higher in the priority list.
2. MICS-4514 - this sensor is definitely useful in an indoor air quality monitor, but I believe that the other sensors are complimentary to each other and to the locations and events system in the dashboard which is used to locate the sources of problems. Carbon Monoxide (CO) is in a different category where as soon as you have a reading, finding the source is typically more straightforward. CO could be something to visit in a separate shield. This chip has 2 sensors in one package, so if only 1 data line is needed the MICS-5524 could be used instead as it does not have the NOx sensor.
3. Buzzer - for me, the buzzer is tied to the CO and an emergency situation, so if the CO sensor is gone it would be fine to lose the buzzer.
4. RGB LED - It would be fine to downgrade to 2 LEDs instead if we are short on lines.
<p align="center">
  <img src="led_pair.png"/>
  <br/>
  Right-Angle LED Pair
</p>
5. WSP-2110 - I would prefer to have the Formaldehyde sensor onboard, but the analog Grove ports allow for the Grove HCHO Sensor which contains the WSP-2110. The cost to regular purchasers is practically the same for the standalone sensor or the Grove HCHO sensor.
