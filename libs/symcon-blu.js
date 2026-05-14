/**
 * @title Example - Universal BLU to MQTT Script
 * @description This script is about shares any BLU product's complete payload to
 *   MQTT..
 * @status production
 * @link https://github.com/ALLTERCO/shelly-script-examples/blob/main/ble/universal-blu-to-mqtt.shelly.js
 */

const uint8 = 0;
const int8 = 1;
const uint16 = 2;
const int16 = 3;
const uint24 = 4;
const int24 = 5;

const topic = 'symcon/shellyblu/';

const BTH = {
  0x00: { n: "pid", t: uint8 },
  0x0C: { n: "capacitor_voltage", t: uint16, f: 0.001 },
  0x01: { n: "battery", t: uint8, u: "%" },
  0x04: { n: "atm_pressure", t: uint24, f: 0.01 },
  0x15: { n: "battery_low", t: uint8 },
  0x1E: { n: "light", t: uint8 },
  0x45: { n: "temperature", t: int16, f: 0.1,  u: "tC" },
  0x02: { n: "temperature", t: int16, f: 0.01, u: "tC" },
  0x2E: { n: "humidity", t: uint8, f: 0.01, u: "%" },
  0x03: { n: "humidity", t: uint16, f: 0.01, u: "%" },
  0x05: { n: "illumination", t: uint24, f: 0.01 },
  0x08: { n: "dew_point", t: int16, f: 0.01 },
  0x20: { n: "rain_status", t: uint8 },
  0x21: { n: "motion", t: uint8 },
  0x2c: { n: "vibration", t: uint8 },
  0x2d: { n: "window", t: uint8 },
  0x2E: { n: "humidity", t: uint8, u: "%" },
  0x3a: { n: "button", t: uint8 },
//  0x3a: [
//    { n: "button", t: uint16 }, // gr��ßter zuerst
//    { n: "button", t: uint8 },
//  ],
  0x3F: { n: "rotation", t: int16, f: 0.1 },
  0x40: { n: "distance_mm", t: uint16 },
  0x44: { n: "wind_speed", t: uint16, f: 0.01 },
  0x46: { n: "uv_index", t: uint8, f: 0.1 },
  0x5F: { n: "precipitation", t: uint16, f: 0.1 },
  0x5E: { n: "wind_direction", t: uint16, f: 0.01 },
  0x60: { n: "channel", t: uint8 },
  0x3C: { n: "dimmer", t: uint16 },
};

function getByteSize(type) {
  if (type === uint8 || type === int8) return 1;
  if (type === uint16 || type === int16) return 2;
  if (type === uint24 || type === int24) return 3;
  return 255;
}

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

  unpack: function (buffer) {
    if (typeof buffer !== "string" || buffer.length === 0) return null;
    let result = {};
    let _dib = buffer.at(0);
    result["encryption"] = _dib & 0x1 ? true : false;
    result["BTHome_version"] = _dib >> 5;
    if (result["BTHome_version"] !== 2) return null;
    if (result["encryption"]) return result;
    buffer = buffer.slice(1);

    let _bth;
    let _value;
    while (buffer.length > 0) {
      _bth = BTH[buffer.at(0)];
      if (typeof _bth === "undefined") {
        console.log("BTH: Unknown type: 0x" + buffer.at(0).toString(16));
        break;
      }

      // Array-Einträge auflösen: größter passender Typ gewinnt
      if (Array.isArray(_bth)) {
        let matched = null;
        for (let i = 0; i < _bth.length; i++) {
          if (buffer.length - 1 >= getByteSize(_bth[i].t)) {
            matched = _bth[i];
            break; // größter zuerst im Array, erster Treffer reicht
          }
        }
        if (matched === null) {
          console.log("BTH: No matching type for array entry");
          break;
        }
        _bth = matched;
      }

      buffer = buffer.slice(1);
      _value = this.getBufValue(_bth.t, buffer);
      if (_value === null) break;
      if (typeof _bth.f !== "undefined") _value = _value * _bth.f;

      if (typeof result[_bth.n] === "undefined") {
        result[_bth.n] = _value;
      } else {
        if (Array.isArray(result[_bth.n])) {
          result[_bth.n].push(_value);
        } else {
          result[_bth.n] = [result[_bth.n], _value];
        }
      }

      buffer = buffer.slice(getByteSize(_bth.t));
    }
    return result;
  },
};

// ***********************  Main Methods ***********************

const BTHOME_SVC_ID_STR = "fcd2";

const SCAN_OPTION = {
  duration_ms: BLE.Scanner.INFINITE_SCAN,
  active: true,
};

