#include <ESP8266WiFi.h>
#include <CertStoreBearSSL.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>
#include <EEPROM.h>
#include <Wire.h>
#include <time.h>
#include <FS.h>
#include <ArduinoJson.h>

#include "MAX30102.h"
#include "Pulse.h"
#include "LiquidCrystal_I2C.h"
#include "MLX90614.h"
#include "MD5.h"


//#define _DEBUG      // UNCOMMENT FOR DEBUGGING/////////////////////////////

//#define _http   //NOT USED //  // COMMENT FOR HTTPS UNCOMMENT FOR HTTP //////////////////////

//#define _KBRD     // COMMENT FOR RFID INTERFACE UNCOMMENT FOR KEYBORD INTERFACE /////////////////////

#ifdef _KBRD

#include "PS2Keyboard.h"
const int DataPin = D4;
const int IRQpin =  D3;

PS2Kbd keyboard(DataPin, IRQpin);

#else

#include <SPI.h>
#include "MFRC522.h"
#define SS_PIN D4
#define RST_PIN D3

MFRC522 mfrc522(SS_PIN, RST_PIN);   // Create MFRC522 instance.

#endif


byte x, y;  // track lcd position
char c;
char MobNo[11] = " "; // Ketan -- we may have to use aadhar number at some places so make this 12 chars

LiquidCrystal_I2C lcd(16, 2); // set the LCD 16 chars and 2 line display

String deviceid;  // = "DEVKOTEST1";      // DEV-KO-001  ko-aaham office

MLX90614 mlx = MLX90614();
MAX30102 sensor;
Pulse pulseIR;
Pulse pulseRed;
MAFilter bpm;

//spo2_table is approximated as  -45.060*ratioAverage* ratioAverage + 30.354 *ratioAverage + 94.845 ;
//Ketan -- as per the comment is stand alone
const uint8_t spo2_table[184] PROGMEM =
{ 95, 95, 95, 96, 96, 96, 97, 97, 97, 97, 97, 98, 98, 98, 98, 98, 99, 99, 99, 99,
  99, 99, 99, 99, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100, 100,
  100, 100, 100, 100, 99, 99, 99, 99, 99, 99, 99, 99, 98, 98, 98, 98, 98, 98, 97, 97,
  97, 97, 96, 96, 96, 96, 95, 95, 95, 94, 94, 94, 93, 93, 93, 92, 92, 92, 91, 91,
  90, 90, 89, 89, 89, 88, 88, 87, 87, 86, 86, 85, 85, 84, 84, 83, 82, 82, 81, 81,
  80, 80, 79, 78, 78, 77, 76, 76, 75, 74, 74, 73, 72, 72, 71, 70, 69, 69, 68, 67,
  66, 66, 65, 64, 63, 62, 62, 61, 60, 59, 58, 57, 56, 56, 55, 54, 53, 52, 51, 50,
  49, 48, 47, 46, 45, 44, 43, 42, 41, 40, 39, 38, 37, 36, 35, 34, 33, 31, 30, 29,
  28, 27, 26, 25, 23, 22, 21, 20, 19, 17, 16, 15, 14, 12, 11, 10, 9, 7, 6, 5,
  3, 2, 1
} ;

const uint8_t MAXWAVE = 36;

class Waveform {
  public:
    Waveform(void) {
      wavep = 0;
    }

    void record(int waveval) {
      waveval = waveval / 8;       // scale to fit in byte
      waveval += 128;              //shift so entired waveform is +ve
      waveval = waveval < 0 ? 0 : waveval;
      waveform[wavep] = (uint8_t) (waveval > 255) ? 255 : waveval;
      wavep = (wavep + 1) % MAXWAVE;
    }

    void scale() {
      uint8_t maxw = 0;
      uint8_t minw = 255;
      for (int i = 0; i < MAXWAVE; i++) {
        maxw = waveform[i] > maxw ? waveform[i] : maxw;
        minw = waveform[i] < minw ? waveform[i] : minw;
      }
      uint8_t scale8 = (maxw - minw) / 4 + 1; //scale * 8 to preserve precision
      uint8_t index = wavep;
      for (int i = 0; i < MAXWAVE; i++) {
        disp_wave[i] = 31 - ((uint16_t)(waveform[index] - minw) * 8) / scale8;
        index = (index + 1) % MAXWAVE;
      }
    }


  private:
    uint8_t waveform[MAXWAVE];
    uint8_t disp_wave[MAXWAVE];
    uint8_t wavep = 0;

} wave;


uint8_t flag = 0;
int  beatAvg;
int  SPO2;//, SPO2f;

//bool filter_for_graph = false;
bool draw_Red = false;

bool tag_found = false;
static const unsigned long frequency  = 5000;
unsigned long previous = 0;

bool TEMPStaus = false;
bool SPO2Staus = false;

bool TEMPTOut = false;
bool SPO2TOut = false;

const int numReadings = 10;
int average_hbcount = 0;
int average_spo2 = 0;
float average_temp = 0;

long lastBeat = 0;    //Time of the last beat
long displaytime = 0; //Time of the last display update

bool mob_found = false;
bool valid_user = false;
int time_out_kb = 0;

int8_t espo2 = 0;
float etemp = 0;
int8_t ehbcount = 0;

bool DevType = false;
bool DevVldSt = false;
bool DevUpSt = false;
bool Proto = false;

char *random1 = "";
String random2 = "";
String qstatus = "";

String qhbcount = "";
String qspo2 = "";
String qtemp = "";
String qdevtype = "";

String qrandom1 = "";
String qrandom2 = "";

String esrvname = "";
String epcol = "";

//I2C Address
//LCD =0x27
//MLX90614=0x57
//MAX30100=0x5A

const int analogInPin = A0;
static const uint8_t RLED = D0;
static const uint8_t GLED = D8;
static const uint8_t BUZ = D9;
static const uint8_t RLY = D10;

int sensorValue = 0;        // value read from the pot

//Establishing Local server at port 80 whenever required
ESP8266WebServer server(80);

String userid = "";
String username = "";
bool dataresp = false;
String flagst = "";
String Uflagst = "";

//Function Decalration
bool testWifi(void);
void launchWeb(void);
void setupAP(void);

void err_rled_buz_3();
void ok_gled_buz_2();
void err_data_rgled_buz_4();

void lcdprintChar(char t);

int i = 0;
int statusCode;
String st = "";
String content = "";


// A single, global CertStore which can be used by all
// connections.  Needs to stay live the entire time any of
// the WiFiClientBearSSLs are present.
BearSSL::CertStore certStore;

// Set time via NTP, as required for x.509 validation
void setClock() {
  configTime(3 * 3600, 0, "pool.ntp.org", "time.nist.gov");

  Serial.print("Waiting for NTP time sync: ");
  time_t now = time(nullptr);
  while (now < 8 * 3600 * 2) {
    delay(500);
    Serial.print(".");
    now = time(nullptr);
  }
  Serial.println("");
  struct tm timeinfo;
  gmtime_r(&now, &timeinfo);
  Serial.print("Current time: ");
  Serial.print(asctime(&timeinfo));
}



//FOR KEYBORD KEY PRINT  AND UPDATE
void lcdprintChar(char t) { // display char on lcd and Serial
  MobNo[x] = t;
#ifdef _DEBUG
  Serial.print(t);
#endif
  x++;
  if (t < '0' || t > '9')
  {
    memset(MobNo, 0, sizeof(MobNo));
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Pls Enter Valid ");
    lcd.setCursor(0, 1);
    lcd.print(" Mobile Number  ");
    delay(2000);
    lcd.clear();
    x = 0; y = 0;
    lcd.setCursor(x, y);
  }


  if (x > 10) {
    memset(MobNo, 0, sizeof(MobNo));
    lcd.clear();
    x = 0; y = 0;
    lcd.setCursor(x, y);
  }
}



bool testWifi(void)
{
  int c = 0;
#ifdef _DEBUG
  Serial.println("Waiting for Wifi to connect");
#endif
  while ( c < 20 ) {
    if (WiFi.status() == WL_CONNECTED)
    {
      return true;
    }
    delay(500);
#ifdef _DEBUG
    Serial.print("*");
#endif
    c++;
  }
#ifdef _DEBUG
  Serial.println("");
  Serial.println("Connect timed out, opening AP");
#endif

  return false;
}

void launchWeb()
{
#ifdef _DEBUG
  Serial.println("");
#endif
  if (WiFi.status() == WL_CONNECTED) {
#ifdef _DEBUG
    Serial.println("WiFi connected");
#endif
  }
#ifdef _DEBUG
  Serial.print("Local IP: ");
  Serial.println(WiFi.localIP());
  Serial.print("SoftAP IP: ");
  Serial.println(WiFi.softAPIP());
#endif
  createWebServer(); // Start the server
  server.begin();
#ifdef _DEBUG
  Serial.println("Server started");
#endif
}

