<?php

declare(strict_types=1);
require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';
require_once __DIR__ . '/../libs/MQTTHelper.php';

class ShellyBLURCButton4 extends IPSModule
{
    use DebugHelper;
    use MQTTHelper;

    public function Create()
    {
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
        $this->RegisterPropertyString('Topic', '');

        $this->RegisterVariableInteger('Button1', $this->Translate('Button 1'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 2,  'IntervalMaxValue' => 2, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Double'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 3,  'IntervalMaxValue' => 3, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Tripple'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 4,  'IntervalMaxValue' => 4, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Long'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 254,  'IntervalMaxValue' =>  254, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Hold'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 0);

        $this->RegisterVariableInteger('Button2', $this->Translate('Button 2'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 2,  'IntervalMaxValue' => 2, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Double'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 3,  'IntervalMaxValue' => 3, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Tripple'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 4,  'IntervalMaxValue' => 4, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Long'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 254,  'IntervalMaxValue' =>  254, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Hold'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 1);

        $this->RegisterVariableInteger('Button3', $this->Translate('Button 3'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 2,  'IntervalMaxValue' => 2, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Double'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 3,  'IntervalMaxValue' => 3, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Tripple'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 4,  'IntervalMaxValue' => 4, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Long'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 254,  'IntervalMaxValue' =>  254, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Hold'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 2);

        $this->RegisterVariableInteger('Button4', $this->Translate('Button 4'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'sun',
            'SUFFIX'         => '',
            'INTERVALS_ACTIVE' => true,
            'INTERVALS'        => json_encode([
                ['IntervalMinValue' => 1,  'IntervalMaxValue' => 1, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Single'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 2,  'IntervalMaxValue' => 2, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Double'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 3,  'IntervalMaxValue' => 3, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Tripple'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 4,  'IntervalMaxValue' => 4, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Long'), 'IconValue' => '', 'Color' => 0],
                ['IntervalMinValue' => 254,  'IntervalMaxValue' =>  254, 'ConstantActive' => true,  'ConstantValue' => $this->Translate('Hold'), 'IconValue' => '', 'Color' => 0],
            ])
        ], 3);

        $this->RegisterVariableInteger('Battery', $this->Translate('Battery'), [
            'PRESENTATION'   => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'ICON'           => 'battery',
            'SUFFIX'         => ' %',
        ], 4);

        $this->RegisterVariableString('Gateway', $this->Translate('Gateway'), '', 5);
        $this->RegisterVariableInteger('RSSI', $this->Translate('RSSI'), '', 6);

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
                if (array_key_exists('motion', $data)) {
                    $this->SetValue('Motion', boolval($data['motion']));
                }
                if (array_key_exists('illumination', $data)) {
                    $this->SetValue('Illumination', $data['illumination']);
                }
                if (array_key_exists('battery', $data)) {
                    $this->SetValue('Battery', $data['battery']);
                }
                if (array_key_exists('button', $data)) {
                    $this->SetValue('Button1', $data['button'][0]);
                    $this->SetValue('Button2', $data['button'][1]);
                    $this->SetValue('Button3', $data['button'][2]);
                    $this->SetValue('Button4', $data['button'][3]);
                }
            }
        }
    }
}
