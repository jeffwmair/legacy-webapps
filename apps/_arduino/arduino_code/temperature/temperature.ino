/*
  ReadAnalogVoltage
  Reads an analog input on pin 0, converts it to voltage, and prints the result to the serial monitor.
  Attach the center pin of a potentiometer to pin A0, and the outside pins to +5V and ground.
 
 This example code is in the public domain.
 */
#include <SPI.h>
#include <Ethernet.h>
#include <stdlib.h>


// mac address of the ethernet module
byte mac[] = {  0x90, 0xA2, 0xDA, 0x00, 0x9B, 0x3E };
//IPAddress server(192,168,0,14);   
char server[] = "www.jefftron.com";
char *HEADER_WEB_AUTH = "Authorization: Basic MTU5aHVyb246MTU5aHVyb24=";
char *HEADER_HOST = "Host: www.jefftron.com";
EthernetClient client;
bool lostConnection = false;
int unableToConnectCount = 0;

// the setup routine runs once when you press reset:
void setup() {
	
	// initialize serial communication at 9600 bits per second:
	Serial.begin(9600);
	Serial.println('Started serial port');

	/***** setup ethernet comms *****/
	Ethernet.begin(mac);
	
}

	// the loop routine runs over and over again forever:
void loop() {

	int sensorValue = analogRead(A0);
	// Convert the analog reading (which goes from 0 - 1023) to a voltage (0 - 5V):
	float sensorMvConversion = 100.0;
	float temperatureInDegreesC = sensorMvConversion * sensorValue * (5.0 / 1023.0);
	sendTemperatureToServer(temperatureInDegreesC);
	// Serial.print("s");
	// 	Serial.print(temperatureInDegreesC);
	// 	Serial.print("e");
	delay(1800000);
	// delay(5000);
	
}

void sendTemperatureToServer(float fTemperature)
{	
	char sTemperature[5];
	dtostrf(fTemperature,5,2,sTemperature);
	// char *requestString = "GET /_159Huron/UpdateTemperature.php?t=##.## HTTP/1.1";
	// int requestStringStartTemperatureIndex = 38;
	// for(int i = 0; i < 5; i++)
	// {
		// requestString[requestStringStartTemperatureIndex+i] = sTemperature[i];
	// }
	char * requestPrefix = "GET /WebApps/_arduino/UpdateTemperature.php?t=";
	char * requestSuffix = " HTTP/1.1";
	char * requestString = (char *)malloc(strlen(requestPrefix) + strlen(sTemperature) + strlen(requestSuffix));
	strcpy(requestString, requestPrefix);
	strcat(requestString, sTemperature);
	strcat(requestString, requestSuffix);
	Serial.println(requestString);
	client.flush();		// throw away anything being received from the heartbeat
	client.stop();
	tryToConnect();
	
	client.println(requestString);
	client.println(HEADER_HOST);
	client.println(HEADER_WEB_AUTH);
	client.println();
	free(requestString);
}

void tryToConnect()
{
	Serial.println("Beginning tryToConnect");
	while (!client.connected())
	{
		if (client.connect(server,80))
		{
			Serial.println("Successfully connected");
			lostConnection = false;
			unableToConnectCount = 0;
		}
		else
		{
			unableToConnectCount++;
			Serial.print(unableToConnectCount);
			Serial.println("Not able to connect yet, waiting 1000 ms, trying again.");
			delay(1000);
			client.flush();
			client.stop();
			lostConnection = true;
		}
	}
}