void setupAP(void)
{
  WiFi.mode(WIFI_STA);
  WiFi.disconnect();
  delay(100);
  int n = WiFi.scanNetworks();
#ifdef _DEBUG
  Serial.println("scan done");
#endif
  if (n == 0) {
#ifdef _DEBUG
    Serial.println("no networks found");
#endif
  }
  else
  {
#ifdef _DEBUG
    Serial.print(n);
    Serial.println("networks found");
#endif
    for (int i = 0; i < n; ++i)
    {
      // Print SSID and RSSI for each network found
#ifdef _DEBUG
      Serial.print(i + 1);
      Serial.print(": ");
      Serial.print(WiFi.SSID(i));
      Serial.print(" (");
      Serial.print(WiFi.RSSI(i));
      Serial.print(")");
      Serial.println((WiFi.encryptionType(i) == ENC_TYPE_NONE) ? " " : "*");
#endif
      delay(10);
    }
  }

#ifdef _DEBUG
  Serial.println("");
#endif

  st = "<ol>";
  for (int i = 0; i < n; ++i)
  {
    // Print SSID and RSSI for each network found
    st += "<li>";
    st += WiFi.SSID(i);
    st += " (";
    st += WiFi.RSSI(i);

    st += ")";
    st += (WiFi.encryptionType(i) == ENC_TYPE_NONE) ? " " : "*";
    st += "</li>";
  }
  st += "</ol>";
  delay(100);
  WiFi.softAP("KO AAHAM", "KO AAHAM");
#ifdef _DEBUG
  Serial.println("Initializing_softap_for_wifi credentials_modification");
#endif
  launchWeb();
#ifdef _DEBUG
  Serial.println("over");
#endif
}


void createWebServer()
{
  {
    server.on("/", []() {

      IPAddress ip = WiFi.softAPIP();
      String ipStr = String(ip[0]) + '.' + String(ip[1]) + '.' + String(ip[2]) + '.' + String(ip[3]);
      content = "<!DOCTYPE HTML>\r\n<html>Welcome to Wifi Credentials Update page";
      content += "<form action=\"/scan\" method=\"POST\"><input type=\"submit\" value=\"scan\"></form>";
      content += ipStr;
      content += "<p>";
      content += st;
      content += "</p>";
      content += "<form method='get' action='setting'><label>SSID: </label><input name='ssid' length=32>";
      content += "<br/>";
      content += "<label>PASS: </label><input name='pass' length=64>";
      content += "<br/>";
      content += "<label>Server: </label><input name='srvname' length=100>";
      content += "<br/>";
      content += "<label>Protocol: </label><input name='pcol' length=5>";
      content += "<br/>";
      content += "<input type='submit'></form>";
      content += "</html>";
      server.send(200, "text/html", content);
    });
    server.on("/scan", []() {
      //setupAP();
      IPAddress ip = WiFi.softAPIP();
      String ipStr = String(ip[0]) + '.' + String(ip[1]) + '.' + String(ip[2]) + '.' + String(ip[3]);

      content = "<!DOCTYPE HTML>\r\n<html>go back";
      server.send(200, "text/html", content);
    });

    server.on("/setting", []() {
      String qssid = server.arg("ssid");
      String qpass = server.arg("pass");
      //getting new variables
      String qsrvName = server.arg("srvname");
      String qpcol = server.arg("pcol");
      //end getting new variables
      if (qssid.length() > 0 || qpass.length() > 0 || qsrvName.length() > 0 || qpcol.length() > 0 ) {
#ifdef _DEBUG
        Serial.println(qssid);
        Serial.println("");
        Serial.println(qpass);
        Serial.println("");
        //printing the new values
        Serial.println(qsrvName);
        Serial.println("");
        Serial.println(qpcol);
        Serial.println("");

        Serial.println("writing eeprom ssid:");
#endif
        if (qssid.length() > 0 && qssid.length() <32) {
#ifdef _DEBUG
          Serial.println("Clearing EEPROM SSID");
#endif
          for (int i = 0; i < 31; ++i) {
            EEPROM.write(i, 0);
          }

          for (int i = 0; i < qssid.length(); ++i)
          {
            EEPROM.write(i, qssid[i]);
#ifdef _DEBUG
            Serial.print("Wrote: ");
            Serial.println(qssid[i]);
#endif
          }
        }
#ifdef _DEBUG
        Serial.println("writing eeprom pass:");
#endif
        if (qpass.length() > 0 && qpass.length()< 32) {
#ifdef _DEBUG
          Serial.println("Clearing EEPROM PASS");
#endif
          for (int i = 32; i < 63; ++i) {
            EEPROM.write(i, 0);
          }

          for (int i = 0; i < qpass.length(); ++i)
          {
            EEPROM.write(32 + i, qpass[i]);
#ifdef _DEBUG
            Serial.print("Wrote: ");
            Serial.println(qpass[i]);
#endif
          }
        }
#ifdef _DEBUG
        Serial.println("writing eeprom server name:");   //Start writing the server name
#endif
        if (qsrvName.length() > 0 && qsrvName.length() < 100) {
#ifdef _DEBUG
          Serial.println("Clearing EEPROM SERVER NAME");
#endif
          for (int i = 97; i < 196; ++i) {
            EEPROM.write(i, 0);
          }
          for (int i = 0 ; i < qsrvName.length(); ++i)
          {
            EEPROM.write(97 + i, qsrvName[i]);
#ifdef _DEBUG
            Serial.print("Wrote: ");
            Serial.println(qsrvName[i]);
#endif
          }
        }
#ifdef _DEBUG
        Serial.println("writing eeprom protocol:");  //Start writing the protocol
#endif
        if (qpcol.length() > 0 && qpcol.length() < 6) {
#ifdef _DEBUG
          Serial.println("Clearing EEPROM PROTOCOL");
#endif
          for (int i = 197; i < 202; ++i) {
            EEPROM.write(i, 0);
          }
          for (int i = 0; i < qpcol.length(); ++i)
          {
            EEPROM.write(197 + i, qpcol[i]);
#ifdef _DEBUG
            Serial.print("Wrote: ");
            Serial.println(qpcol[i]);
#endif
          }
        }
        EEPROM.commit();

        content = "{\"Success\":\"saved to eeprom... reset to boot into new wifi\"}";
        statusCode = 200;
        ESP.reset();
      } else {
        content = "{\"Error\":\"404 not found\"}";
        statusCode = 404;
#ifdef _DEBUG
        Serial.println("Sending 404");
#endif
      }
      server.sendHeader("Access-Control-Allow-Origin", "*");
      server.send(statusCode, "application/json", content);

    });
  }
}

String getValue(String data, char separator, int index)
{
  int found = 0;
  int strIndex[] = {0, -1};
  int maxIndex = data.length() - 1;

  for (int i = 0; i <= maxIndex && found <= index; i++) {
    if (data.charAt(i) == separator || i == maxIndex) {
      found++;
      strIndex[0] = strIndex[1] + 1;
      strIndex[1] = (i == maxIndex) ? i + 1 : i;
    }
  }

  return found > index ? data.substring(strIndex[0], strIndex[1]) : "";
}



