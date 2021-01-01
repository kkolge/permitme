#include <Wire.h>
#include "MAX30102.h"
#include "Pulse.h"
#include "LiquidCrystal_I2C.h"
#include "MLX90614.h"

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ESP8266WebServer.h>
#include <WiFiClient.h>
#include <EEPROM.h>

#include "PS2Keyboard.h"

//#define _DEBUG

const int DataPin = D4;
const int IRQpin =  D3;
PS2Keyboard keyboard;

const int numRows = 2;
const int numCols = 16;

byte x, y;  // track lcd position
char c;
char MobNo[11] = " ";

//0x3F  or 0x27
LiquidCrystal_I2C lcd(0x27, 16, 2); // set the LCD address to 0x27 for a 16 chars and 2 line display

String deviceid;

MLX90614 mlx = MLX90614();
MAX30102 sensor;
Pulse pulseIR;
Pulse pulseRed;
MAFilter bpm;

//spo2_table is approximated as  -45.060*ratioAverage* ratioAverage + 30.354 *ratioAverage + 94.845 ;
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



//Your Domain name with URL path or IP address with path
//const char* server_valid_card = "http://scanapp.ko-aaham.com/vRFID";
//const char* server_device_data = "http://scanapp.ko-aaham.com/sDevData";

const char* server_device_data = "http://permitmemass.ko-aaham.com/sDevData";



//I2C Address
//LCD =0x27
//MLX90614=0x57
//MAX30100=0x5A

const int analogInPin = A0;
static const uint8_t RLED = D0;
static const uint8_t GLED = D8;
//static const uint8_t BUZ = D7;
//static const uint8_t RLY = D6;
static const uint8_t BUZ = D9;
static const uint8_t RLY = D10;

int sensorValue = 0;        // value read from the pot
//Establishing Local server at port 80 whenever required
ESP8266WebServer server(80);

const char* ssid = "Default_SSID";
const char* passphrase = "Default_Password";


String userid = "";
String username = "";
bool dataresp = false;


//Function Decalration
bool testWifi(void);
void launchWeb(void);
void setupAP(void);

void lcdprintChar(char t);

void err_rled_buz_3();
void ok_gled_buz_2();
void err_data_rgled_buz_4();


