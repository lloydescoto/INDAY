#include <TinyGPS++.h>
#include <SoftwareSerial.h>
#include "EMIC2.h"
TinyGPSPlus gps;
SoftwareSerial ss(4, 3);
String AP = "Guest House";       // CHANGE ME
String PASS = "bigfirm94"; // CHANGE ME
String HOST = "192.168.10.101";
String PORT = "80";
String payload;
int countTrueCommand;
int countTimeCommand;
String location;
boolean found = false;
SoftwareSerial esp8266(10,11);
EMIC2 emic;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(9600);
  ss.begin(9600);
  esp8266.begin(115200);
  sendCommand("AT",5,"OK");
  sendCommand("AT+CWMODE=1",5,"OK");
  sendCommand("AT+CWJAP=\""+ AP +"\",\""+ PASS +"\"",20,"OK");
  emic.begin(18, 19);
}

void loop() {
  // put your main code here, to run repeatedly:
  ss.listen();
    while (ss.available() > 0){
      gps.encode(ss.read());
      if (gps.location.isUpdated()){
        Serial.print("Latitude= "); 
        Serial.print(gps.location.lat(), 6);
        Serial.print(" Longitude= "); 
        Serial.println(gps.location.lng(), 6);
        esp8266.listen();
        String getData = "GET /location.php?latitude="+String(gps.location.lat(), 6)+"&longitude="+String(gps.location.lng(), 6);
        sendCommand("AT+CIPMUX=1",5,"OK");
        sendCommand("AT+CIPSTART=0,\"TCP\",\""+ HOST +"\","+ PORT,15,"OK");
        sendCommand("AT+CIPSEND=0," +String(getData.length()+4),4,">");
        esp8266.println(getData);delay(1500);countTrueCommand++;
        sendCommand("AT+CIPCLOSE=0",5,"OK");
        payload = "";
        location = "";
        while (esp8266.available() > 0) {
         payload += esp8266.readString();
        }
        Serial.println(payload);
        location = payload.substring((payload.indexOf('[') + 1), payload.indexOf(']'));
      }
 }
 if(location != "")
 {
   emic.speak(location);
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