void HB_SPO2_OUT()
{
  //  int readings_spo2[numReadings];      // the readings from the SPO2f
  //  int readIndex_spo2 = 0;              // the index of the current reading
  //  int total_spo2 = 0;                  // the running total
  //  // the average
  //
  int readings_hbcount[numReadings];      // the readings from the beatAvg
  int readIndex_hbcount = 0;              // the index of the current reading
  int total_hbcount = 0;                  // the running total
  // the average

  for (int thisReading = 0; thisReading < numReadings; thisReading++) {
    //readings_spo2[thisReading] = 0;
    readings_hbcount[thisReading] = 0;
  }

  int i = 0;
  uint16_t time_out_Spo2 = 0;
  int8_t  SPO2_max = 0;
  SPO2TOut = true;
  SPO2 = beatAvg = 0;

  if (!sensor.begin())  {
#ifdef _DEBUG
    Serial.println("MAX30102 NOT FOUND");
#endif
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SENSOR NOT FOUND");
    delay(500);
    while (1);
  }
#ifdef _DEBUG
  Serial.println("MAX30102 Setup");
#endif
  sensor.setup();
  delay(100);

  while (1) {

    //Serial.print("i="); Serial.println(i);
    sensor.check();
    //delay(500);
    //if (!sensor.available()) return;
    sensor.available();
    long now = millis();   //start time of this cycle
    uint32_t irValue = sensor.getIR();
    uint32_t redValue = sensor.getRed();

    sensor.nextSample();

    if (irValue < 5000) {
#ifdef _DEBUG
      Serial.println("   finger not detected    ");
#endif
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("   finger not   ");
      lcd.setCursor(0, 1);
      lcd.print("    detected    ");
      time_out_Spo2++;
      delay(100);
    }

    else {
      int16_t IR_signal, Red_signal;
      bool beatRed, beatIR;
      //      if (!filter_for_graph) {
      //        IR_signal =  pulseIR.dc_filter(irValue) ;
      //        Red_signal = pulseRed.dc_filter(redValue);
      //        beatRed = pulseRed.isBeat(pulseRed.ma_filter(Red_signal));
      //        beatIR =  pulseIR.isBeat(pulseIR.ma_filter(IR_signal));
      //      } else {
      IR_signal =  pulseIR.ma_filter(pulseIR.dc_filter(irValue)) ;
      Red_signal = pulseRed.ma_filter(pulseRed.dc_filter(redValue));
      beatRed = pulseRed.isBeat(Red_signal);
      beatIR =  pulseIR.isBeat(IR_signal);
      //      }
      // invert waveform to get classical BP waveshape
      wave.record(draw_Red ? -Red_signal : -IR_signal );
      // check IR or Red for heartbeat
      if (draw_Red ? beatRed : beatIR) {
        long btpm = 60000 / (now - lastBeat);
        if (btpm > 0 && btpm < 200) beatAvg = bpm.filter((int16_t)btpm);
        lastBeat = now;
        // compute SpO2 ratio
        long numerator   = (pulseRed.avgAC() * pulseIR.avgDC()) / 256;
        long denominator = (pulseRed.avgDC() * pulseIR.avgAC()) / 256;
        int RX100 = (denominator > 0) ? (numerator * 100) / denominator : 999;
        // using formula
        //SPO2f = (10400 - RX100 * 17 + 50) / 100;

        // from table
        if ((RX100 >= 0) && (RX100 < 184))
          SPO2 = pgm_read_byte_near(&spo2_table[RX100]);
      }
      // update display every 50 ms if fingerdown
      if (now - displaytime > 50) {
        displaytime = now;
        wave.scale();
        if (i > 190)
        {
          //          total_spo2 = total_spo2 - readings_spo2[readIndex_spo2];
          //          readings_spo2[readIndex_spo2] = SPO2;
          //          // add the reading to the total:
          //          total_spo2 = total_spo2 + readings_spo2[readIndex_spo2];
          //          // advance to the next position in the array:
          //          readIndex_spo2 = readIndex_spo2 + 1;
          //
          total_hbcount = total_hbcount - readings_hbcount[readIndex_hbcount];
          readings_hbcount[readIndex_hbcount] = beatAvg;
          // add the reading to the total:
          total_hbcount = total_hbcount + readings_hbcount[readIndex_hbcount];
          // advance to the next position in the array:
          readIndex_hbcount = readIndex_hbcount + 1;

          if ( readIndex_hbcount >= numReadings) { //readIndex_spo2 >= numReadings &&
            // ...wrap around to the beginning:
            //readIndex_spo2 = 0;
            readIndex_hbcount = 0;
          }
        }

#ifdef _DEBUG
        Serial.print("SPO2=");
        Serial.println(SPO2);
        Serial.print("Pulse Rate: ");
        Serial.println(beatAvg);
#endif
        //lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Pulse Rate: ");
        lcd.print(beatAvg); lcd.print("          ");
        lcd.setCursor(0, 1);
        lcd.print("SPO2: ");
        lcd.print(SPO2); lcd.print("          ");
        i++;
        if (SPO2 > SPO2_max && i > 100)
          SPO2_max = SPO2;
        delay(1);
      }
    }
    if (i > 200)
    {
      SPO2TOut = false;
      break;
    }

    if (time_out_Spo2 >= 150) { // 1 minute time out
      err_rled_buz_3();
      lcd.clear();
      time_out_Spo2 = 0;
      break;
      SPO2TOut = true;
    }
  }

  average_spo2 = SPO2_max;

  // calculate the average:
  //  average_spo2 = total_spo2 / numReadings;
  average_hbcount = total_hbcount / numReadings;
  // send it to the computer as ASCII digits
#ifdef _DEBUG
  Serial.print("average_hbcount=");
  Serial.println(average_hbcount);
  Serial.print("average_spo2=");
  Serial.println(average_spo2);
  Serial.print("MAX_SPO2=");
  Serial.println(SPO2_max);
#endif
  delay(50);        // delay in between reads for stability
}


void MLX9614()
{
tscan:
  float readings_temp[numReadings];      // the readings from the beatAvg
  int readIndex_temp = 0;              // the index of the current reading
  float total_temp = 0;                  // the running total
  // the average

  for (int thisReading = 0; thisReading < numReadings; thisReading++) {
    readings_temp[thisReading] = 0;
  }
#ifdef _DEBUG
  Serial.println("MLX90614 begin");
#endif
  mlx.begin();
  delay(100);

  while (1)
  {
#ifdef _DEBUG
    Serial.print("Ambient = "); Serial.print(mlx.readAmbientTempC());
    Serial.print("*C\tObject = "); Serial.print(mlx.readObjectTempC()); Serial.println("*C");
    Serial.print("Ambient = "); Serial.print(mlx.readAmbientTempF());
    Serial.print("*F\tObject = "); Serial.print(mlx.readObjectTempF()); Serial.println("*F");
#endif
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Scaning Temp....");
    lcd.setCursor(0, 1);
    lcd.print("Temp: ");
    lcd.print(mlx.readObjectTempF());
    lcd.print("'F");
    total_temp = total_temp - readings_temp[readIndex_temp];
    readings_temp[readIndex_temp] = mlx.readObjectTempF();
    // add the reading to the total:
    total_temp = total_temp + readings_temp[readIndex_temp];
    // advance to the next position in the array:
    readIndex_temp = readIndex_temp + 1;

    if (readIndex_temp >= numReadings ) {
      // ...wrap around to the beginning:
      readIndex_temp = 0;
      break;
    }

    sensorValue = analogRead(analogInPin);
    if (sensorValue < 100) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print(" Stay closer to ");
      lcd.setCursor(0, 1);
      lcd.print("   Temp Sensor  ");
      err_rled_buz_3();
      goto tscan;
    }

    delay(500);

  }
  // calculate the average:
  average_temp = total_temp / numReadings;
#ifdef _DEBUG
  // send it to the computer as ASCII digits
  Serial.print("average_temp=");
  Serial.println(average_temp);
#endif
  delay(50);        // delay in between reads for stability
}


String Read_Device_Id() {
  int offset = 300;
  String rid = "";
  for (int8_t i = 0; i < 20; ++i)
  {
    if (EEPROM.read(offset + i) == '\0')
      break;
    rid += char(EEPROM.read(offset + i));
  }
  return (rid);
}



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
  }
  data[newStrLen] = '\0'; // !!! NOTE !!! Remove the space between the slash "/" and "0" (I've added a space because otherwise there is a display bug)
  return String(data);
}

