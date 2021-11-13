// I2C Keypad for Arduino
// Venkateswara Rao.E
// 19-oct-2015
// Credits to  @author Alexander Brevig

#include <Wire.h>

#include "LiquidCrystal_I2C.h"

LiquidCrystal_I2C lcd(0x27, 16, 2); // set the LCD 16 chars and 2 line display

#include "SoftwareI2C.h"
SoftwareI2C WireS1;

#include "Keypad_I2C.h"
#include "Keypad.h"

#define I2CADDR 0x24

#define SDA_PIN D5
#define SCL_PIN D4

const int8_t I2C_MASTER = 0x42;
const int8_t I2C_SLAVE = 0x08;


const byte ROWS = 4; //four rows
const byte COLS = 3; //three columns
char keys[ROWS][COLS] = {
  {'1', '2', '3'},
  {'4', '5', '6'},
  {'7', '8', '9'},
  {'*', '0', '#'}
};

// Digitran keypad, bit numbers of PCF8574 i/o port
byte rowPins[ROWS] = {0, 1, 2, 4}; //connect to the row pinouts of the keypad
byte colPins[COLS] = {5, 6, 7}; //connect to the column pinouts of the keypad

Keypad_I2C kpd( makeKeymap(keys), rowPins, colPins, ROWS, COLS, I2CADDR, PCF8574 );

void setup() {

  WireS1.begin(SDA_PIN, SCL_PIN);       // join i2c bus (address optional for master)
  kpd.begin(makeKeymap(keys));
  lcd.begin();
  lcd.backlight();

  lcd.setCursor(0, 0);
  lcd.print("  KO-AAHAM TECH ");
  lcd.setCursor(0, 1);
  lcd.print("Initializing....");
  delay(3000);
  lcd.clear();

  Serial.begin(115200);
  Serial.println( "start" );

}

void loop() {
  char key = kpd.getKey();

  if (key) {
    Serial.println(key);
    lcd.setCursor(0, 0);
    lcd.print(key);
  }
}
