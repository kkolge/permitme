#pragma once

#include <EEPROM.h>

class IdGuardClass
{
  public:
    uint8_t error_led_pin = -1;
    int offset;
    String readId();
    void forceId(String myId);
    void writeIdAndRestartDevice(String myId, uint8_t siz);
  private:
    void _resetDevice();
    void _ledBlink(bool dot);

};

void IdGuardClass::forceId(String myId) {
  String rid = readId();

  if (rid != myId) {
    // _resetDevice();
    //ESP.reset();
  }

}

void IdGuardClass::writeIdAndRestartDevice(String myId, uint8_t siz) {
  int i;
  String rid = readId();
  if (rid != myId) {

    for (i = 0 ; i < 20 ; i++) {
      EEPROM.write(offset + i, 0);
    }

    for (i = 0; i < siz; ++i)
    {
      EEPROM.write(offset + i, myId[i]);
    }
    EEPROM.write(offset + i + 1 , '\0');

    EEPROM.commit();
  }

  //_resetDevice();
  //ESP.reset();
}

String IdGuardClass::readId() {
  String rid = "";
  for (int i = 0; i < 20; ++i)
  {
    if (EEPROM.read(offset + i) == '\0')
      break;
    rid += char(EEPROM.read(offset + i));
  }
  return (rid);
}

void IdGuardClass::_resetDevice() {

  if (error_led_pin >= 0) {
    pinMode(error_led_pin, OUTPUT);

    // I
    _ledBlink(true);
    _ledBlink(true);
    delay(1000);
    // D
    _ledBlink(false);
    _ledBlink(true);
    _ledBlink(true);
    delay(2000);
  }

  //  void(* resetFunc) (void) = 0;  // declare reset fuction at address 0
  //  resetFunc();
  //ESP.reset();
}

void IdGuardClass::_ledBlink(bool dot) {
  digitalWrite(error_led_pin, HIGH);
  delay(dot ? 300 : 1000);
  digitalWrite(error_led_pin, LOW);
  delay(300);
}

static IdGuardClass IdGuard;