void fetchURL_POST(BearSSL::WiFiClientSecure *client, const char *host, const uint16_t port, String path, String json) {

  if (!path) {
    path = "/";
  }

#ifdef _DEBUG
  Serial.printf("Trying: %s:443...", host);
#endif
  client->connect(host, port);

  if (!client->connected()) {
#ifdef _DEBUG
    Serial.printf("*** Can't connect. ***\n-------\n");
#endif
    return;
  }

  client->print(String("POST ") + path + " HTTP/1.1\r\n" +
                "Host: " + host + "\r\n" +
                "Content-Type: application/json\r\n" +
                "User-Agent: PermitMe\r\n" +
                "Content-Length: " + json.length() + "\r\n" +
                "\r\n" + // This is the extra CR+LF pair to signify the start of a body
                json + "\n");
#ifdef _DEBUG
  Serial.println("Request sent");
#endif
  while (client->connected()) {
    String line = client->readStringUntil('\n');
    if (line == "\r") {
#ifdef _DEBUG
      Serial.println("Headers received");
#endif
      break;
    }
  }

  String line = client->readStringUntil('\n');
#ifdef _DEBUG
  Serial.println("Lenth was:");
  Serial.println("==========");
  Serial.println(line);
  Serial.println("==========");
#endif

  if (isDigit(line[0]) || isDigit(line[1])) {

    String jsonrsp = client->readStringUntil('\n');
#ifdef _DEBUG
    Serial.println("Data was:");
    Serial.println("==========");
    Serial.println(jsonrsp);
    Serial.println("==========");
#endif
    char buff[jsonrsp.length()];
    jsonrsp.toCharArray(buff, jsonrsp.length());

    StaticJsonBuffer<200> jsonBufferrsp;

    JsonObject& rootresp = jsonBufferrsp.parseObject(buff);

    // Test if parsing succeeds.
    if (!rootresp.success()) {
#ifdef _DEBUG
      Serial.println("parseObject() failed");
#endif
      return;
    }


    if (path == "/api/validateDevice" ) {          // Check Device Validate Status

      qstatus = (const char*)rootresp["status"];

      if (qstatus == "success" ) {

        qrandom1 = (const char*)rootresp["random1"];
        qrandom2 = (const char*)rootresp["random2"];
        byte len = qrandom2.length();
        if (len >= 16)
          writeStringToEEPROM(350, qrandom2);

        qhbcount = (const char*)rootresp["hbcount"];
        ehbcount = qhbcount.toInt();

        qspo2 = (const char*)rootresp["spo2"];
        espo2 = qspo2.toInt();

        qtemp = (const char*)rootresp["temp"];
        etemp = qtemp.toFloat();

        qdevtype = (const char*)rootresp["devtype"];

        if (qdevtype == "KEYBOARD" || qdevtype == "OTHER")
        {
          DevType = false;
        }
        else
        {
          DevType = true;
        }

#ifdef _DEBUG                                   // Print values.
        Serial.println("Got Status Success ");
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
        Serial.print("qrandom2 = ");
        Serial.println(qrandom2);
        Serial.print("qhbcount = ");
        Serial.println(qhbcount);
        Serial.print("qspo2 = ");
        Serial.println(qspo2);
        Serial.print("qtemp = ");
        Serial.println(qtemp);
        Serial.print("qDevType = ");
        Serial.println(qdevtype);
#endif
        DevVldSt = true;
        ok_gled_buz_2();
        delay(2000);
      }
      else {
        const char* qrandom1 = rootresp["random1"];
        const char* qrandom2 = rootresp["random2"];
        const char* qreason = rootresp["reason"];

#ifdef _DEBUG
        Serial.println();
        Serial.println(qstatus);
        Serial.println(qrandom1);
        Serial.println(qrandom2);
        Serial.println(qreason);
#endif
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Contact Support");
        lcd.setCursor(0, 1);
        lcd.print("Error Code:");
        lcd.print(qreason);
        delay(1000);
        err_rled_buz_3();
        delay(1000);
        DevVldSt = false;
      }
    }

    qstatus = (const char*)rootresp["status"];
#ifdef _DEBUG
    Serial.println();
    Serial.println(qstatus);
#endif

    if (path == "/api/updateDevStatus" ) {                  // Check Device Update Status

      qstatus = (const char*)rootresp["status"];
#ifdef _DEBUG
      Serial.println();
      Serial.println(qstatus);
#endif
      if (qstatus == "success") {

        qrandom1 = (const char*)rootresp["random1"];
        qrandom2 = (const char*)rootresp["random2"];

#ifdef _DEBUG                                   // Print values.
        Serial.println("Got Status Success ");
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
        Serial.print("qrandom2 = ");
        Serial.println(qrandom2);
#endif
        DevUpSt = true;
        ok_gled_buz_2();
      }
      else {
        const char* qrandom1 = rootresp["random1"];
        const char* qrandom2 = rootresp["random2"];
        const char* qreason = rootresp["reason"];
#ifdef _DEBUG
        Serial.println();
        Serial.println(qstatus);
        Serial.println(qrandom1);
        Serial.println(qrandom2);
        Serial.println(qreason);
#endif
        DevUpSt = false;
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Contact Support");
        lcd.setCursor(0, 1);
        lcd.print("Error Code:");
        lcd.print(qreason);
        delay(1000);
        err_rled_buz_3();
        delay(1000);
      }
    }


    if (path == "/api/vRFID" ) {                  // Check RFID Card Validation Status

      qstatus = (const char*)rootresp["status"];

      if (qstatus == "success" ) {

        qrandom1 = (const char*)rootresp["random1"];
        qrandom2 = (const char*)rootresp["random2"];
        username = (const char*)rootresp["username"];
        userid = (const char*)rootresp["identifier"];
        flagst = (const char*)rootresp["flagstatus"];

#ifdef _DEBUG                                   // Print values.
        Serial.println("Got Status Success ");
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
        Serial.print("qrandom2 = ");
        Serial.println(qrandom2);
        Serial.print("username = ");
        Serial.println(username);
        Serial.print("userid = ");
        Serial.println(userid);
        Serial.print("flagst = ");
        Serial.println(flagst);
#endif

        if (sizeof(username) > 6 && flagst == "0") {
          valid_user = true;
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Welcome");
          lcd.setCursor(0, 1);
          lcd.print(username);
          delay(2000);
          ok_gled_buz_2();
        }
        else if (sizeof(username) > 6 && flagst == "1") {
          valid_user = true;
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Last Sacning is ");
          lcd.setCursor(0, 1);
          lcd.print("    Abnormal    ");
          err_rled_buz_3();
          delay(2000);
        }

        //        else if (sizeof(username) > 6 && Uflagst == "1") {
        //          valid_user = false;
        //          lcd.clear();
        //          lcd.setCursor(0, 0);
        //          lcd.print("your are not   ");
        //          lcd.setCursor(0, 1);
        //          lcd.print("Active user    ");
        //          err_rled_buz_3();
        //          delay(2000);
        //        }

      }
      else {
        const char* qrandom1 = rootresp["random1"];
        const char* qrandom2 = rootresp["random2"];
        const char* qreason = rootresp["reason"];

#ifdef _DEBUG
        Serial.println();
        Serial.println(qstatus);
        Serial.println(qrandom1);
        Serial.println(qrandom2);
        Serial.println(qreason);
#endif
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Contact Support");
        lcd.setCursor(0, 1);
        lcd.print("Error Code:");
        lcd.print(qreason);
        delay(1000);
        err_rled_buz_3();
        delay(1000);
        valid_user = false;
      }
    }



    if (path == "/api/saveDeviceData" ) {                  // Send Data To Server

      qstatus = (const char*)rootresp["status"];

      if (qstatus == "success" ) {

        qrandom1 = (const char*)rootresp["random1"];
        qrandom2 = (const char*)rootresp["random2"];

#ifdef _DEBUG                                   // Print values.
        Serial.println("Got Status Success ");
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
        Serial.print("qrandom2 = ");
        Serial.println(qrandom2);
#endif
        dataresp = true;
      }
      else {
        const char* qrandom1 = rootresp["random1"];
        const char* qrandom2 = rootresp["random2"];
        const char* qreason = rootresp["reason"];

#ifdef _DEBUG
        Serial.println();
        Serial.println(qstatus);
        Serial.println(qrandom1);
        Serial.println(qrandom2);
        Serial.println(qreason);
#endif
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Contact Support");
        lcd.setCursor(0, 1);
        lcd.print("Error Code:");
        lcd.print(qreason);
        delay(1000);
        err_rled_buz_3();
        delay(1000);
        dataresp = false;
      }
    }


  }
#ifdef _DEBUG
  Serial.println("Closing connection");
  Serial.printf("\n-------\n");
#endif
  client->stop();
}

