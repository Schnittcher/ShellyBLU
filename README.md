[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-8.0%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![Check Style](https://github.com/Schnittcher/ShellyBLU/workflows/Check%20Style/badge.svg)](https://github.com/Schnittcher/ShellyBLU/actions)

# ShellyBLU
   Mit diesem Modul ist es möglich die Bluetooth Geräte von Shelly in IP-Symcon einzubinden.
 
## Inhaltverzeichnis
- [ShellyBLU](#shellyblu)
  - [Inhaltverzeichnis](#inhaltverzeichnis)
  - [1. Voraussetzungen](#1-voraussetzungen)
  - [1.1 Shelly BLU Script](#11-shelly-blu-script)
  - [2. Enthaltene Module](#2-enthaltene-module)
  - [3. Installation](#3-installation)
  - [4. Konfiguration in IP-Symcon](#4-konfiguration-in-ip-symcon)
  - [5. Spenden](#5-spenden)
  - [6. Lizenz](#6-lizenz)
   
## 1. Voraussetzungen

* mindestens IPS Version 8.0
* MQTT Server oder MQTT Client
* Shelly BLU Script auf den Wifi Geräten, welche als BLU Gateway fungieren.

## 1.1 Shelly BLU Script

Das Script ist hier zu finden: * [symcon-blu.js](libs/symcon-blu.js)

## 2. Enthaltene Module

* [ShellyBLUButton1](ShellyBLUButton1/README.md)
* [ShellyBLUButtonTough1](ShellyBLUButtonTough1/README.md)
* [ShellyBLUButtonTough1ZB](ShellyBLUButtonTough1ZB/README.md)
* [ShellyBLUConfigurator](ShellyBLUConfigurator/README.md)
* [ShellyBLUDoorWindow](ShellyBLUDoorWindow/README.md)
* [ShellyBLUEcowittWS90](ShellyBLUEcowittWS90/README.md)
* [ShellyBLUHT](ShellyBLUHT/README.md)
* [ShellyBLUHTDisplayZB](ShellyBLUHTDisplayZB/README.md)
* [ShellyBLUHTZB](ShellyBLUHTZB/README.md)
* [ShellyBLUMotion](ShellyBLUMotion/README.md)
* [ShellyBLURCButton4](ShellyBLURCButton4/README.md)
* [ShellyBLURemoteControlZB](ShellyBLURemoteControlZB/README.md)
* [ShellyBLUWallSwitch4](ShellyBLUWallSwitch4/README.md) 


## 3. Installation
Installation über den IP-Symcon Module Store.

## 4. Konfiguration in IP-Symcon
Das Modul kann mit dem internen MQTT Server betrieben werden, oder aber mit einem externen MQTT Broker.

Standardmäßig wird der MQTT Server bei den Geräteinstanzen als Parent hinterlegt, wenn aber ein externer Broker verwendet werden soll, muss der MQTT Client per Hand angelegt werden und in der Geräteinstanz unter "Gateway ändern" ausgewählt werden.

Die weitere Dokumentation bitte den einzelnen Modulen entnehmen.

## 5. Spenden

Dieses Modul ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a> <a href="https://www.amazon.de/hz/wishlist/ls/3JVWED9SZMDPK?ref_=wl_share" target="_blank">Amazon Wunschzettel</a>

## 6. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
