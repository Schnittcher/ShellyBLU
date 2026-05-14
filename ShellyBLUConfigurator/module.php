<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';

class ShellyBLUConfigurator extends IPSModule
{
    use DebugHelper;
    private const topic = 'symcon/shellyblu';
    private const SENSORS = [
    'battery',
    'battery_low',
    'light',
    'humidity',
    'illuminance',
    'motion',
    'vibration',
    'window',
    'button',
    'rotation',
    'distance_mm',
    'temperature',
    'rain_status'
];

    private const MODELS = [
        "SBBT-002C"   => [
            'name' => "Shelly BLU Button1",
            'guid' => '{5E02DB53-B7BD-4479-AC5C-09E7519BD89F}',
        ],
        "SBBT-102C" => [
            'name' => "Shelly BLU Button Tough 1 ZB",
            'guid' => '{4AABB6F7-1732-86B4-410D-8B42F063D71D}',
        ],
        "SBDW-002C"   => [
            'name' => "Shelly BLU DoorWindow",
            'guid' => '{3551089F-4CDF-4440-B7FA-3ACB88CAD23F}',
        ],
        "SBHT-003C"   => [
            'name' => "Shelly BLU HT",
            'guid' => '{C077278B-316D-7027-CA62-5D4EBDCE1769}',
        ],
        "SBMO-003Z"   => [
            'name' => "Shelly BLU Motion",
            'guid' => '{2F6CA178-2817-4F78-A88B-1783997CEC0E}',
        ],
        "SBBT-004CEU" => [
            'name' => "Shelly BLU Wall Switch 4",
            'guid' => '{52E13936-2A63-37AE-34A3-A0005D46591E}',
        ],
        "SBBT-004CUS" => [
            'name' => "Shelly BLU RC Button 4",
            'guid' => '{C99EAB02-DEF7-25CF-6453-5C8F1E3A27B7}',
        ],
        "SBTR-001AEU" => [
            'name' => "Shelly BLU TRV",
            'guid' => null,
        ],
        "SBRC-005B"   => [
            'name' => "Shelly BLU Remote",
            'guid' => '{F7E189C8-7AA4-AAB5-9D63-61CC253BB5F3}',
        ],
        "SBWS-90CM"   => [
            'name' => "Shelly BLU Weather Station",
            'guid' => '{B2BFB2FB-48DE-BF02-34E0-0A8C86E71CF6}',
        ],
        "SBHT-103C"   => [
            'name' => "Shelly BLU H&T Display ZB",
            'guid' => '{FD518ADF-BD16-BDA6-E168-A0CDA4FE67B5}',
        ],
        "SBHT-203C"   => [
            'name' => "Shelly BLU H&T ZB",
            'guid' => '{805FB2B8-BC00-A62A-FCE6-EF44B2EEA98D}',
        ],
        "SBDI-003E"   => [
            'name' => "Shelly BLU Distance",
            'guid' => null,
        ],
    ];


    public function Create()
    {
        //Never delete this line!
        parent::Create();
        $this->ConnectParent('{C6D2AEB3-6E1F-4B2E-8E69-3A1A00246850}');
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        $Filter = self::topic;
        $this->SetReceiveDataFilter('.*' . $Filter . '.*');

        $this->RegisterAttributeString('Devices', '{}');
    }

    public function ReceiveData($JSONString)
    {
        $Buffer = json_decode($JSONString, true);
        $this->SendDebug('JSON', $Buffer, 0);

        $Devices = json_decode($this->ReadAttributeString('Devices'), true);

        $Payload = json_decode($Buffer['Payload'], true);

        $model = $Payload['model'] ?? '';
        if (str_starts_with($model, 'SBTR')) {
            return;
        }
        $Devices[$Payload['addr']] = $Payload['model'];

        $this->WriteAttributeString('Devices', json_encode($Devices));
    }

    public function GetConfigurationForm()
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

        if (floatval(IPS_GetKernelVersion()) < 5.3) {
            return json_encode($Form);
        }

        $Values = [];
        $Devices = json_decode($this->ReadAttributeString('Devices'), true);
        $this->SendDebug(__FUNCTION__ . ' Devices', $Devices, 0);
        if (count($Devices) > 0) {
            foreach ($Devices as $BLUAddress => $Device) {
                $instanceID = $this->getShellyInstances(self::topic . '/' . $BLUAddress);
                IPS_LogMessage('test', print_r($Device, true));
                $guid = self::MODELS[$Device]['guid'] ?? null;
                $name = self::MODELS[$Device]['name'] ?? $Device['name'] ?? $Device;
                $AddValue = [
                            'name'       => '',
                            'BLUAddress' => $BLUAddress,
                            'model'      => $name,
                            'instanceID' => $instanceID,
                            'create'     => [
                                    'moduleID'      => $guid,
                                    'info'          => $BLUAddress,
                                    'configuration' => [
                                        'Topic' => self::topic . '/' . $BLUAddress,
                                    ]
                            ]
                            ];

                $Values[] = $AddValue;
            }
            $Form['actions'][0]['values'] = $Values;
        }
        return json_encode($Form);
    }

    private function getShellyInstances($Topic)
    {
        $InstanceIDs = [];
        foreach (self::MODELS as $model) {
            if ($model['guid'] !== null) {
                $InstanceIDs[] = IPS_GetInstanceListByModuleID($model['guid']);
            }
        }

        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (strtolower(IPS_GetProperty($id, 'Topic')) == $Topic) {
                    if (IPS_GetInstance($id)['ConnectionID'] === IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                        return $id;
                    }
                }
            }
        }
        return 0;
    }
}