void setup() {

  // initialize digital pin LED, RELAY and BUZZER as an output.
  pinMode(GLED, OUTPUT);
  pinMode(RLED, OUTPUT);


#ifdef _DEBUG
  Serial.begin(115200);
#else
  //GPIO 1 (TX) swap the pin to a GPIO.
  pinMode(1, FUNCTION_3);
  //GPIO 3 (RX) swap the pin to a GPIO.
  pinMode(3, FUNCTION_3);
  pinMode(RLY, OUTPUT);     //comment for debugging
  pinMode(BUZ, OUTPUT);
  digitalWrite(RLED, LOW);
  digitalWrite(GLED, LOW);
  digitalWrite(RLY, LOW);
  digitalWrite(BUZ, LOW);
#endif

  EEPROM.begin(512); //Initialasing EEPROM
  delay(10);

  lcd.begin();
  lcd.backlight();

  lcd.setCursor(0, 0);
  lcd.print("  KO-AAHAM TECH ");
  lcd.setCursor(0, 1);
  lcd.print("Initializing....");
  delay(3000);
#ifdef _DEBUG
  Serial.println("Disconnecting current wifi connection");
#endif
  WiFi.disconnect();

  deviceid = Read_Device_Id();  //read device id from eeprom
  deviceid.trim();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Device ID :");
  lcd.setCursor(0, 1);
  lcd.print(deviceid);

#ifdef _DEBUG
  Serial.println();
  Serial.print("Device ID : ");
  Serial.println(deviceid);
#endif
  delay(100);


  //---------------------------------------- Read eeprom for ssid and pass
#ifdef _DEBUG
  Serial.println();
  Serial.println("Startup");
  Serial.println("Reading EEPROM ssid");
#endif

  String esid;
  for (int i = 0; i < 32; ++i)
  {
    esid += char(EEPROM.read(i));
  }
  esid.trim();
#ifdef _DEBUG
  Serial.println();
  Serial.println("Reading EEPROM ");
  Serial.print("SSID: ");
  Serial.println(esid);

#endif

  String epass = "";
  for (int i = 32; i < 96; ++i)
  {
    epass += char(EEPROM.read(i));
  }
  epass.trim();
#ifdef _DEBUG
  Serial.print("PASS: ");
  Serial.println(epass);
#endif


  for (int i = 97; i < 196 ; ++i)
  {
    esrvname += char(EEPROM.read(i));
  }
  esrvname.trim();
#ifdef _DEBUG
  Serial.print("SERVER_NAME: ");
  Serial.println(esrvname);
#endif


  for (int i = 197; i < 202; ++i)
  {
    epcol += char(EEPROM.read(i));
  }
  epcol.trim();
#ifdef _DEBUG
  Serial.print("PROTOCL: ");
  Serial.println(epcol);
#endif

  //WiFi.mode(WIFI_STA);
  //WiFi.begin("Jagtap", "sagar015");
  //WiFi.begin("JioFi3_5EC6DD", "keecdrv5xt");

  WiFi.begin(esid, epass);
  if (testWifi())
  {

#ifdef _DEBUG
    Serial.println("Succesfully Connected!!!");
#endif

    lcd.setCursor(0, 0);
    lcd.print("  Succesfully   ");
    lcd.setCursor(0, 1);
    lcd.print("  Connected!!!  ");
    delay(1000);
    //return;
  }
  else
  {
#ifdef _DEBUG
    Serial.println("Turning the HotSpot On");
#endif

    lcd.setCursor(0, 0);
    lcd.print("   HotSpot ON   ");
    lcd.setCursor(0, 1);
    lcd.print("Enter password  ");
    delay(1000);
    launchWeb();
    setupAP();// Setup HotSpot
  }

#ifdef _DEBUG
  Serial.println();
  Serial.println("Waiting.");
#endif

  lcd.clear();
  uint8_t time_out = 0;
  while ((WiFi.status() != WL_CONNECTED))
  {
    time_out++;
#ifdef _DEBUG
    Serial.print(".");
#endif
    lcd.setCursor(0, 0);
    lcd.print("   HotSpot ON   ");
    lcd.setCursor(3, 1);
    lcd.print(WiFi.softAPIP());
    delay(1000);
    server.handleClient();
    if (time_out >= 180) // 3 minute time out
      ESP.reset();
  }

  lcd.setCursor(0, 0);
  lcd.print("WIFI_CONNECTED");
  lcd.setCursor(0, 1);
  lcd.print(WiFi.localIP());
  delay(2000);
#ifdef _DEBUG
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
#endif

  //#ifdef _http
  //
  //#else
  if (epcol == "http")
  {
    Proto = true;
#ifdef _DEBUG
    Serial.print("PROTOCOL = ");
    Serial.println(epcol );
#endif
  }
  else
  {
    Proto = false;
#ifdef _DEBUG
    Serial.print("PROTOCOL = ");
    Serial.println(epcol );
#endif
    SPIFFS.begin();
    setClock(); // Required for X.509 validation

    int numCerts = certStore.initCertStore(SPIFFS, PSTR("/certs.idx"), PSTR("/certs.ar"));
#ifdef _DEBUG
    Serial.printf("Number of CA certs read: %d\n", numCerts);
#endif
    if (numCerts == 0) {
#ifdef _DEBUG
      Serial.printf("No certs found. Did you run certs-from-mozilla.py and upload the LittleFS directory before running?\n");
#endif
      return; // Can't connect to anything w/o certs!
    }
  }
  //#endif


  digitalWrite(RLED, HIGH);
  digitalWrite(GLED, HIGH);
  //digitalWrite(RLY, HIGH);
  digitalWrite(BUZ, HIGH);
  delay(500);

  digitalWrite(RLED, LOW);
  digitalWrite(GLED, LOW);
  digitalWrite(RLY, LOW);
  digitalWrite(BUZ, LOW);
  delay(500);

  digitalWrite(RLED, HIGH);
  digitalWrite(GLED, HIGH);
  //digitalWrite(RLY, HIGH);
  digitalWrite(BUZ, HIGH);
  delay(500);

  digitalWrite(RLED, LOW);
  digitalWrite(GLED, LOW);
  //digitalWrite(RLY, LOW);
  digitalWrite(BUZ, LOW);
  delay(500);

  String macid = "483FDA7D" ; //BC70"; // WiFi.macAddress();
  char buf[12];
  deviceid.toCharArray(buf, sizeof(deviceid));
  unsigned char* hash = MD5::make_hash(buf);
  random1 = MD5::make_digest(hash, 16);
  random2 = readStringFromEEPROM(350); //xlrEIN3NO7q8euAL

#ifdef _DEBUG
  Serial.println("");
  Serial.print("MAC Address : ");
  Serial.println(macid);
  Serial.println("");
  Serial.print("MD5 Hash : ");
  Serial.println(random1);
  Serial.println("");
  Serial.print("Tocken : ");
  Serial.println(random2);
#endif
UP:

  if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status

    if (Proto)
    {
      //#ifdef _http                            /// FOR HTTP
      WiFiClient client;
      HTTPClient http;
      // Your Domain name with URL path or IP address with path
      char DevValidatin[100];
      String vld = "/api/validateDevice";
      sprintf(DevValidatin, "%s://%s%s", epcol.c_str(), esrvname.c_str(), vld.c_str());
#ifdef _DEBUG
      Serial.println(DevValidatin);
#endif

      http.begin(client, DevValidatin);
      // If you need an HTTP request with a content type: application/json, use the following:
      http.addHeader("Content-Type", "application/json", "User-Agent/PermitMe");
#ifdef _DEBUG
      Serial.println(" {\"deviceid\":\"" + deviceid + "\",\"macid\":\"" + macid + "\",\"random1\":\"" + random1 + "\",\"random2\":\"" + random2 + "\"}");
#endif
      int httpResponseCode = http.POST("{\"deviceid\":\"" + deviceid + "\",\"macid\":\"" + macid + "\",\"random1\":\"" + random1 + "\",\"random2\":\"" + random2 + "\"}");
      String payload = http.getString();
#ifdef _DEBUG
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      Serial.println(payload);    //Print request response
#endif
      http.end();                 // Free resources

      qstatus = getValue(payload, '"', 3);
#ifdef _DEBUG
      Serial.print("qstatus = ");
      Serial.println(qstatus);
#endif
      if (qstatus == "success") {
#ifdef _DEBUG
        Serial.println("Got Status Success ");
#endif
        String qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
#endif

        qrandom2 = getValue(payload, '"', 11);
        byte len = qrandom2.length();
        if (len >= 16)
          writeStringToEEPROM(350, qrandom2);
#ifdef _DEBUG
        Serial.print("qrandom2 = ");
        Serial.println(qrandom2);
#endif
        qhbcount = getValue(payload, '"', 15);
        ehbcount = qhbcount.toInt();
#ifdef _DEBUG
        Serial.print("qhbcount = ");
        Serial.println(qhbcount);
#endif

        qspo2 = getValue(payload, '"', 19);
        espo2 = qspo2.toInt();
#ifdef _DEBUG
        Serial.print("qspo2 = ");
        Serial.println(qspo2);
#endif
        qtemp = getValue(payload, '"', 23);
        etemp = qtemp.toFloat();
#ifdef _DEBUG
        Serial.print("qtemp = ");
        Serial.println(qtemp);
#endif

        qdevtype = getValue(payload, '"', 27);
#ifdef _DEBUG
        Serial.print("qDevType = ");
        Serial.println(qdevtype);
#endif
        if (qdevtype == "KEYBOARD" || qdevtype == "OTHER")
        {
          DevType = false;
        }
        else
        {
          DevType = true;
        }

        DevVldSt = true;
        delay(2000);
        ok_gled_buz_2();
      }
      else {
        qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
        Serial.print("qrandom1 = ");
        Serial.println(qrandom1);
#endif
        String ErrorCode = getValue(payload, '"', 11);
#ifdef _DEBUG
        Serial.print("Error Code = ");
        Serial.println(ErrorCode);
#endif
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Contact Support");
        lcd.setCursor(0, 1);
        lcd.print("Error Code:");
        lcd.print(ErrorCode);
        delay(1000);
        err_rled_buz_3();
        delay(1000);
        DevVldSt = false;
        goto UP;
      }
    }
    //#else                                  /// for HTTPS  ///////////
    else {

      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("DEVICE VALIDATE");
      delay(1000);

      StaticJsonBuffer<200> jsonBuffer1;
      JsonObject& root = jsonBuffer1.createObject();

      root["deviceid"] = deviceid;
      root["macid"] = macid;
      root["random1"] = random1;
      root["random2"] = random2;

      Serial.println();
      String dataStr = "";
      root.printTo(dataStr);

      Serial.println(dataStr);

      BearSSL::WiFiClientSecure *bear = new BearSSL::WiFiClientSecure();

      // Integrate the cert store with this connection
      bear->setCertStore(&certStore);
#ifdef _DEBUG
      Serial.printf("Attempting to fetch https://");
      Serial.printf(esrvname.c_str());
      Serial.printf("/api/validateDevice\n");
#endif
      String API_Path = "/api/validateDevice";
      fetchURL_POST(bear, esrvname.c_str() , 443, API_Path, dataStr);
      delete bear;
      lcd.setCursor(0, 1);
      lcd.print("      DONE!     ");

      if (DevVldSt == false) {

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("DEVICE VALIDATE");
        lcd.setCursor(0, 1);
        lcd.print("      FAIL!     ");
        delay(2000);

        goto UP;
      }
      //#endif
    }
  }

  else {
#ifdef _DEBUG
    Serial.println("WiFi Disconnected");
#endif
    dataresp = false;
    err_data_rgled_buz_4();
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Disconnect");
    delay(1000);
    uint8_t time_out = 0;
    while ((WiFi.status() != WL_CONNECTED))
    {
      time_out++;
#ifdef _DEBUG
      Serial.print(".");
#endif
      lcd.setCursor(0, 0);
      lcd.print("WIFI_CONNECTING");
      delay(1000);
      server.handleClient();
      if (time_out >= 60) {// 1 minute time out
        err_data_rgled_buz_4();
        break;
      }
    }
  }
  lcd.clear();

  if (DevVldSt)
  {
    if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status

      if (Proto)
      {
        //#ifdef _http                            /// FOR HTTP
        WiFiClient client;
        HTTPClient http;
        // Your Domain name with URL path or IP address with path
        char DevUpdate[100];
        String vld = "/api/updateDevStatus";
        sprintf(DevUpdate, "%s://%s%s", epcol.c_str(), esrvname.c_str(), vld.c_str());
#ifdef _DEBUG
        Serial.println(DevUpdate);
#endif

        http.begin(client, DevUpdate);
        // If you need an HTTP request with a content type: application/json, use the following:
        http.addHeader("Content-Type", "application/json", "User-Agent/PermitMe");
#ifdef _DEBUG
        Serial.println(" {\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"status\":\"" + qstatus + "\"}");
#endif
        int httpResponseCode = http.POST("{\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"status\":\"" + qstatus + "\"}");
        String payload = http.getString();
#ifdef _DEBUG
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        Serial.println(payload);    //Print request response
#endif
        http.end();                 // Free resources

        qstatus = getValue(payload, '"', 3);
#ifdef _DEBUG
        Serial.print("qstatus = ");
        Serial.println(qstatus);
#endif
        if (qstatus == "success")
        {
          qrandom1 = getValue(payload, '"', 7);
          qrandom2 = getValue(payload, '"', 11);


#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
          Serial.print("qrandom2 = ");
          Serial.println(qrandom2);
#endif
        }
        else {
          qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          String ErrorCode = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("Error Code = ");
          Serial.println(ErrorCode);
#endif
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Contact Support");
          lcd.setCursor(0, 1);
          lcd.print("Error Code:");
          lcd.print(ErrorCode);
          delay(1000);
          err_rled_buz_3();
          delay(1000);
          goto UP;
        }
      }
      //#else                                  /// for HTTPS  ///////////
      else {

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("DEVICE UPDATE   ");
        delay(1000);
        StaticJsonBuffer<200> jsonBuffer1;
        JsonObject& root = jsonBuffer1.createObject();

        root["random1"] = qrandom1;
        root["random2"] = qrandom2;
        root["status"] = qstatus;

        Serial.println();
        String dataStr = "";
        root.printTo(dataStr);

        Serial.println(dataStr);

        BearSSL::WiFiClientSecure *bear = new BearSSL::WiFiClientSecure();

        // Integrate the cert store with this connection
        bear->setCertStore(&certStore);
#ifdef _DEBUG
        Serial.printf("Attempting to fetch https://");
        Serial.printf(esrvname.c_str());
        Serial.printf("/api/updateDevStatus\n");
#endif
        String API_Path = "/api/updateDevStatus";
        fetchURL_POST(bear, esrvname.c_str(), 443, API_Path, dataStr);
        delete bear;


//        if (DevUpSt == false) {
//        lcd.setCursor(0, 1);
//        lcd.print("      FAIL!     ");
//        delay(2000);
//        goto UP;
//        }
//        else
//        {
//          lcd.setCursor(0, 1);
//          lcd.print("     DONE!    ");
//        }
//        //#endif
      }

    }
    else {
#ifdef _DEBUG
      Serial.println("WiFi Disconnected");
#endif
      err_data_rgled_buz_4();
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WiFi Disconnect");
      delay(1000);
      uint8_t time_out = 0;
      while ((WiFi.status() != WL_CONNECTED))
      {
        time_out++;
#ifdef _DEBUG
        Serial.print(".");
#endif
        lcd.setCursor(0, 0);
        lcd.print("WIFI_CONNECTING");
        delay(1000);
        server.handleClient();
        if (time_out >= 60) {// 1 minute time out
          err_data_rgled_buz_4();
          break;
        }
      }
    }
    lcd.clear();
  }

