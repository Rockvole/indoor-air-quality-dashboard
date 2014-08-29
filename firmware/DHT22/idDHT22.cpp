/********************************************************************/
/*
FILE: idDHT22.cpp
VERSION: 0.0.1
PURPOSE: Interrupt driven Lib for DHT11 with Arduino.
LICENCE: GPL v3 (http://www.gnu.org/licenses/gpl.html)
DATASHEET: http://www.micro4you.com/files/sensor/DHT11.pdf
Based on DHT11 library: http://playground.arduino.cc/Main/DHT11Lib
*/
/*
	Modified by Paul Kourany for DHT22, Mar 28, 2014
	Originally ported to Spark.Core
	January 18, 2014
	Scott Piette

	Original version from niesteszek located @ https://github.com/niesteszeck/idDHT22
*/

#include "idDHT22.h"


// Fix to define word type conversion function
uint16_t word(uint8_t high, uint8_t low) {
    uint16_t ret_val = low;
    ret_val += (high << 8);
    return ret_val;
}


idDHT22::idDHT22(int sigPin, void (*callback_wrapper)()) {
	init(sigPin, callback_wrapper);
}

void idDHT22::init(int sigPin, void (*callback_wrapper) ()) {
	this->_sigPin = sigPin;
	this->isrCallback_wrapper = callback_wrapper;
	_hum = 0;
	_temp = 0;
	pinMode(sigPin, OUTPUT);
	digitalWrite(sigPin, HIGH);
	_state = STOPPED;
	_status = IDDHTLIB_ERROR_NOTSTARTED;
}

int idDHT22::acquire() {
	if (_state == STOPPED || _state == ACQUIRED) {
		// Setup the initial state machine
		_state = RESPONSE;

		//Empty buffer and variables
		for (int i=0; i< 6; i++)
			_bits[i] = 0;
		_cnt = 7;
		_idx = 0;
		_hum = 0;
		_temp = 0;

		/*
		 * Toggle the digital output to trigger the DHT device
		 * to send us temperature and humidity data
		 */
		pinMode(_sigPin, OUTPUT);
		digitalWrite(_sigPin, LOW);
		delay(18);						//Spec: min 1ms
		digitalWrite(_sigPin, HIGH);
		delayMicroseconds(40);			//Spec: 20-40us
		pinMode(_sigPin, INPUT);

		/*
		 * Attach the interrupt handler to receive the data once the DHT
		 * starts to send us its data
		 */
		_us = micros();
		attachInterrupt(_sigPin, isrCallback_wrapper, FALLING);

		return IDDHTLIB_ACQUIRING;
	} else
		return IDDHTLIB_ERROR_ACQUIRING;
}

int idDHT22::acquireAndWait() {
	acquire();
	while(acquiring())
		;
	return getStatus();
}

void idDHT22::isrCallback() {
	unsigned long newUs = micros();
	unsigned long delta = (newUs -_us);
	_us = newUs;
	if (delta>6000) {
		_status = IDDHTLIB_ERROR_ISR_TIMEOUT;
		_state = STOPPED;
		detachInterrupt(_sigPin);
		return;
	}
	switch(_state) {
		case RESPONSE:
			if(delta < 25){
				_us -= delta;
				break; //do nothing, it started the response signal
			} if(125 < delta && delta < 190) {
				_state = DATA;
			} else {
				detachInterrupt(_sigPin);
				_status = IDDHTLIB_ERROR_RESPONSE_TIMEOUT;
				_state = STOPPED;
			}
			break;
		case DATA:
			if(delta<10) {
				detachInterrupt(_sigPin);
				_status = IDDHTLIB_ERROR_DELTA;
				_state = STOPPED;
			} else if(60 < delta && delta < 145) { //valid in timing
				if(delta > 100) //is a one
					_bits[_idx] |= (1 << _cnt);
				if (_cnt == 0) { // we have fullfilled the byte, go to next
						_cnt = 7; // restart at MSB
						if(_idx++ == 4) { // go to next byte, if we have got 5 bytes stop.
							detachInterrupt(_sigPin);
							// WRITE TO RIGHT VARS
							_hum = word(_bits[0], _bits[1]) * 0.1;
							_temp = (_bits[2] & 0x80 ? 
								-word(_bits[2] & 0x7F, _bits[3]) :
								word(_bits[2], _bits[3]))
								* 0.1;
								uint8_t sum = _bits[0] + _bits[1] + _bits[2] + _bits[3];
							if (_bits[4] != sum) {
								_status = IDDHTLIB_ERROR_CHECKSUM;
								_state = STOPPED;
							} else {
								_status = IDDHTLIB_OK;
								_state = ACQUIRED;
							}
							break;
						}
				}
				else _cnt--;
			}
			else {
				detachInterrupt(_sigPin);
				_status = IDDHTLIB_ERROR_DATA_TIMEOUT;
				_state = STOPPED;
			}
			break;
		default:
			break;
	}
}

bool idDHT22::acquiring() {
	if (_state != ACQUIRED && _state != STOPPED)
		return true;
	return false;
}

int idDHT22::getStatus() {
	return _status;
}

float idDHT22::getCelsius() {
	idDHT22_CHECK_STATE;
	return _temp;
}

float idDHT22::getHumidity() {
	idDHT22_CHECK_STATE;
	return _hum;
}

float idDHT22::getFahrenheit() {
	idDHT22_CHECK_STATE;
	return _temp * 1.8 + 32;
}

float idDHT22::getKelvin() {
	idDHT22_CHECK_STATE;
	return _temp + 273.15;
}

// delta max = 0.6544 wrt dewPoint()
// 5x faster than dewPoint()
// reference: http://en.wikipedia.org/wiki/Dew_point
double idDHT22::getDewPoint() {
	idDHT22_CHECK_STATE;
	double a = 17.271;
	double b = 237.7;
	double temp_ = (a * (double) _temp) / (b + (double) _temp) + log( (double) _hum/100);
	double Td = (b * temp_) / (a - temp_);
	return Td;
}

// dewPoint function NOAA
// reference: http://wahiduddin.net/calc/density_algorithms.htm
double idDHT22::getDewPointSlow() {
	idDHT22_CHECK_STATE;
	double a0 = (double) 373.15 / (273.15 + (double) _temp);
	double SUM = (double) -7.90298 * (a0-1.0);
	SUM += 5.02808 * log10(a0);
	SUM += -1.3816e-7 * (pow(10, (11.344*(1-1/a0)))-1) ;
	SUM += 8.1328e-3 * (pow(10,(-3.49149*(a0-1)))-1) ;
	SUM += log10(1013.246);
	double VP = pow(10, SUM-3) * (double) _hum;
	double T = log(VP/0.61078); // temp var
	return (241.88 * T) / (17.558-T);
}
