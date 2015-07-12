//
//  HttpClient.cpp
//  water-firmware
//
//  Created by Mojtaba Cazi on 6/5/15.
//  Copyright (c) 2015 Cazisoft. All rights reserved.
//

#include "HttpClient.h"
#include "application.h"

//#define LOGGING
static const uint16_t TIMEOUT = 5000; // Allow maximum 5s between data packets.

/**
* Constructor.
*/
HttpClient::HttpClient()
{

}

/**
* Method to send a header, should only be called from within the class.
*/
void HttpClient::sendHeader(const char* aHeaderName, const char* aHeaderValue)
{
    client.print(aHeaderName);
    client.print(": ");
    client.println(aHeaderValue);

#ifdef LOGGING
    Serial.print(aHeaderName);
    Serial.print(": ");
    Serial.println(aHeaderValue);
#endif
}

void HttpClient::sendHeader(const char* aHeaderName, const int aHeaderValue)
{
    client.print(aHeaderName);
    client.print(": ");
    client.println(aHeaderValue);

#ifdef LOGGING
    Serial.print(aHeaderName);
    Serial.print(": ");
    Serial.println(aHeaderValue);
#endif
}

void HttpClient::sendHeader(const char* aHeaderName)
{
    client.println(aHeaderName);

#ifdef LOGGING
    Serial.println(aHeaderName);
#endif
}