#ifdef _KBRD    //KEYBORD / OTHER
  //if (!DevType) {
  keyboard.begin();
  delay(10);
  lcd.clear();
  lcd.setCursor(0, 1);
  lcd.print("Enter Mobile NO ");
  // }
#else         //RFID
  //else {
  SPI.begin();          // Initiate  SPI bus
  delay(10);
  mfrc522.PCD_Init();   // Initiate MFRC522
  delay(10);
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(" Scan Your Card ");
  lcd.setCursor(0, 1);
  //}
#endif
}



void ok_gled_buz_2() {
  digitalWrite(GLED, HIGH);
  digitalWrite(BUZ, HIGH);
  delay(250);
  digitalWrite(GLED, LOW);
  digitalWrite(BUZ, LOW);
  delay(250);
}

void err_rled_buz_3() {
  for (int i = 0 ; i < 3 ; i++)
  {
    digitalWrite(RLED, HIGH);
    digitalWrite(BUZ, HIGH);
    delay(500);
    digitalWrite(RLED, LOW);
    digitalWrite(BUZ, LOW);
    delay(500);

  }
}

void err_data_rgled_buz_4() {
  for (int i = 0 ; i < 4 ; i++)
  {
    digitalWrite(GLED, HIGH);
    digitalWrite(RLED, HIGH);
    digitalWrite(BUZ, HIGH);
    delay(500);
    digitalWrite(GLED, LOW);
    digitalWrite(RLED, LOW);
    digitalWrite(BUZ, LOW);
    delay(500);

  }
}

void loop() {
up:

  const unsigned long fiveMinutes = 5 * 60 * 1000UL;
  static unsigned long lastSampleTime = 0 - fiveMinutes;  // initialize such that a reading is due the first time through loop()

  unsigned long now = millis();
  if (now - lastSampleTime >= fiveMinutes)
  {
    lastSampleTime += fiveMinutes;

    if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status

      if (Proto)
      {
        //#ifdef _http                            /// FOR HTTP
        WiFiClient client;
        HTTPClient http;
        // Your Domain name with URL path or IP address with path
        char DevUpdate[100];
        String vld = "/api/updateDevStatus";
        qstatus = "update";
        sprintf(DevUpdate, "%s://%s%s", epcol.c_str(), esrvname.c_str(), vld.c_str());
#ifdef _DEBUG
        Serial.println(DevUpdate);
#endif

        http.begin(client, DevUpdate);
        // If you need an HTTP request with a content type: application/json, use the following:
        http.addHeader("Content-Type", "application/json", "User-Agent/PermitMe");
#ifdef _DEBUG
        Serial.println(" {\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"status\":\"" + qstatus + "\"}");
#endif
        int httpResponseCode = http.POST("{\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"status\":\"" + qstatus + "\"}");
        String payload = http.getString();
#ifdef _DEBUG
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        Serial.println(payload);    //Print request response
#endif
        http.end();                 // Free resources

        qstatus = getValue(payload, '"', 3);
#ifdef _DEBUG
        Serial.print("qstatus = ");
        Serial.println(qstatus);
#endif
        if (qstatus == "success")
        {
          qrandom1 = getValue(payload, '"', 7);
          qrandom2 = getValue(payload, '"', 11);


#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
          Serial.print("qrandom2 = ");
          Serial.println(qrandom2);
#endif
        }
        else {
          qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          String ErrorCode = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("Error Code = ");
          Serial.println(ErrorCode);
#endif
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Sontact Support");
          lcd.setCursor(0, 1);
          lcd.print("Error Code:");
          lcd.print(ErrorCode);
          delay(1000);
          err_rled_buz_3();
          delay(1000);
          goto up;
        }
      }
      //#else                                  /// for HTTPS  ///////////
      else {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("DEVICE UPDATE   ");
        delay(1000);
        StaticJsonBuffer<200> jsonBuffer1;
        JsonObject& root = jsonBuffer1.createObject();

        root["random1"] = qrandom1;
        root["random2"] = qrandom2;
        root["status"] = qstatus;

        Serial.println();
        String dataStr = "";
        root.printTo(dataStr);

        Serial.println(dataStr);

        BearSSL::WiFiClientSecure *bear = new BearSSL::WiFiClientSecure();

        // Integrate the cert store with this connection
        bear->setCertStore(&certStore);
#ifdef _DEBUG
        Serial.printf("Attempting to fetch https://");
        Serial.printf(esrvname.c_str());
        Serial.printf("/api/validateDevice\n");
#endif
        String API_Path = "/api/updateDevStatus";
        fetchURL_POST(bear, esrvname.c_str(), 443, API_Path, dataStr);
        delete bear;

//        if (DevUpSt == false) {
//        lcd.setCursor(0, 1);
//        lcd.print("      FAIL!     ");
//        delay(2000);
//        goto UP;
//        }
//        else
//        {
//          lcd.setCursor(0, 1);
//          lcd.print("      STATUS!   ");
//        }
//        //#endif
      }

    }
    else {
#ifdef _DEBUG
      Serial.println("WiFi Disconnected");
#endif
      err_data_rgled_buz_4();
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WiFi Disconnect");
      delay(1000);
      uint8_t time_out = 0;
      while ((WiFi.status() != WL_CONNECTED))
      {
        time_out++;
#ifdef _DEBUG
        Serial.print(".");
#endif
        lcd.setCursor(0, 0);
        lcd.print("WIFI_CONNECTING");
        delay(1000);
        server.handleClient();
        if (time_out >= 60) {// 1 minute time out
          err_data_rgled_buz_4();
          break;
        }
      }
    }
    lcd.clear();
  }