int i = 0;
int statusCode;
String st = "";
String content = "";


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
    Serial.println(" networks found");
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
      content += "</p><form method='get' action='setting'><label>SSID: </label><input name='ssid' length=32><input name='pass' length=64><input type='submit'></form>";
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
      String qsid = server.arg("ssid");
      String qpass = server.arg("pass");
      if (qsid.length() > 0 && qpass.length() > 0) {
#ifdef _DEBUG
        Serial.println("clearing eeprom");
#endif
        for (int i = 0; i < 96; ++i) {
          EEPROM.write(i, 0);
        }
#ifdef _DEBUG
        Serial.println(qsid);
        Serial.println("");
        Serial.println(qpass);
        Serial.println("");
        Serial.println("writing eeprom ssid:");
#endif
        for (int i = 0; i < qsid.length(); ++i)
        {
          EEPROM.write(i, qsid[i]);
#ifdef _DEBUG
          Serial.print("Wrote: ");
          Serial.println(qsid[i]);
#endif
        }
#ifdef _DEBUG
        Serial.println("writing eeprom pass:");
#endif
        for (int i = 0; i < qpass.length(); ++i)
        {
          EEPROM.write(32 + i, qpass[i]);
#ifdef _DEBUG
          Serial.print("Wrote: ");
          Serial.println(qpass[i]);
#endif
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
//                  total_spo2 = total_spo2 - readings_spo2[readIndex_spo2];
//                  readings_spo2[readIndex_spo2] = SPO2;
//                  // add the reading to the total:
//                  total_spo2 = total_spo2 + readings_spo2[readIndex_spo2];
//                  // advance to the next position in the array:
//                  readIndex_spo2 = readIndex_spo2 + 1;
//        
                  total_hbcount = total_hbcount - readings_hbcount[readIndex_hbcount];
                  readings_hbcount[readIndex_hbcount] = beatAvg;
                  // add the reading to the total:
                  total_hbcount = total_hbcount + readings_hbcount[readIndex_hbcount];
                  // advance to the next position in the array:
                  readIndex_hbcount = readIndex_hbcount + 1;
        
                  if (readIndex_hbcount >= numReadings) { //readIndex_spo2 >= numReadings && 
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
  //    average_spo2 = total_spo2 / numReadings;
      average_hbcount = total_hbcount / numReadings;
  // send it to the computer as ASCII digits
#ifdef _DEBUG
  Serial.print("average_spo2=");
  Serial.println(average_spo2);
  Serial.print("average_hbcount=");
  Serial.println(average_hbcount);
#endif
  delay(50);        // delay in between reads for stability
}


void MLX9614() {
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
  int8_t offset = 100;
  String rid = "";
  for (int8_t i = 0; i < 20; ++i)
  {
    if (EEPROM.read(offset + i) == '\0')
      break;
    rid += char(EEPROM.read(offset + i));
  }
  return (rid);
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

keyboard.begin(DataPin, IRQpin); //FOR KEYBORD INIT
//keyboard.begin(DataPin, IRQpin, PS2Keymap_US);
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
EEPROM.begin(512); //Initialasing EEPROM
delay(10);

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

#ifdef _DEBUG
Serial.println();
Serial.println("Startup");
Serial.println("Reading EEPROM ssid");
#endif
//---------------------------------------- Read eeprom for ssid and pass
String esid;
for (int i = 0; i < 32; ++i)
{
  esid += char(EEPROM.read(i));
}

#ifdef _DEBUG
Serial.println();
Serial.print("SSID: ");
Serial.println(esid);
Serial.println("Reading EEPROM pass");
#endif

String epass = "";
for (int i = 32; i < 96; ++i)
{
  epass += char(EEPROM.read(i));
}

#ifdef _DEBUG
Serial.print("PASS: ");
Serial.println(epass);
#endif

WiFi.begin(esid.c_str(), epass.c_str());
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
//  digital pin LED, RELAY and BUZZER.
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


lcd.clear();
//  lcd.setCursor(0, 1);
//  lcd.print("Enter mobile NO ");
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
    if (c == PS2_ENTER) {
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
      delay(2000);

      while (1)
      {
        if (keyboard.available()) {
          // read the next key
          c = keyboard.read();

          // check for some of the special keys
          if (c == PS2_ENTER) {
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

    else if (c == PS2_ESC) {
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



  if (valid_user)
  {
tps:
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
        memset(MobNo, 0, sizeof(MobNo));
        lcd.clear();
        x = 0; y = 0;
        lcd.setCursor(x, y);
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
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Temp Scan finish");
    lcd.setCursor(0, 1);
    lcd.print("Temp : ");
    lcd.print(average_temp);
    lcd.print("'F");
#ifdef _DEBUG
    Serial.print("object Temprature= ");
    Serial.print(average_temp);
    Serial.println("'F");
#endif
    String tempavg = String(average_temp);
    if (tempavg.toInt() > 94) {
#ifdef _DEBUG
      Serial.println("Temprature is > 94");
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
      Serial.println("Temprature is <86");
#endif
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Temprature low ");
      //lcd.setCursor(0, 1);
      //lcd.print("Pls Scan again ");
      err_rled_buz_3();
      delay(1000);
      TEMPStaus = false;
      lcd.clear();
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
    lcd.clear();
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
      memset(MobNo, 0, sizeof(MobNo));
      lcd.clear();
      x = 0; y = 0;
      lcd.setCursor(x, y);
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

    if (spo2.toInt() < 90) {
#ifdef _DEBUG
      Serial.println("SOP2 is < 90");
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
      HTTPClient http;

      // Your Domain name with URL path or IP address with path
      http.begin(server_device_data);
      // If you need an HTTP request with a content type: application/json, use the following: {"deviceid":"010","userid":"1","temp":"92.77","spo2":"89.84"}
      http.addHeader("Content-Type", "application/json"); //                                   {"deviceid":"010","userid":"1","temp":"98.2","spo2":"96"}
      //#ifdef _DEBUG
      //      Serial.println("{\"deviceid\":\"" + deviceid + "\",\"userid\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hb\":\"" + hb + "\"}");
      //#endif
      //      int httpResponseCode = http.POST("{\"deviceid\":\"" + deviceid + "\",\"userid\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hb\":\"" + hb + "\"}");

#ifdef _DEBUG
      Serial.println("{\"deviceid\":\"" + deviceid + "\",\"identifier\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hbcount\":\"" + hb + "\",\"flagstatus\":\"" + flag + "\"}");
#endif
      int httpResponseCode = http.POST("{\"deviceid\":\"" + deviceid + "\",\"identifier\":\"" + userid + "\",\"temp\":\"" + tempavg + "\",\"spo2\":\"" + spo2 + "\",\"hbcount\":\"" + hb + "\",\"flagstatus\":\"" + flag + "\"}");


      String payload = http.getString();
#ifdef _DEBUG
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      Serial.println(payload);    //Print request response
#endif
      http.end();                 // Free resources
      String resp = getValue(payload, '"', 11);
      if (resp == "OK") {
        dataresp = true;
        ok_gled_buz_2();
      }
      else {
        dataresp = false;
        err_rled_buz_3();
      }
#ifdef _DEBUG
      Serial.print("dataresp = ");
      Serial.println(resp);
#endif
    }
    else {
#ifdef _DEBUG
      Serial.println("WiFi Disconnected");
#endif
      dataresp = false;
      err_data_rgled_buz_4();
      err_rled_buz_3();
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
    lcd.setCursor(x, y);
    lcd.clear();
  }
}
