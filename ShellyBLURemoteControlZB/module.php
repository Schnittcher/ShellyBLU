<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyBLURemoteControlZB extends IPSModule
{
    use DebugHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterPropertyString('Topic', '');

        $this->RegisterVariableInteger('Channel', $this->Translate('Channel'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'list-ol',
            'SUFFIX'         => '',
        ], 0);

        $this->RegisterVariableInteger('ButtonLeft', $this->Translate('Button left'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'arrow-left',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 1);

        $this->RegisterVariableInteger('ButtonRight', $this->Translate('Button right'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'arrow-right',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 2);

        $this->RegisterVariableInteger('Wheel', $this->Translate('Wheel'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'arrows-up-down',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Up'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 2,  'IntervalMaxValue' => 2, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Down'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 3);

        $this->RegisterVariableInteger('WheelSteps', $this->Translate('Wheel Steps'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'ellipsis-vertical',
            'SUFFIX'         => ''
        ], 4);

        $this->RegisterVariableFloat('MagicWandX', $this->Translate('Magic Wand X-Axis'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'rotate',
            'SUFFIX'         => ' °',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.1,
        ], 5);

        $this->RegisterVariableFloat('MagicWandY', $this->Translate('Magic Wand Y-Axis'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'rotate',
            'SUFFIX'         => ' °',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.1,
        ], 6);

        $this->RegisterVariableFloat('MagicWandZ', $this->Translate('Magic Wand Z-Axis'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'rotate',
            'SUFFIX'         => ' °',
            'DIGITS'         => 1,
            'PERCENTAGE'     => false,
            'STEP_SIZE'      => 0.1,
        ], 7);

        $this->RegisterVariableInteger('Battery', $this->Translate('Battery'), [
           'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
           'ICON'           => 'battery-full',
           'SUFFIX'         => ' %',
        ], 8);
        $this->RegisterVariableString('Gateway', $this->Translate('Gateway'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'server',
            'SUFFIX'         => '',

        ], 9);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'signal'
        ], 10);

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
                if (array_key_exists('button', $data)) {
                    $this->SetValue('ButtonLeft', $data['button'][0]);
                    $this->SetValue('ButtonRight', $data['button'][1]);
                }
                if (array_key_exists('channel', $data)) {
                    $this->SetValue('Channel', $data['channel'] + 1); // Kanal wird in MQTT mit 0-3 übergeben, in IPS soll es 1-4 sein
                }
                if (array_key_exists('dimmer', $data)) {
                    $this->SetValue('Wheel', $data['dimmer']);
                }
                if (array_key_exists('dimmersteps', $data)) {
                    $this->SetValue('WheelSteps', $data['dimmersteps']);
                }
                if (array_key_exists('rotation', $data)) {
                    $this->SetValue('MagicWandX', $data['rotation'][0]);
                    $this->SetValue('MagicWandY', $data['rotation'][1]);
                    $this->SetValue('MagicWandZ', $data['rotation'][2]);
                }

            }
        }
    }
}