function pushToMQ(addr, message) {
  if (!MQTT.isConnected()) return false;
  MQTT.publish(addr, message);
  return true;
}

function scanCB(ev, res) {
  if (ev !== BLE.Scanner.SCAN_RESULT) return;
  const addr = res.addr;
  if (typeof res.service_data === "undefined" || typeof res.service_data[BTHOME_SVC_ID_STR] === "undefined") return;
  if (typeof addr === "undefined") return;

  try {
    const decodeData = BTHomeDecoder.unpack(res.service_data[BTHOME_SVC_ID_STR]);
    const deviceInfo = Shelly.getDeviceInfo();
    const scanRsp = parseScanRsp(res.scanRsp);

if (typeof decodeData.dimmer !== "undefined") {
    console.log("BEFORE:", decodeData.dimmer, "HEX:", decodeData.dimmer.toString(16));
    decodeData.dimmersteps = (decodeData.dimmer >> 8) & 0xFF;
    decodeData.dimmer = decodeData.dimmer & 0xFF;
    console.log("AFTER dimmer:", decodeData.dimmer, "steps:", decodeData.dimmersteps);
}
    
    const postMessage = {
      gateway: deviceInfo.id,        // z.B. "shellyblugw-f008d1c2a3b4"
      gateway_name: deviceInfo.name, // z.B. "Wohnzimmer Gateway"
      addr: addr,
      rssi: res.rssi,
      model: res.local_name || (scanRsp ? scanRsp.local_name : "") || "",
      model_id: scanRsp ? scanRsp.model_id : null,
      service_data: decodeData,
    };

    pushToMQ(topic + addr, JSON.stringify(postMessage));
  } catch (err) {
    console.log(err);
  }
}

function parseScanRsp(buf) {
    if (typeof buf === "undefined" || buf.length === 0) return null;
    let result = {};
    let pos = 0;

    while (pos < buf.length) {
        let len = buf.at(pos);
        let type = buf.at(pos + 1);
        pos += 2;

        if (type === 0x08 || type === 0x09) {
            // Local Name
            let name = "";
            for (let i = 0; i < len - 1; i++) {
                name += String.fromCharCode(buf.at(pos + i));
            }
            result.local_name = name;
        } else if (type === 0xFF) {
            // Manufacturer Data
            let mfid = buf.at(pos) | (buf.at(pos + 1) << 8);
            if (mfid === 0x0BA9) {
                let mpos = pos + 2;
                while (mpos < pos + len - 1) {
                    let blockType = buf.at(mpos);
                    mpos++;
                    if (blockType === 0x01) {
                        result.flags = buf.at(mpos) | (buf.at(mpos + 1) << 8);
                        mpos += 2;
                    } else if (blockType === 0x0B) {
                        result.model_id = buf.at(mpos) | (buf.at(mpos + 1) << 8);
                        mpos += 2;
                    } else if (blockType === 0x0A) {
                        mpos += 6; // MAC überspringen
                    } else {
                        break;
                    }
                }
            }
        }
        pos += len - 1;
    }
    return result;
}

function init() {
  const BLEConfig = Shelly.getComponentConfig("ble");

  if (!BLEConfig.enable) {
    console.log("Error: The Bluetooth is not enabled, please enable it from settings");
    return;
  }

  if (BLE.Scanner.isRunning()) {
    console.log("Info: The BLE gateway is running, the BLE scan configuration is managed by the device");
  } else {
    const bleScanner = BLE.Scanner.Start(SCAN_OPTION);
    if (!bleScanner) {
      console.log("Error: Can not start new scanner");
    }
  }

  BLE.Scanner.Subscribe(scanCB);
}

function parseManufacturerData(mfdata) {
    if (typeof mfdata === "undefined") return null;
    
    // Prüfen ob es Allterco MFID 0x0BA9 ist
    // Format: Length | Type(0xFF) | MFID(0xA9, 0x0B) | payload
    let result = {};
    let pos = 0;
    
    // MFID überspringen (2 Bytes)
    pos = 2;
    
    while (pos < mfdata.length) {
        let blockType = mfdata.at(pos);
        pos++;
        
        if (blockType === 0x01) {
            // Flags (2 Bytes)
            result.flags = mfdata.at(pos) | (mfdata.at(pos + 1) << 8);
            pos += 2;
        } else if (blockType === 0x0A) {
            // MAC (6 Bytes) - überspringen
            pos += 6;
        } else if (blockType === 0x0B) {
            // Device model ID (2 Bytes)
            result.model_id = mfdata.at(pos) | (mfdata.at(pos + 1) << 8);
            pos += 2;
        } else {
            break; // Unbekannter Block
        }
    }
    
    return result;
}

init();