#ifdef _KBRD
  if (!DevType)
  {
    if (!keyboard.available()) {
      lcd.setCursor(0, 0);
      lcd.print(MobNo);
      lcd.setCursor(0, 1);
      lcd.print("Enter Mobile NO ");
    }

    if (keyboard.available()) {
      // read the next key
      c = keyboard.read();

      // check for some of the special keys
      if (c == ENTER) {
       uint8_t i;
       
        for(i=0;i<11;i++)
        {
        if(MobNo[i]=='\0')
        break;
        }

        if (i < 9)
        {
          memset(MobNo, 0, sizeof(MobNo));
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("  Mobile Number ");
          lcd.setCursor(0, 1);
          lcd.print("  is Not Valid  ");
          x = 0; y = 0;
          lcd.setCursor(x, y);
          delay(2000);
          lcd.clear();
          goto up;
        }
        else
        {
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("Saved Mobile No ");
          lcd.setCursor(0, 1);
          lcd.print(MobNo);
#ifdef _DEBUG
          Serial.println();
          Serial.println(MobNo);
#endif
          userid = MobNo;
        }
        delay(2000);

        while (1)
        {
          if (keyboard.available()) {
            // read the next key
            c = keyboard.read();

            // check for some of the special keys
            if (c == ENTER) {
              valid_user = true;
              lcd.clear();
              break;
            }

          }
          time_out_kb++;
          delay(1000);
          if (time_out_kb >= 20) { // 1 minute time out
            err_rled_buz_3();
            x = 0; y = 0;
            lcd.clear();
            lcd.setCursor(x, y);
            goto up;
          }
        }
      }

      else if (c == ESC) {
#ifdef _DEBUG
        Serial.println(F("\n[ESC]\n"));
#endif
        memset(MobNo, 0, sizeof(MobNo));
        lcd.clear();
        lcd.setCursor(0, 1);
        lcd.print("Enter Mobile NO ");
        x = 0; y = 0;
        lcd.setCursor(x, y);
      }

      else {
        // otherwise, just print all normal characters
        lcdprintChar(c);
      }
    }
  }
#else
  else
  {
    lcd.setCursor(0, 0);
    lcd.print(" Scan Your Card ");

    if (tag_found == true)
    {
      if (millis() - previous > frequency )
      {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print(" Scan Your Card ");
        tag_found = false;
      }
    }
    // Look for new cards
    if ( ! mfrc522.PICC_IsNewCardPresent())
    {
      return;
    }
    // Select one of the cards
    if ( ! mfrc522.PICC_ReadCardSerial())
    {
      return;
    }
    previous = millis();
    ok_gled_buz_2();
    tag_found = true;
    //Show UID on Serial monitor
#ifdef _DEBUG
    Serial.println();
    Serial.print(" UID tag :");
#endif
    String content = "";
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("validating user ");
    lcd.setCursor(0, 1);
    lcd.print("please wait.... ");

    for (byte i = 0; i < mfrc522.uid.size; i++)
    {
#ifdef _DEBUG
      Serial.print(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
      Serial.print(mfrc522.uid.uidByte[i], HEX);
#endif
      content.concat(String(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " "));
      content.concat(String(mfrc522.uid.uidByte[i], HEX));
    }
    content.toUpperCase();
    content.trim();
#ifdef _DEBUG
    Serial.println();
#endif


    if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status
      if (Proto)
      {
        //#ifdef _http                            /// FOR HTTP
        WiFiClient client;
        HTTPClient http;
        // Your Domain name with URL path or IP address with path
        char DevVerCard[100];
        String vld = "/api/vRFID";
        sprintf(DevVerCard, "%s://%s%s", epcol.c_str(), esrvname.c_str(), vld.c_str());
#ifdef _DEBUG
        Serial.println(DevVerCard);
#endif
        // Your Domain name with URL path or IP address with path
        http.begin(client, DevVerCard);
        // If you need an HTTP request with a content type: application/json, use the following:
        http.addHeader("Content-Type", "application/json", "User-Agent/PermitMe");
#ifdef _DEBUG
        Serial.println("{\"deviceid\":\"" + deviceid + "\",\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"cardid\":\"" + content + "\"}");
#endif
        int httpResponseCode = http.POST("{\"deviceid\":\"" + deviceid + "\",\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"cardid\":\"" + content + "\"}");
        String payload = http.getString();
#ifdef _DEBUG
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        Serial.println(payload);    //Print request response
#endif
        http.end();                 // Free resources


        qstatus = getValue(payload, '"', 3);
#ifdef _DEBUG
        Serial.print("qstatus = ");
        Serial.println(qstatus);
#endif

        if (qstatus == "success")
        {
          String qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          qrandom2 = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("qrandom2 = ");
          Serial.println(qrandom2);
#endif
          username = getValue(payload, '"', 15);
#ifdef _DEBUG
          Serial.print("username = ");
          Serial.println(username);
#endif
          userid = getValue(payload, '"', 19);
#ifdef _DEBUG
          Serial.print("userid = ");
          Serial.println(userid);
#endif
          flagst = getValue(payload, '"', 23);
#ifdef _DEBUG
          Serial.print("flag = ");
          Serial.println(flagst);
#endif

          if (sizeof(username) > 6 && flagst == "0") {
            valid_user = true;
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Welcome");
            lcd.setCursor(0, 1);
            lcd.print(username);
            delay(2000);
            ok_gled_buz_2();
          }
          else if (sizeof(username) > 6 && flagst == "1") {
            valid_user = true;
            lcd.clear();
            lcd.setCursor(0, 0);
            lcd.print("Last Sacning is ");
            lcd.setCursor(0, 1);
            lcd.print("    Abnormal    ");
            err_rled_buz_3();
            delay(2000);
          }

          //          else if (sizeof(username) > 6 && Uflagst == "1") {
          //            valid_user = false;
          //            lcd.clear();
          //            lcd.setCursor(0, 0);
          //            lcd.print("your are not   ");
          //            lcd.setCursor(0, 1);
          //            lcd.print("Active user    ");
          //            err_rled_buz_3();
          //            delay(2000);
          //          }
        }
        else {
          qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          String ErrorCode = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("Error Code = ");
          Serial.println(ErrorCode);
#endif
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("contact support");
          lcd.setCursor(0, 1);
          lcd.print("Error Code:");
          lcd.print(ErrorCode);
          delay(1000);
          valid_user = false;
          err_rled_buz_3();
          delay(1000);
        }
      }
      //#else                                  /// for HTTPS  ///////////
      else {
        StaticJsonBuffer<200> jsonBuffer1;
        JsonObject& root = jsonBuffer1.createObject();

        root["deviceid"] = deviceid;
        root["random1"] = qrandom1;
        root["random2"] = qrandom2;
        root["cardid"] = content;

        Serial.println();
        String dataStr = "";
        root.printTo(dataStr);

        Serial.println(dataStr);

        BearSSL::WiFiClientSecure *bear = new BearSSL::WiFiClientSecure();

        // Integrate the cert store with this connection
        bear->setCertStore(&certStore);
#ifdef _DEBUG
        Serial.printf("Attempting to fetch https://");
        Serial.printf(esrvname.c_str());
        Serial.printf("/api/vRFID\n");
#endif
        String API_Path = "/api/vRFID";
        fetchURL_POST(bear, esrvname.c_str(), 443, API_Path, dataStr);
        delete bear;
        //#endif
      }
    }
    else {
#ifdef _DEBUG
      Serial.println("WiFi Disconnected");
#endif
      valid_user = false;
      err_data_rgled_buz_4();
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WiFi Disconnect");
      delay(1000);
      uint8_t time_out = 0;
      while ((WiFi.status() != WL_CONNECTED))
      {
        time_out++;
#ifdef _DEBUG
        Serial.print(".");
#endif
        lcd.setCursor(0, 0);
        lcd.print("WIFI_CONNECTING");
        delay(1000);
        server.handleClient();
        if (time_out >= 60) { // 1 minute time out
          err_data_rgled_buz_4();
          break;
        }
      }
    }
  }

