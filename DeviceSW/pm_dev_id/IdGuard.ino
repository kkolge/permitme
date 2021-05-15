#include "IdGuard.h"

const char* DEVICE_ID = "DEVKOTEST1";
String deviceid = "";
int ledPin = D0;

//#define _DEBUG

void setup() {

#ifdef _DEBUG
  Serial.begin(115200);
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }
  Serial.println("serial start");
#endif

  EEPROM.begin(512); //Initialasing EEPROM



  // Optional offset to write ID in 4th byte from the end in EEPROM.
  // Defaults to 0, which means ID is stored in last byte in EEPROM.
  IdGuard.offset = 100;

  // Recommended, but optional led for error signalization.
  IdGuard.error_led_pin = ledPin;

  // Writes DEVICE_ID to EEPROM memory.
  // LED defined by error_led_pin blinks I and D letters in Morse code "..|-..".
  // Restarts device to prevent execution of any following code.
  // Comment out this line after ID is successfully stored in EEPROM.
  IdGuard.writeIdAndRestartDevice(DEVICE_ID);
#ifdef _DEBUG
  Serial.println("write Id And Restart Device");
#endif
  // Checks DEVICE_ID against last byte in EEPROM memory.
  // Blinks I and D in morse code "..|-.."and restarts device in case of
  // mismatch to prevent execution of any following code.
  IdGuard.forceId(DEVICE_ID);
#ifdef _DEBUG
  Serial.println(DEVICE_ID);
#endif
  // Only reads ID from EEPROM.
  deviceid = IdGuard.readId();
#ifdef _DEBUG
  Serial.println(deviceid);
#endif
}

void loop()
{
#ifdef _DEBUG
  Serial.println(deviceid);
  delay(1000);
  // main loop will be never called in case of ID mismatch thanks to IdGuard
#endif
}
