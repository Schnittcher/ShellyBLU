<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyBLUHT extends IPSModule
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

        $this->RegisterVariableInteger('Button', $this->Translate('Button'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'circle-dot',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 254,  'IntervalMaxValue' => 254, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Hold'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 2);

        $this->RegisterVariableInteger('Battery', $this->Translate('Battery'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'battery-full',
            'SUFFIX'         => ' %',
        ], 3);

        $this->RegisterVariableString('Gateway', $this->Translate('Gateway'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'server',
            'SUFFIX'         => '',

        ], 4);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'signal'
        ], 5);
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
                if (array_key_exists('humidity', $data)) {
                    $this->SetValue('RelativeHumidity', $data['humidity']);
                }
                if (array_key_exists('temperature', $data)) {
                    $this->SetValue('Temperature', $data['temperature']);
                }
                if (array_key_exists('button', $data)) {
                    $this->SetValue('Button', $data['button']);
                }
                if (array_key_exists('battery', $data)) {
                    $this->SetValue('Battery', $data['battery']);
                }
            }
        }
    }
}
