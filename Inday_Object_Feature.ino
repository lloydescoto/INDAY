#include <uspeech.h>
#include <SoftwareSerial.h>
#include "EMIC2.h"
signal voice(A0);
int time;
bool first =  true, process = false;
syllable s;
String AP = "Guest House";       // CHANGE ME
String PASS = "bigfirm94"; // CHANGE ME
String HOST = "192.168.10.101";
String PORT = "80";
String payload;
int countTrueCommand;
int countTimeCommand;
boolean found = false;
long duration;
int distance;
SoftwareSerial esp8266(10,11);
EMIC2 emic;
void detect(){
  digitalWrite(7, LOW);
  delayMicroseconds(2);
  // Sets the trigPin on HIGH state for 10 micro seconds
  digitalWrite(7, HIGH);
  delayMicroseconds(10);
  digitalWrite(7, LOW);
  // Reads the echoPin, returns the sound wave travel time in microseconds
  duration = pulseIn(8, HIGH);
  // Calculating the distance
  distance= duration*0.034/2;
  if(distance < 200)
  {
    emic.speak("Object close ahead");
  } else {
    emic.speak("Object far ahead");
  }
  esp8266.listen();
  String getData = "GET /object.php?distance=" + String(distance);
  sendCommand("AT+CIPMUX=1",5,"OK");
  sendCommand("AT+CIPSTART=0,\"TCP\",\""+ HOST +"\","+ PORT,15,"OK");
  sendCommand("AT+CIPSEND=0," +String(getData.length()+4),4,">");
  esp8266.println(getData);delay(1500);countTrueCommand++;
  sendCommand("AT+CIPCLOSE=0",5,"OK");
}
void setup() {
  // put your setup code here, to run once:
  pinMode(7, OUTPUT);
  pinMode(8, INPUT);
  Serial.begin(9600);
  voice.f_enabled = true;
  voice.minVolume = 1500;
  voice.fconstant = 400;
  voice.econstant = 1;
  voice.aconstant = 2;
  voice.vconstant = 3;
  voice.shconstant = 4;
  voice.calibrate();
  esp8266.begin(115200);
  sendCommand("AT",5,"OK");
  sendCommand("AT+CWMODE=1",5,"OK");
  sendCommand("AT+CWJAP=\""+ AP +"\",\""+ PASS +"\"",20,"OK");
  emic.begin(18, 19);
}

void loop() {
  // put your main code here, to run repeatedly: 
  char c = voice.getPhoneme();
  
  if(c==' '){
    if(process){
      int sum = s.f+s.o+s.v+s.s+s.h;
      if(sum>30){
        if(s.f>3){
          if(s.s>3){
            detect();
          }
          else{
          detect();
          }
        }
        else{
          if(s.s>3){
            detect();
          }
          else{
            detect();
          }
        }
      }
      s.f = 0;
      s.e = 0;
      s.o = 0;
      s.v = 0;
      s.s = 0;
      s.h = 0;
      process = false;
      
    }
  }
  else{
    if(first){
      time = millis();
    }
    else{
      
    }
    s.classify(c);
    process = true;
  }
}

void sendCommand(String command, int maxTime, char readReplay[]) {
  Serial.print(countTrueCommand);
  Serial.print(". at command => ");
  Serial.print(command);
  Serial.print(" ");
  while(countTimeCommand < (maxTime*1))
  {
    esp8266.println(command);//at+cipsend
    if(esp8266.find(readReplay))//ok
    {
      found = true;
      break;
    }
  
    countTimeCommand++;
  }
  
  if(found == true)
  {
    Serial.println("OYI");
    countTrueCommand++;
    countTimeCommand = 0;
  }
  
  if(found == false)
  {
    Serial.println("Fail");
    countTrueCommand = 0;
    countTimeCommand = 0;
  }
  
  found = false;
 }
