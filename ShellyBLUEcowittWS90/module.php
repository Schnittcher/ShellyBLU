<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyBLUEcowittWS90 extends IPSModule
{
    use DebugHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterPropertyString('Topic', '');

        $this->RegisterVariableFloat('Temperature', $this->Translate('Temperature'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'temperature-full',
            'SUFFIX'         => ' °C',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 0);

        $this->RegisterVariableFloat('WindChill', $this->Translate('Wind Chill'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'temperature-full',
            'SUFFIX'         => ' °C',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 1);

        $this->RegisterVariableFloat('RelativeHumidity', $this->Translate('Relative Humidity'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet-percent',
            'SUFFIX'         => ' %',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 2);

        $this->RegisterVariableFloat('Illumination', $this->Translate('Illumination'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'brightness',
            'SUFFIX'         => ' lux',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 3);

        $this->RegisterVariableBoolean('RainStatus', $this->Translate('Rain Status'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'raindrops',
            'OPTIONS'      => json_encode([
                    [
                        'Value' => true,
                        'Caption' => $this->Translate('Rain'),
                        'IconActive' => false,
                        'Icon' => '',
                        'ColorActive' => true,
                        'ColorValue' => 16711680
                    ],
                    [
                        'Value' => false,
                        'Caption' => $this->Translate('No Rain'),
                        'IconActive' => false,
                        'Icon' => '',
                        'ColorActive' => true,
                        'ColorValue' => 3329330
                    ]
            ])
        ], 4);

        $this->RegisterVariableFloat('Precipitation', $this->Translate('Precipitation'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet',
            'SUFFIX'         => ' mm',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 5);
        $this->RegisterVariableFloat('WindSpeed', $this->Translate('Wind Speed'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'wind',
            'SUFFIX'         => ' m/s',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 6);

        $this->RegisterVariableFloat('GustSpeed', $this->Translate('Gust Speed'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'wind',
            'SUFFIX'         => ' m/s',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 7);

        $this->RegisterVariableInteger('WindDirection', $this->Translate('Wind Direction'), [
         'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
         'ICON'           => 'WindDirection',
         'SUFFIX'         => ' °',
         'PERCENTAGE'     => false,
        ], 8);

        $this->RegisterVariableString('WindDirectionString', $this->Translate('Wind Direction'), [
         'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
         'ICON'           => 'WindDirection',
         'SUFFIX'         => '',
         'PERCENTAGE'     => false,
        ], 9);

        $this->RegisterVariableInteger('UVIndex', $this->Translate('UV Index'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'PERCENTAGE'     => false,
        ], 10);

        $this->RegisterVariableFloat('AtmPressure', $this->Translate('Atmospheric Pressure'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'gauge',
            'SUFFIX'         => ' hPa',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
        ], 11);

        $this->RegisterVariableFloat('DewPoint', $this->Translate('Dew Point'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet-degree',
            'SUFFIX'         => ' °C',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 12);

        $this->RegisterVariableFloat('CapacitorVoltage', $this->Translate('Capacitor Voltage'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'bolt',
            'SUFFIX'         => ' V',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 13);

        $this->RegisterVariableInteger('Battery', $this->Translate('Battery'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'battery-full',
            'SUFFIX'         => ' %',
        ], 14);

        $this->RegisterVariableString('Gateway', $this->Translate('Gateway'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'server',
            'SUFFIX'         => '',

        ], 15);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'signal'
        ], 16);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');

        //Setze Filter für ReceiveData
        $Topic = $this->ReadPropertyString('Topic');
        $this->SetReceiveDataFilter('.*' . $Topic . '.*');

    }

    public function ReceiveData($JSONString)
    {

        if (!empty($this->ReadPropertyString('Topic'))) {
            $Buffer = json_decode($JSONString, true);
            $this->SendDebug('JSON', $Buffer, 0);

            $Payload = json_decode($Buffer['Payload'], true);

            if (array_key_exists('rssi', $Payload)) {
                $this->SetValue('RSSI', intval($Payload['rssi']));
            }
            if (array_key_exists('gateway', $Payload)) {
                $this->SetValue('Gateway', $Payload ['gateway']);
            }

            if (array_key_exists('service_data', $Payload)) {
                $data = $Payload['service_data'];
                if (array_key_exists('illumination', $data)) {
                    $this->SetValue('Illumination', $data['illumination']);
                }
                if (array_key_exists('rain_status', $data)) {
                    $this->SetValue('RainStatus', $data['rain_status']);
                }
                if (array_key_exists('wind_speed', $data)) {
                    $this->SetValue('WindSpeed', $data['wind_speed'][0]);
                    $this->SetValue('GustSpeed', $data['wind_speed'][1]);
                }
                if (array_key_exists('uv_index', $data)) {
                    $this->SetValue('UVIndex', $data['uv_index']);
                }
                if (array_key_exists('wind_direction', $data)) {
                    $this->SetValue('WindDirection', $data['wind_direction']);
                    $this->SetValue('WindDirectionString', $this->mappingWindDirection($data['wind_direction']));

                }
                if (array_key_exists('atm_pressure', $data)) {
                    $this->SetValue('AtmPressure', $data['atm_pressure']);
                }
                if (array_key_exists('dew_point', $data)) {
                    $this->SetValue('DewPoint', $data['dew_point']);
                }
                if (array_key_exists('capacitor_voltage', $data)) {
                    $this->SetValue('CapacitorVoltage', $data['capacitor_voltage']);
                }
                if (array_key_exists('humidity', $data)) {
                    $this->SetValue('RelativeHumidity', $data['humidity']);
                }
                if (array_key_exists('temperature', $data)) {
                    $this->SetValue('Temperature', $data['temperature']);
                    $windChill = $this->calculateWindChill(
                        floatval($data['temperature']),
                        floatval($data['humidity'] ?? 0),
                        floatval($data['wind_speed'][0] ?? 0)
                    );
                    $this->SetValue('WindChill', $windChill);

                }
                if (array_key_exists('precipitation', $data)) {
                    $this->SetValue('Precipitation', $data['precipitation']);
                }
                if (array_key_exists('battery', $data)) {
                    $this->SetValue('Battery', $data['battery']);
                }
            }
        }
    }

    //Geüfhlte Temperatur berechnen
    public static function calculateWindChill(float $temp, float $humidity, float $windSpeed): float
    {
        if ($temp <= 10 && $windSpeed > 4.8) {
            // Wind Chill
            return round(
                13.12
                + 0.6215 * $temp
                - 11.37 * pow($windSpeed, 0.16)
                + 0.3965 * $temp * pow($windSpeed, 0.16),
                1
            );
        } elseif ($temp >= 27) {
            // Heat Index
            return round(
                -8.78469475556
                + 1.61139411 * $temp
                + 2.3385248 * $humidity
                - 0.14611605 * $temp * $humidity
                - 0.012308094 * pow($temp, 2)
                - 0.016424828 * pow($humidity, 2)
                + 0.002211732 * pow($temp, 2) * $humidity
                + 0.00072546 * $temp * pow($humidity, 2)
                - 0.000003582 * pow($temp, 2) * pow($humidity, 2),
                1
            );
        }

        return round($temp, 1);
    }

    // Windrichtung in Text umwandeln
    public function mappingWindDirection(int $degree): string
    {
        $directions = array(
        "N","NNO","NO","ONO",
        "O","OSO","SO","SSO",
        "S","SSW","SW","WSW",
        "W","WNW","NW","NNW"
        );
        $index = round($degree / 22.5) % 16;
        return $directions[$index];

    }
}
