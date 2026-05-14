<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';


class ShellyBLUDoorWindow extends IPSModule
{
    use DebugHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterPropertyString('Topic', '');

        $this->RegisterVariableBoolean('Contact', $this->Translate('Contact'), [
                'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'ICON'           => 'window',
                'SUFFIX'         => '',
                            'OPTIONS'      => json_encode([
                            [
                                'Value' => true,
                                'Caption' => $this->Translate('Opend'),
                                'IconActive' => false,
                                'Icon' => '',
                                'ColorActive' => true,
                                'ColorValue' => 16711680
                            ],
                            [
                                'Value' => false,
                                'Caption' => $this->Translate('Closed'),
                                'IconActive' => false,
                                'Icon' => '',
                                'ColorActive' => true,
                                'ColorValue' => 3329330
                            ]
                ])
        ], 0);

        $this->RegisterVariableFloat('Illumination', $this->Translate('Illumination'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'brightness',
            'SUFFIX'         => ' lux',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.01,
        ], 1);

        $this->RegisterVariableInteger('Rotation', $this->Translate('Rotation'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'rotate',
            'SUFFIX'         => '',
            'PERCENTAGE'     => false,
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

        ], 2);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'signal'
        ], 3);
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
                if (array_key_exists('battery', $data)) {
                    $this->SetValue('Battery', $data['battery']);
                }
                if (array_key_exists('illumination', $data)) {
                    $this->SetValue('Illumination', $data['illumination']);
                }
                if (array_key_exists('window', $data)) {
                    $this->SetValue('Contact', boolval($data['window']));
                }
                if (array_key_exists('rotation', $data)) {
                    $this->SetValue('Rotation', $data['rotation']);
                }

            }
        }
    }
}
