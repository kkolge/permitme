#include "IdGuard.h"
#include <Wire.h>
#include <EEPROM.h>
#include "LiquidCrystal_I2C.h"

// Set the LCD address to 0x27 or 0x3F as per LCD//////////////////////////
//Ketan --  CHANGE TOCKEN TO TOKEN

byte LCD_ADD = 0x27;

LiquidCrystal_I2C lcd(LCD_ADD, 16, 2);

String DEVICE_ID = "DEVKODEMO8";    // Enter Device ID///////////////////////
                                     // 0000000000000000
String random1 = "0000000000000000"; //clear tocken or relpace with letest tocken/////////

#define _DEBUG                       // UNCOMMENT FOR DEBUGGING//////////////////////////

//#define _WRITE_LCD_EEPROM            // UNCOMMENT FOR WRITEING LCD I2C ADDRESS TO EEPROM ///////

//#define _WRITE_DEVICE_ID_EEPROM      // UNCOMMENT FOR WRITEING DEVICE ID TO EEPROM ///////

#define _WRITE_TOCKEN_EEPROM         // UNCOMMENT FOR WRITEING TOCKEN ID TO EEPROM ///////


String deviceid = "";
String retrievedString = "";
int ledPin = D0;

#ifdef _WRITE_TOCKEN_EEPROM

//Ketan -- you get the token as 16 byte Hex . convert the string into 16 bytes Hex and save so you can save lots of space in the memory. Instead of writing 32 bytes - you need to deal with only 16 bytes. 
// you can use String to Hex and reverse functions
void writeStringToEEPROM(int addrOffset, const String &strToWrite) 
{
  byte len = strToWrite.length();
  EEPROM.write(addrOffset, len);
  for (int i = 0; i < len; i++)
  {
    EEPROM.write(addrOffset + 1 + i, strToWrite[i]);
  }
  EEPROM.commit();
}
String readStringFromEEPROM(int addrOffset)
{
  int newStrLen = EEPROM.read(addrOffset);
  char data[newStrLen + 1];
  for (int i = 0; i < newStrLen; i++)
  {
    data[i] = EEPROM.read(addrOffset + 1 + i);
    delay(1);
  }
  data[newStrLen] = '\0'; // !!! NOTE !!! Remove the space between the slash "/" and "0" (I've added a space because otherwise there is a display bug)
  return String(data);
}
#endif


#ifdef  _WRITE_LCD_EEPROM
void LCD_ADD_EEPROM_WR()
{
#ifdef _DEBUG
  Serial.print("Write LCD I2C ADDRESS TO 400 = ");
  Serial.println(LCD_ADD, HEX);
#endif
  EEPROM.write(400, LCD_ADD);
  delay(1);
  EEPROM.commit();
}

byte LCD_ADD_EEPROM_RD()
{
#ifdef _DEBUG
  Serial.print("READ LCD I2C ADDRESS AT 400 =");
  Serial.println(EEPROM.read(400), HEX);
#endif
  return (EEPROM.read(400));
}
#endif

void setup() {

  // initialize the LCD
  lcd.begin();
  // Turn on the blacklight and print a message.
  lcd.backlight();
  lcd.print("EERROM WRITEING");

#ifdef _DEBUG
  Serial.begin(115200);
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }
  Serial.println("serial start");
#endif

  EEPROM.begin(512); //Initialasing EEPROM


#ifdef  _WRITE_DEVICE_ID_EEPROM

  IdGuard.offset = 300;
  IdGuard.error_led_pin = ledPin;
  IdGuard.writeIdAndRestartDevice(DEVICE_ID, DEVICE_ID.length());
#ifdef _DEBUG
  Serial.println("write Id And Restart Device");
#endif
  IdGuard.forceId(DEVICE_ID);

#ifdef _DEBUG
  Serial.println(DEVICE_ID);
#endif
  deviceid = IdGuard.readId();   // Only reads ID from EEPROM.
#ifdef _DEBUG
  Serial.println(deviceid);
#endif

#endif

  delay(1000);


#ifdef _WRITE_TOCKEN_EEPROM

#ifdef _DEBUG
  Serial.println();
  Serial.println();
  Serial.println("EEPROM Write Tocken");
#endif
  delay(1000);

#ifdef _DEBUG
  Serial.println(random1);
#endif
  delay(1000);
  writeStringToEEPROM(350, random1 );  // Tocken
  delay(1000);

  retrievedString = readStringFromEEPROM(350);
  delay(1000);

#ifdef _DEBUG
  Serial.println("The String we read from EEPROM: ");
  Serial.println(retrievedString);
#endif

#endif


#ifdef _WRITE_LCD_EEPROM

  LCD_ADD_EEPROM_WR();

  LCD_ADD_EEPROM_RD();
  
#endif

}

void loop()
{
#ifdef _DEBUG
  Serial.print("DEVICE ID : ");
  Serial.println(deviceid);
  Serial.print("LCD ADDRESS : ");
  Serial.println(LCD_ADD);
  Serial.print("TOCKEN ID : ");
  Serial.println(retrievedString);
#endif

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(deviceid);
  lcd.setCursor(0, 1);
  lcd.print("LCD ADD : ");
  lcd.print(LCD_ADD);
  delay(3000);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Radom1 : ");
  lcd.print(retrievedString);
  delay(3000);
}
