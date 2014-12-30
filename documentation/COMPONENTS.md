## Components
#### Pair Digital Grove Ports
<b>Used for Grove Dust Sensor / custom additional sensors</b>

The hardware designer used a pair of these ports which share the 2 data lines. This allows for either one sensor requiring 2 data lines, or 2 sensors each requiring 1 data line. This is a good solution which makes the best use of available data lines.

<i>The Grove sockets used should be reviewed since they require trimming of the Grove Connectors before the Connectors will fit.</i>
#### Pair Analog Grove Ports
<b>Used for various Grove sensors / custom additional sensors</b>

I propose adding a pair of analog Grove Ports in the same data line configuration as the existing digital ports. 

If these ports are added I recommend placing them in the same location as the exisiting grove ports, and moving the digital grove ports to the other side of the board. I recommend placing these ports apart from each other since the grove sensors are supplied with short cables and it would be tricky to put 4 sensors on 1 side of the board.
#### Switches
I plan to put the IAQ shield into an enclosure, I could not find a simple way to press the switches once the shield is in an enclosure. Replacing the switches with right-angled long actuator switches would enable the buttons to stick out the top of the enclosure. 

<a href="http://www.digikey.com/product-detail/en/TL1105SF160Q/EG1839-ND/13532">Right-Angled Long Actuator Switches</a>
#### RGB LED
The RGB LED could be seen if a transparent enclosure is used, but there is a smaller selection of these enclosures off-the-shelf. I recommend replacing the RGB LED with one which supports a right-angle light pipe - this would allow the LED to poke out the top of the enclosure.

<a href="http://www.digikey.ca/Web%20Export/Supplier%20Content/Dialight_350/PDF/dialight-sg-surface-mount-leds.pdf?redirected=1">LEDs for Light-Pipes</a>
