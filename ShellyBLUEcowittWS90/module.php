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

        $this->RegisterVariableFloat('RelativeHumidity', $this->Translate('Relative Humidity'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet-percent',
            'SUFFIX'         => ' %',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 1);

        $this->RegisterVariableFloat('Illumination', $this->Translate('Illumination'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'brightness',
            'SUFFIX'         => ' lux',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 2);

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
        ], 3);

        $this->RegisterVariableFloat('Precipitation', $this->Translate('Precipitation'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet',
            'SUFFIX'         => ' mm',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 4);
        $this->RegisterVariableFloat('WindSpeed', $this->Translate('Wind Speed'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'wind',
            'SUFFIX'         => ' m/s',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 5);

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
        ], 6);

        $this->RegisterVariableInteger('UVIndex', $this->Translate('UV Index'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'PERCENTAGE'     => false,
        ], 8);

        $this->RegisterVariableFloat('AtmPressure', $this->Translate('Atmospheric Pressure'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'gauge',
            'SUFFIX'         => ' hPa',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
        ], 9);

        $this->RegisterVariableFloat('DewPoint', $this->Translate('Dew Point'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'droplet-degree',
            'SUFFIX'         => ' °C',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 10);

        $this->RegisterVariableFloat('CapacitorVoltage', $this->Translate('Capacitor Voltage'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'bolt',
            'SUFFIX'         => ' V',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 11);

        $this->RegisterVariableInteger('Battery', $this->Translate('Battery'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'battery-full',
            'SUFFIX'         => ' %',
        ], 12);

        $this->RegisterVariableString('Gateway', $this->Translate('Gateway'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'server',
            'SUFFIX'         => '',

        ], 13);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'signal'
        ], 14);
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
}
