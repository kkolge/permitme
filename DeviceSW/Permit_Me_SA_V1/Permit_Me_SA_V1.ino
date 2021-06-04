#include <EEPROM.h>
#include <Wire.h>
#include "LiquidCrystal_I2C.h"
#include "MAX30102.h"
#include "MLX90614.h"
#include "Pulse.h"


//#define _DEBUG      // UNCOMMENT FOR DEBUGGING/////////////////////////////


LiquidCrystal_I2C lcd(16, 2); // set the LCD 16 chars and 2 line display

String deviceid = "DEVKOTEST1";      // DEV-KO-001  ko-aaham office

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

bool TScanst=false;

const int analogInPin = A0;
static const uint8_t RLED = D0;
static const uint8_t GLED = D8;
static const uint8_t BUZ = D9;
static const uint8_t RLY = D10;

int sensorValue = 0;        // value read from the pot


void HB_SPO2_OUT()
{
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
      TScanst=false;
      break;
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


//String Read_Device_Id() {
//  int offset = 300;
//  String rid = "";
//  for (int8_t i = 0; i < 20; ++i)
//  {
//    if (EEPROM.read(offset + i) == '\0')
//      break;
//    rid += char(EEPROM.read(offset + i));
//  }
//  return (rid);
//}



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

  //deviceid = Read_Device_Id();  //read device id from eeprom
  //deviceid.trim();
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

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(" Stay closer to ");
  lcd.setCursor(0, 1);
  lcd.print("   Temp Sensor  ");
  uint8_t time_out = 0;
  while (1)
  {
    sensorValue = analogRead(analogInPin);
    delay(1000);
#ifdef _DEBUG
    Serial.print("sensor = ");
    Serial.println(sensorValue);
#endif
    if (sensorValue > 100) {
      ok_gled_buz_2();
      break;
    }
  }

  MLX9614();
  
  if(TScanst==false)
  goto up;
  
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
  if (tempavg.toFloat() > 93.5) {
#ifdef _DEBUG
    Serial.println("Temprature is > Set Max Value");
    Serial.println(93.5);
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
    Serial.println("Temprature is <86");Serial.println("86'F");
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

  if (spo2.toInt() < 95) {
#ifdef _DEBUG
    Serial.print("SOP2 is < Set Max Value = ");Serial.println("95%");
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
        goto up;
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


  if (SPO2Staus && TEMPStaus) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("   DOOR OPEN    ");
    digitalWrite(RLY, HIGH);
    delay(3000);
#ifdef _DEBUG
    Serial.println("OPEN DOOR");
#endif
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("   Thank you!   ");
    lcd.setCursor(0, 1);
    lcd.print("Have a good day.");
    ok_gled_buz_2();
    delay(3000);
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
    delay(3000);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("TEMP HIGH");
    lcd.setCursor(0, 1);
    lcd.print("Pls visit doctor");
    err_rled_buz_3();
    delay(3000);
  }
  else if (!SPO2Staus) {
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("   DOOR CLOSE    ");
    digitalWrite(RLY, LOW);
    delay(3000);
#ifdef _DEBUG
    Serial.println("close DOOR");
#endif
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("SPO2 LOW");
    lcd.setCursor(0, 1);
    lcd.print("Pls visit doctor");
    err_rled_buz_3();
    delay(3000);
  }
lcd.setCursor(0, 0);
lcd.clear();
}
