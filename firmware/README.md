Firmware Features
=================

1. Sampling Synchronization<br />
Takes a reading every n minutes which is synchronized from the start of the day. (e.g. 60m interval will be taken at 1:00, 2:00 etc).

2. Http send of readings<br/>
Occurs at random time in the future before the next read in order to reduce load on server with all the synchronized readings.

3. Readings are stored in a queue.<br/>
If there is a failure during send the reading will be saved until the next send attempt.

4. Temperature / Humidity Reading from DHT22

5. Sewer Gas Reading from Figaro TGS2602

6. Dust sensor reading from Grove dust sensor

7. User Reading button - Takes a reading when user presses BTN1

8. Calibration button - Calibrates sensors when user presses BTN2<br/>
Waits for pre-heat time, then calibrates sensors and stores calibration results in eeprom. Orange LED flash means pre-heating. Blue LED flash means calibrating. Buzzes when calibration finished. This is so the user can take the shield to a field for clean air calibration.