/**
* Method to send an HTTP Request. Allocate variables in your application code
* in the aResponse struct and set the headers and the options in the aRequest
* struct.
*/
void HttpClient::request(http_request_t &aRequest, http_response_t &aResponse, http_header_t headers[], const char* aHttpMethod)
{
	
    // If a proper response code isn't received it will be set to -1.
    aResponse.status = -1;

    // NOTE: The default port tertiary statement is unpredictable if the request structure is not initialised
    // http_request_t request = {0} or memset(&request, 0, sizeof(http_request_t)) should be used
    // to ensure all fields are zero
    bool connected = false;
    if(aRequest.hostname!=NULL) {
        connected = client.connect(aRequest.hostname.c_str(), (aRequest.port) ? aRequest.port : 80 );
    }   else {
        connected = client.connect(aRequest.ip, aRequest.port);
    }
	
#ifdef LOGGING
    if (connected) {
        if(aRequest.hostname!=NULL) {
            Serial.print("HttpClient>\tConnecting to: ");
            Serial.print(aRequest.hostname);
        } else {
            Serial.print("HttpClient>\tConnecting to IP: ");
            Serial.print(aRequest.ip);
        }
        Serial.print(":");
        Serial.println(aRequest.port);
    } else {
        Serial.println("HttpClient>\tConnection failed.");
    }
#endif

    if (!connected) {
        client.stop();
        // If TCP Client can't connect to host, exit here.
        return;
    }

    //
    // Send HTTP Headers
    //

    // Send initial headers (only HTTP 1.0 is supported for now).
    client.print(aHttpMethod);
    client.print(" ");
    client.print(aRequest.path);
    client.print(" HTTP/1.0\r\n");

#ifdef LOGGING
    Serial.println("HttpClient>\tStart of HTTP Request.");
    Serial.print(aHttpMethod);
    Serial.print(" ");
    Serial.print(aRequest.path);
    Serial.print(" HTTP/1.0\r\n");
#endif

    // Send General and Request Headers.
    sendHeader("Connection", "close"); // Not supporting keep-alive for now.
    if(aRequest.hostname != NULL) {
        sendHeader("HOST", aRequest.hostname.c_str());
    }

    //Send Entity Headers
    // TODO: Check the standard, currently sending Content-Length : 0 for empty
    // POST requests, and no content-length for other types.
    if (aRequest.body != NULL) {
        sendHeader("Content-Length", (aRequest.body).length());
    } else if (strcmp(aHttpMethod, HTTP_METHOD_POST) == 0) { //Check to see if its a Post method.
        sendHeader("Content-Length", 0);
    }

    if (headers != NULL)
    {
        int i = 0;
        while (headers[i].header != NULL)
        {
            if (headers[i].value != NULL) {
                sendHeader(headers[i].header, headers[i].value);
            } else {
                sendHeader(headers[i].header);
            }
            i++;
        }
    }

    // Empty line to finish headers
    client.println();
    client.flush();

    //
    // Send HTTP Request Body
    //

    if (aRequest.body != NULL) {
        client.println(aRequest.body);

#ifdef LOGGING
        Serial.println(aRequest.body);
#endif
    }

#ifdef LOGGING
    Serial.println("HttpClient>\tEnd of HTTP Request.");
#endif

    // clear response buffer
	
	memset(&buffer[0], 0, sizeof(buffer));

    unsigned int bufferPosition = 0;
    unsigned long lastRead = millis();
    bool error = false;
    bool timeout = false;
	
	int contentLen = 0;
	char *pos1 = 0;
	char *pos2 = 0;
	bool done = false;
	typedef enum {one, two, three, four} state_t;
	state_t state = one;
	bool eofbuffer = false;

    do {
        while (client.available() && !done) {
			int readSize = client.read((uint8_t *)buffer + bufferPosition, kHttpBufferSize - bufferPosition);
			
			if (readSize == -1) {
				error = true;
				Serial.println("HttpClient>\tError: No data available.");
				break;
			}
			bufferPosition += readSize;
			
			if (bufferPosition >= sizeof(buffer)) {
				buffer[bufferPosition - 1] = '\0'; // Null-terminate buffer
				client.stop();
				error = true;
				Serial.println("HttpClient>\tError: Response body larger than buffer.");
			}
			
#ifdef LOGGING
			Serial.print("HttpClient>\t: readSize=");
            Serial.println(readSize);
#endif
            lastRead = millis();
			
			eofbuffer = false;
			while (!done && !eofbuffer) {
				switch (state) {
					case one:
						pos1 = strstr(buffer, "Content-Length: ");
						if (pos1) {
							pos1 += 16;//sizeof("Content-Length: ") + 1;
							state = two;
#ifdef LOGGING
							Serial.println("--> Two");
#endif
						} else {
							eofbuffer = true;
						}
						break;
					case two:
						pos2 = strstr(pos1, "\n");
						if (pos2) {
							contentLen = atoi(pos1);
							pos1 = pos2+1;
							state = three;
#ifdef LOGGING
							Serial.print("--> Three; Content Length = ");
							Serial.println(contentLen);
#endif
						} else {
							eofbuffer = true;
						}
						break;
					case three:
						pos2 = strstr(pos1, "\r\n\r\n");
						if (pos2) {
							state = four;
#ifdef LOGGING
							Serial.println("--> four");
#endif
						} else {
							eofbuffer = true;
						}
						break;
					case four:
						if (bufferPosition - ((uint32_t)pos2+4)  >= (unsigned int)contentLen) {
							done = true;
							break;
						} else {
							eofbuffer = true;
						}
				}
			}
        } /*while (client.available() && !done)*/
        buffer[bufferPosition] = '\0'; // Null-terminate buffer
		
        timeout = millis() - lastRead > TIMEOUT;
    } while (client.connected() && !timeout && !error && !done);
	client.stop();
	
    if (timeout) {
        Serial.println("\r\nHttpClient>\tError: Timeout while reading response.");
    }
	
    String raw_response(buffer);

    // Not super elegant way of finding the status code, but it works.
    String statusCode = raw_response.substring(9,12);

    Serial.print("HttpClient>\tStatus Code: ");
    Serial.println(statusCode);

    int bodyPos = raw_response.indexOf("\r\n\r\n");
    if (bodyPos == -1) {
        Serial.println("HttpClient>\tError: Can't find HTTP response body.");
        return;
    }
    // Return the entire message body from bodyPos+4 till end.
    aResponse.body = "";
    aResponse.body += raw_response.substring(bodyPos+4);
    aResponse.status = atoi(statusCode.c_str());
}
