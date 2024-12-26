[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-7.1%20%3E-blue.svg)
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

* mindestens IPS Version 7.1
* MQTT Server oder MQTT Client
* Shelly BLU Script auf den wifi Geräten, welche als BLU Gateway fungieren.

## 1.1 Shelly BLU Script

```js
/**
 * This script will use BLE observer to listen for advertising data from nearby Shelly BLU devices,
 * decodes the data using a BTHome data structure, and emits the decoded data for further processing.
 *
 * This script DOESN'T execute actions, only emit events. Can be used with `ble-events-handler.js` example.
 * You can configure the event name, by default its `shelly-blu`, the body of the event contains all the data
 * parsed from the BLE device
 *
 * Represents data provided by each device.
 * Every"Info: The BLE gateway is running, the BLE scan configuration is managed by the device");
  }
  else {
    //start the scanner
    const bleScanner = BLE.Scanner.Start({
        duration_ms: BLE.Scanner.INFINITE_SCAN,
        active: CONFIG.active
    });

    if(!bleScanner) {
      console.log("Error: Can not start new scanner");
    }
  }

  //subscribe a callback to BLE scanner
  BLE.Scanner.Subscribe(BLEScanCallback);

  // disable console.log when logs are disabled
  if (!CONFIG.debug) {
    console.l value illustrating a sensor reading (e.g., button) may be a singular sensor value or 
 * an array of values if the object has multiple instances. 
 * 
 * @typedef {Object} DeviceData
 * @property {number} pid - Packet ID.
 * @property {number} battery - The battery level of the device in percentage (%).
 * @property {number} rssi - The signal strength in decibels (dB).
 * @property {string} address - The MAC address of the Shelly BLU device.
 * @property {string} model - The model of the Shelly BLU device.og = function() {};
  }
}

init();

 * @property {number | number[]} [temperature] - The temperature value in degrees Celsius if the device has a temperature sensor. (Can be an array if has multiple instances)
 * @property {number | number[]} [humidity] - The humidity value in percentage (%) if the device has a humidity sensor. (Can be an array if has multiple instances)
 * @property {number | number[]} [illuminance] - The illuminance value in lux if the device has a light sensor. (Can be an array if has multiple instances)
 * @property {number | number[]} [motion] - Motion status: 0 for clear, 1 for motion (if the device has a motion sensor). (Can be an array if has multiple instances)
 * @property {number | number[]} [window] - Window status: 0 for closed, 1 for open (if the device has a reed switch). (Can be an array if has multiple instances)
 * @property {number | number[]} [button] - The number of presses if the device has a button. (Can be an array if has multiple instances)
 * @property {number | number[]} [rotation] - The angle of rotation in degrees if the device has a gyroscope. (Can be an array if has multiple instances)
 * 
 * @example
 * {"component":"script:*","name":"script","id":*,"now":*,"info":{"component":"script:*","id":*,"event":"shelly-blu","data":{"encryption":false,"BTHome_version":2,"pid":118,"battery":100,"button":1,"rssi":-76,"address":*},"ts":*}}
 */

/******************* START CHANGE HERE *******************/
const CONFIG = {
  // Specify the destination event where the decoded BLE data will be emitted. It allows for easy identification by other applications/scripts
  eventName: "shelly-blu",

  // If the script owns the scanner and this value is set to true, the scan will be active.
  // If the script does not own the scanner, it may remain passive even when set to true. 
  // Active scan means the scanner will ping back the Bluetooth device to receive all its data, but it will drain the battery faster
  active: false,

  // When set to true, debug messages will be logged to the console
  debug: true,
};
/******************* STOP CHANGE HERE *******************/

const BTHOME_SVC_ID_STR = "fcd2";

const uint8 = 0;
const int8 = 1;
const uint16 = 2;
const int16 = 3;
const uint24 = 4;
const int24 = 5;

// The BTH object defines the structure of the BTHome data
const BTH = {
  0x00: { n: "pid", t: uint8 },
  0xF0: { n: "deviceID", t: uint16},
  0x01: { n: "battery", t: uint8, u: "%" },
  0x02: { n: "temperature", t: int16, f: 0.01, u: "tC" },
  0x03: { n: "humidity", t: uint16, f: 0.01, u: "%" },
  0x05: { n: "illuminance", t: uint24, f: 0.01 },
  0x21: { n: "motion", t: uint8 },
  0x2d: { n: "window", t: uint8 },
  0x2e: { n: "humidity", t: uint8, u: "%" },
  0x3a: { n: "button", t: uint8 },
  0x3f: { n: "rotation", t: int16, f: 0.1 },
  0x45: { n: "temperature", t: int16, f: 0.1, u: "tC" },
};

function getByteSize(type) {
  if (type === uint8 || type === int8) return 1;
  if (type === uint16 || type === int16) return 2;
  if (type === uint24 || type === int24) return 3;
  //impossible as advertisements are much smaller;
  return 255;
}

// functions for decoding and unpacking the service data from Shelly BLU devices
const BTHomeDecoder = {
  utoi: function (num, bitsz) {
    const mask = 1 << (bitsz - 1);
    return num & mask ? num - (1 << bitsz) : num;
  },
  getUInt8: function (buffer) {
    return buffer.at(0);
  },
  getInt8: function (buffer) {
    return this.utoi(this.getUInt8(buffer), 8);
  },
  getUInt16LE: function (buffer) {
    return 0xffff & ((buffer.at(1) << 8) | buffer.at(0));
  },
  getInt16LE: function (buffer) {
    return this.utoi(this.getUInt16LE(buffer), 16);
  },
  getUInt24LE: function (buffer) {
    return (
      0x00ffffff & ((buffer.at(2) << 16) | (buffer.at(1) << 8) | buffer.at(0))
    );
  },
  getInt24LE: function (buffer) {
    return this.utoi(this.getUInt24LE(buffer), 24);
  },
  getBufValue: function (type, buffer) {
    if (buffer.length < getByteSize(type)) return null;
    let res = null;
    if (type === uint8) res = this.getUInt8(buffer);
    if (type === int8) res = this.getInt8(buffer);
    if (type === uint16) res = this.getUInt16LE(buffer);
    if (type === int16) res = this.getInt16LE(buffer);
    if (type === uint24) res = this.getUInt24LE(buffer);
    if (type === int24) res = this.getInt24LE(buffer);
    return res;
  },

  // Unpacks the service data buffer from a Shelly BLU device
  unpack: function (buffer) {
    //beacons might not provide BTH service data
    if (typeof buffer !== "string" || buffer.length === 0) return null;
    let result = {};
    let _dib = buffer.at(0);
    result["encryption"] = _dib & 0x1 ? true : false;
    result["BTHome_version"] = _dib >> 5;
    if (result["BTHome_version"] !== 2) return null;
    //can not handle encrypted data
    if (result["encryption"]) return result;
    buffer = buffer.slice(1);

    let _bth;
    let _value;
    while (buffer.length > 0) {
      _bth = BTH[buffer.at(0)];
      if (typeof _bth === "undefined") {
        console.log("BTH: Unknown type");
        break;
      }
      buffer = buffer.slice(1);
      _value = this.getBufValue(_bth.t, buffer);
      if (_value === null) break;
      if (typeof _bth.f !== "undefined") _value = _value * _bth.f;

      if (typeof result[_bth.n] === "undefined") {
        result[_bth.n] = _value;
      }
      else {
        if (Array.isArray(result[_bth.n])) {
          result[_bth.n].push(_value);
        } 
        else {
          result[_bth.n] = [
            result[_bth.n],
            _value
          ];
        }
      }

      buffer = buffer.slice(getByteSize(_bth.t));
    }
    return result;
  },
};

/**
 * ��mitting the decoded BLE data to a specified event. It allows other scripts to receive and process the emitted data
 * @param {DeviceData} data 
 */
function emitData(data) {
  if (typeof data !== "object") {
    return;
  }

  Shelly.emitEvent(CONFIG.eventName, data);
}

//saving the id of the last packet, this is used to filter the duplicated packets
let lastPacketId = 0x100;

// Callback for the BLE scanner object
function BLEScanCallback(event, result) {
  //exit if not a result of a scan
  if (event !== BLE.Scanner.SCAN_RESULT) {
    return;
  }

  //exit if service_data member is missing
  if (
    typeof result.service_data === "undefined" ||
    typeof result.service_data[BTHOME_SVC_ID_STR] === "undefined"
  ) {
    return;
  }

  let unpackedData = BTHomeDecoder.unpack(
    result.service_data[BTHOME_SVC_ID_STR]
  );

  //exit if unpacked data is null or the device is encrypted
  if (
    unpackedData === null ||
    typeof unpackedData === "undefined" ||
    unpackedData["encryption"]
  ) {
    console.log("Error: Encrypted devices are not supported");
    return;
  }

  //exit if the event is duplicated
  if (lastPacketId === unpackedData.pid) {
    return;
  }

  lastPacketId = unpackedData.pid;

  unpackedData.rssi = result.rssi;
  unpackedData.address = result.addr;
  unpackedData.model = result.local_name;
  unpackedData.deviceID = unpackedData.deviceID;
    
  emitData(unpackedData);
}

// Initializes the script and performs the necessary checks and configurations
function init() {
  //exit if can't find the config
  if (typeof CONFIG === "undefined") {
    console.log("Error: Undefined config");
    return;
  }

  //get the config of ble component
  const BLEConfig = Shelly.getComponentConfig("ble");

  //exit if the BLE isn't enabled
  if (!BLEConfig.enable) {
    console.log(
      "Error: The Bluetooth is not enabled, please enable it from settings"
    );
    return;
  }

  //check if the scanner is already running
  if (BLE.Scanner.isRunning()) {
    console.log("Info: The BLE gateway is running, the BLE scan configuration is managed by the device");
  }
  else {
    //start the scanner
    const bleScanner = BLE.Scanner.Start({
        duration_ms: BLE.Scanner.INFINITE_SCAN,
        active: CONFIG.active
    });

    if(!bleScanner) {
      console.log("Error: Can not start new scanner");
    }
  }

  //subscribe a callback to BLE scanner
  BLE.Scanner.Subscribe(BLEScanCallback);

  // disable console.log when logs are disabled
  if (!CONFIG.debug) {
    console.log = function() {};
  }
}

init();
```

## 2. Enthaltene Module

* [ShellyBLUButton1](ShellyBLUButton1/README.md)
* [ShellyBLUConfigurator](ShellyBLUConfigurator/README.md)
* [ShellyBLUDoorWindow](ShellyBLUDoorWindow/README.md)
* [ShellyBLUGateway](ShellyBLUGateway/README.md)
* [ShellyBLUHT](ShellyBLUHT/README.md)
* [ShellyBLUMotion](ShellyBLUMotion/README.md)
* [ShellyBLURCButton](ShellyBLURCButton/README.md)

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
