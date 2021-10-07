
#include "MLX90614.h"
/**
   @brief Construct a new MLX90614::MLX90614 object

   @param i2caddr The I2C address to use. Defaults to 0x5A
*/
MLX90614::MLX90614(uint8_t i2caddr) {
  _addr = i2caddr;
}
/**
   @brief Begin the I2C connection

   @return bool  Always returns true
*/
bool MLX90614::begin(void) {
  Wire.begin(D2,D1);
  //Wire.setClock(I2C_BUS_SPEED);

  /*
    for (uint8_t i=0; i<0x20; i++) {
    Serial.print(i); Serial.print(" = ");
    Serial.println(read16(i), HEX);
    }
  */
  return true;
}

/**
   @brief Read the raw value from the emissivity register

   @return uint16_t The unscaled emissivity value
*/
uint16_t MLX90614::readEmissivityReg(void) {
  return read16(MLX90614_EMISS);
}
/**
   @brief Write the raw unscaled emissivity value to the emissivity register

   @param ereg The unscaled emissivity value
*/
void MLX90614::writeEmissivityReg(uint16_t ereg) {
  write16(MLX90614_EMISS, 0); // erase
  delay(10);
  write16(MLX90614_EMISS, ereg);
  delay(10);
}
/**
   @brief Read the emissivity value from the sensor's register and scale

   @return double The emissivity value, ranging from 0.1 - 1.0
*/
double MLX90614::readEmissivity(void) {
  uint16_t ereg = read16(MLX90614_EMISS);
  return ((double)ereg) / 65535.0;
}
/**
   @brief Set the emissivity value

   @param emissivity The emissivity value to use, between 0.1 and 1.0
*/
void MLX90614::writeEmissivity(double emissivity) {
  uint16_t ereg = int(0xffff * emissivity);

  writeEmissivityReg(ereg);
}

/**
   @brief Get the current temperature of an object in degrees Farenheit

   @return double The temperature in degrees Farenheit
*/
double MLX90614::readObjectTempF(void) {
  return (readTemp(MLX90614_TOBJ1) * 9 / 5) + 32;
}
/**
   @brief Get the current ambient temperature in degrees Farenheit

   @return double The temperature in degrees Farenheit
*/
double MLX90614::readAmbientTempF(void) {
  return (readTemp(MLX90614_TA) * 9 / 5) + 32;
}

/**
   @brief Get the current temperature of an object in degrees Celcius

   @return double The temperature in degrees Celcius
*/
double MLX90614::readObjectTempC(void) {
  return readTemp(MLX90614_TOBJ1);
}

/**
   @brief Get the current ambient temperature in degrees Celcius

   @return double The temperature in degrees Celcius
*/
double MLX90614::readAmbientTempC(void) {
  return readTemp(MLX90614_TA);
}

float MLX90614::readTemp(uint8_t reg) {
  float temp;

  temp = read16(reg);
  temp *= .02;
  temp -= 273.15;
  return temp;
}

/*********************************************************************/

uint16_t MLX90614::read16(uint8_t a) {
  uint16_t ret;

  Wire.beginTransmission(_addr); // start transmission to device
  Wire.write(a);                 // sends register address to read from
  Wire.endTransmission(false);   // end transmission

  Wire.requestFrom(_addr, (size_t)3); // send data n-bytes read
  ret = Wire.read();                  // receive DATA
  ret |= Wire.read() << 8;            // receive DATA

  uint8_t pec = Wire.read();

  return ret;
}

byte MLX90614::crc8(byte *addr, byte len)
// The PEC calculation includes all bits except the START, REPEATED START, STOP,
// ACK, and NACK bits. The PEC is a CRC-8 with polynomial X8+X2+X1+1.
{
  byte crc = 0;
  while (len--) {
    byte inbyte = *addr++;
    for (byte i = 8; i; i--) {
      byte carry = (crc ^ inbyte) & 0x80;
      crc <<= 1;
      if (carry)
        crc ^= 0x7;
      inbyte <<= 1;
    }
  }
  return crc;
}

void MLX90614::write16(uint8_t a, uint16_t v) {
  uint8_t pec;
  uint8_t pecbuf[4];

  pecbuf[0] = _addr << 1;
  pecbuf[1] = a;
  pecbuf[2] = v & 0xff;
  pecbuf[3] = v >> 8;
  pec = crc8(pecbuf, sizeof pecbuf);

  Wire.beginTransmission(_addr); // start transmission to device
  Wire.write(a);                 // sends register address to write
  Wire.write(v & 0xff);          // lo
  Wire.write(v >> 8);            // hi
  Wire.write(pec);               // pec
  Wire.endTransmission(true);    // end transmission
}