#endif

  if (valid_user)
  {
tps:
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(" Stay closer to ");
    lcd.setCursor(0, 1);
    lcd.print("   Temp Sensor  ");
    uint8_t time_out = 0;
    while (1)
    {
      sensorValue = analogRead(analogInPin);
#ifdef _DEBUG
      Serial.print("sensor = ");
      Serial.println(sensorValue);
#endif
      time_out++;
      delay(1000);
      if (time_out >= 20) { // 1 minute time out
        err_rled_buz_3();
        valid_user = false;
        //memset(MobNo, 0, sizeof(MobNo));
        lcd.clear();
        lcd.setCursor(0, 0);
        goto up;
      }
      if (sensorValue > 100) {
        ok_gled_buz_2();
        break;
      }
    }

    MLX9614();
    delay(1000);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Temp Scan finish");
    lcd.setCursor(0, 1);
    lcd.print("Temp: ");
    lcd.print(average_temp);
    lcd.print("'F");
#ifdef _DEBUG
    Serial.print("object Temprature= ");
    Serial.print(average_temp);
    Serial.println("'F");
#endif
    String tempavg = String(average_temp);
    if (tempavg.toFloat() > etemp) {
#ifdef _DEBUG
      Serial.println("Temprature is > Set Max Value");
      Serial.println(etemp);
#endif
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Temprature HIGH ");
      lcd.setCursor(0, 1);
      lcd.print(tempavg); lcd.print("'F");
      err_rled_buz_3();
      delay(1000);
      TEMPStaus = false;
    }
    else if (tempavg.toInt() < 86) {
#ifdef _DEBUG
      Serial.println("Temprature is <86"); Serial.println("86'F");
      Serial.print(tempavg); Serial.println("'F");
#endif
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Temprature LOW ");
      //lcd.setCursor(0, 1);
      //lcd.print("Pls Scan again ");
      err_rled_buz_3();
      delay(1000);
      TEMPStaus = false;
      //goto tps;
    }
    else {
      ok_gled_buz_2();
#ifdef _DEBUG
      Serial.println("Temprature is Normal ");
#endif
      TEMPStaus = true;
      delay(500);
    }

    //  scan SPO2
    lcd.setCursor(0, 0);
    lcd.print("Place finger to ");
    lcd.setCursor(0, 1);
    lcd.print("measur HB & SPO2");
    delay(2000);
    String hb = ""; String spo2 = "";

    HB_SPO2_OUT();
    delay(1000);

    if (SPO2TOut) {
      err_rled_buz_3();
      valid_user = false;
      lcd.clear();
      lcd.setCursor(0, 0);
      goto up;
    }

#ifdef _DEBUG
    Serial.print( "BPM: " );
    Serial.print((uint8_t)average_hbcount );  //result.heartBPM
    Serial.print( " | " );

    Serial.print( "SaO2: " );
    Serial.print((uint8_t)average_spo2);  //result.SaO2
    Serial.println( "%" );
#endif
    lcd.clear();
    //    lcd.setCursor(0, 0);
    //    lcd.print("HB:");
    //    lcd.print((uint8_t)average_hbcount);
    //    lcd.print("Bpm");

    lcd.setCursor(0, 0);
    lcd.print("SPO2 : ");
    lcd.print((uint8_t)average_spo2);  //result.SaO2
    lcd.print("%");

    lcd.setCursor(0, 1);
    lcd.print("Temp : ");
    lcd.print(average_temp);
    lcd.print("'F");
    delay(3000);

    hb = String((uint8_t)average_hbcount);
    spo2 = String((uint8_t)average_spo2 );

    if (spo2.toInt() < espo2) {
#ifdef _DEBUG
      Serial.print("SOP2 is < Set Max Value = "); Serial.print(espo2); Serial.println("%");
      Serial.print(average_spo2); Serial.println("%");
#endif
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("SPO2 LEVEL LOW  ");
      lcd.setCursor(0, 1);
      lcd.print("SPO2 : ");
      lcd.print(spo2);
      lcd.print("%  ");
      err_rled_buz_3();
      delay(1000);
      SPO2Staus = false;
      //goto up;
    }
    else {
#ifdef _DEBUG
      Serial.println("SOP2 is Normal");
#endif
      ok_gled_buz_2();
      SPO2Staus = true;
      delay(500);
    }

#ifdef _DEBUG
    Serial.print( "BPM : " );
    Serial.print( hb);
    Serial.print( " | ");
    Serial.print( "SaO2 : " );
    Serial.print( spo2 );
    Serial.println( "%" );
#endif

    if (!TEMPStaus || !SPO2Staus) {
      uint8_t rescan_time_out = 0;
      while (1)
      {
        sensorValue = analogRead(analogInPin);

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("  For Re-Scan   ");
        delay(1000);

        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print(" Stay closer to ");

        lcd.setCursor(0, 1);
        lcd.print("   Temp Sensor  ");
        delay(1000);

        if (sensorValue > 100) {
          goto tps;
        }

        rescan_time_out++;
        if (rescan_time_out >= 5) { // 10 sec rescan time out
          err_rled_buz_3();
          flag = 1;
          break;
        }
      }
    }
    else {
      flag = 0;
    }


    // Now we can publish stuff!
    if (WiFi.status() == WL_CONNECTED) { //Check WiFi connection status

      if (Proto)
      {
        //#ifdef _http                            /// FOR HTTP
        WiFiClient client;
        HTTPClient http;
        // Your Domain name with URL path or IP address with path
        char DevData[100];
        String vld = "/api/saveDeviceData";
        sprintf(DevData, "%s://%s%s", epcol.c_str(), esrvname.c_str(), vld.c_str());
#ifdef _DEBUG
        Serial.println(DevData);
#endif
        // Your Domain name with URL path or IP address with path
        http.begin(client, DevData);
        // If you need an HTTP request with a content type: application/json, use the following: {"deviceid":"010","userid":"1","temp":"92.77","spo2":"89.84"}
        http.addHeader("Content-Type", "application/json", "User-Agent/PermitMe");

#ifdef _DEBUG
        Serial.println("{\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"deviceid\":\"" + deviceid + "\",\"identifier\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hbcount\":\"" + hb + "\",\"flagstatus\":\"" + flag + "\"}");
#endif
        int httpResponseCode = http.POST("{\"random1\":\"" + (String)random1 + "\",\"random2\":\"" + random2 + "\",\"deviceid\":\"" + deviceid + "\",\"identifier\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hbcount\":\"" + hb + "\",\"flagstatus\":\"" + flag + "\"}");

        String payload = http.getString();
#ifdef _DEBUG
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        Serial.println(payload);    //Print request response
#endif
        http.end();                 // Free resources

        qstatus = getValue(payload, '"', 3);
#ifdef _DEBUG
        Serial.print("qstatus = ");
        Serial.println(qstatus);
#endif

        if (qstatus == "success") {
          qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          qrandom2 = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("qrandom2 = ");
          Serial.println(qrandom2);
#endif
          dataresp = true;
          ok_gled_buz_2();
        }
        else {
          qrandom1 = getValue(payload, '"', 7);
#ifdef _DEBUG
          Serial.print("qrandom1 = ");
          Serial.println(qrandom1);
#endif
          String ErrorCode = getValue(payload, '"', 11);
#ifdef _DEBUG
          Serial.print("Error Code = ");
          Serial.println(ErrorCode);
#endif
          lcd.clear();
          lcd.setCursor(0, 0);
          lcd.print("    Got Error   ");
          lcd.setCursor(0, 1);
          lcd.print("Error Code:");
          lcd.print(ErrorCode);
          delay(1000);
          dataresp = false;
          err_rled_buz_3();
          delay(1000);
          lcd.clear();
        }
      }
      //#else                                  /// for HTTPS  ///////////
      else {
        StaticJsonBuffer<300> jsonBuffer1;
        JsonObject& root = jsonBuffer1.createObject();


        root["random1"] = qrandom1;
        root["random2"] = qrandom2;
        root["deviceid"] = deviceid;
        root["identifier"] = userid;
        root["temp"] = tempavg;
        root["spo2"] = spo2;
        root["hbcount"] = hb;
        root["flagstatus"] = flag;

        Serial.println();
        String dataStr = "";
        root.printTo(dataStr);

        Serial.println(dataStr);

        BearSSL::WiFiClientSecure *bear = new BearSSL::WiFiClientSecure();

        // Integrate the cert store with this connection
        bear->setCertStore(&certStore);
#ifdef _DEBUG
        Serial.printf("Attempting to fetch https://");
        Serial.printf(esrvname.c_str());
        Serial.printf("/api/saveDeviceData\n");
#endif
        String API_Path = "/api/saveDeviceData";
        fetchURL_POST(bear, esrvname.c_str(), 443, API_Path, dataStr);
        delete bear;
        //#endif
      }
    }
    else {
#ifdef _DEBUG
      Serial.println("WiFi Disconnected");
#endif
      dataresp = false;
      err_data_rgled_buz_4();
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("WiFi Disconnect");
      delay(1000);
      uint8_t time_out = 0;
      while ((WiFi.status() != WL_CONNECTED))
      {
        time_out++;
#ifdef _DEBUG
        Serial.print(".");
#endif
        lcd.setCursor(0, 0);
        lcd.print("WIFI_CONNECTING");
        delay(1000);
        server.handleClient();
        if (time_out >= 60) {// 1 minute time out
          err_data_rgled_buz_4();
          break;
        }
      }
    }
    lcd.clear();

    if (dataresp)
    {
      if (SPO2Staus && TEMPStaus) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("   DOOR OPEN    ");
        digitalWrite(RLY, HIGH);
#ifdef _DEBUG
        Serial.println("OPEN DOOR");
#endif
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("   Thank you!   ");
        lcd.setCursor(0, 1);
        lcd.print("Have a good day.");
        ok_gled_buz_2();
        delay(5000);
        digitalWrite(RLY, LOW);
      }
      else if (!TEMPStaus) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("   DOOR CLOSE    ");
        digitalWrite(RLY, LOW);
#ifdef _DEBUG
        Serial.println("close DOOR");
#endif
        delay(2000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("TEMP HIGH");
        lcd.setCursor(0, 1);
        lcd.print("Pls visit doctor");
        err_rled_buz_3();
        //digitalWrite(RLY, HIGH);
      }
      else if (!SPO2Staus) {
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("   DOOR CLOSE    ");
        digitalWrite(RLY, LOW);
#ifdef _DEBUG
        Serial.println("close DOOR");
#endif
        delay(2000);
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("SPO2 LOW");
        lcd.setCursor(0, 1);
        lcd.print("Pls visit doctor");
        err_rled_buz_3();
        //digitalWrite(RLY, HIGH);
      }
    }
    else
    {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Error");
      lcd.setCursor(0, 1);
      lcd.print("Try again");
      digitalWrite(RLY, LOW);
#ifdef _DEBUG
      Serial.println("close DOOR");
#endif
      err_data_rgled_buz_4();
      //digitalWrite(RLY, HIGH);
      lcd.setCursor(0, 0);
      delay(1000);
      lcd.clear();
    }

    valid_user = false;
    memset(MobNo, 0, sizeof(MobNo));
    x = 0; y = 0;
    lcd.setCursor(0, 0);
    lcd.clear();
  }
}
