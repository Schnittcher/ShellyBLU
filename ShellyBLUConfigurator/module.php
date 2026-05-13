<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/DebugHelper.php';

const GUID_BLU_BUTTON1 = '{5E02DB53-B7BD-4479-AC5C-09E7519BD89F}';
const GUID_BLU_BUTTON_TOUGH1 = '{8F1094B5-E729-55F1-B5C1-7AA82BD9B02E}';
const GUID_BLU_BUTTON_TOUGH1_ZB = '{4AABB6F7-1732-86B4-410D-8B42F063D71D}';
const GUID_BLU_DOOR_WINDOW = '{3551089F-4CDF-4440-B7FA-3ACB88CAD23F}';
const GUID_BLU_HT = '{C077278B-316D-7027-CA62-5D4EBDCE1769}';
const GUID_BLU_HT_DISPLAY_ZB = '{FD518ADF-BD16-BDA6-E168-A0CDA4FE67B5}';
const GUID_BLU_HT_ZB = '{805FB2B8-BC00-A62A-FCE6-EF44B2EEA98D}';
const GUID_BLU_MOTION = '{2F6CA178-2817-4F78-A88B-1783997CEC0E}';
const GUID_BLU_RC_BUTTON4 = '{C99EAB02-DEF7-25CF-6453-5C8F1E3A27B7}';
const GUID_BLU_WALL_SWITCH = '{C99EAB02-DEF7-25CF-6453-5C8F1E3A27B7}';
const GUID_BLU_ECOWITT_WS90 = '{B2BFB2FB-48DE-BF02-34E0-0A8C86E71CF6}';
const GUID_BLU_WALL_SWITCH4 = '{52E13936-2A63-37AE-34A3-A0005D46591E}';
const GUID_BLU_REMOTE_CONTROL_ZB = '{F7E189C8-7AA4-AAB5-9D63-61CC253BB5F3}';

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
        if (array_key_exists('service_data', $Payload)) {
            $data = $Payload['service_data'];
            $found = array_filter(self::SENSORS, fn ($sensor) => array_key_exists($sensor, $data));

            $variables = [];
            foreach ($found as $sensor) {
                $value = $data[$sensor];
                if (is_array($value)) {
                    foreach ($value as $index => $v) {
                        $variables[] = $sensor . '_' . ($index + 1); // z.B. temperature_1, temperature_2, temperature_3
                    }
                } else {
                    $variables[] = $sensor; // z.B. battery
                }
            }

            $Devices[$Payload['addr']] = $variables;



        }
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
                $AddValue = [
                    'name'                   => '',
                    'BLUAddress'                   => $BLUAddress,
                    'variables'               => $Device,
                    'instanceID'             => $instanceID,
                    'create'             => [
                      'Shelly BLU RC Button 4' => [
                        'moduleID' => GUID_BLU_RC_BUTTON4,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                      ],
                       'Shelly BLU Button 1' => [
                        'moduleID' => GUID_BLU_BUTTON1,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                      ]
                    ],
                    'Shelly BLU Button Tough 1 ZB' => [
                        'moduleID' => GUID_BLU_BUTTON_TOUGH1_ZB,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU Button Tough 1' => [
                        'moduleID' => GUID_BLU_BUTTON_TOUGH1,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU Door/Window' => [
                        'moduleID' => GUID_BLU_DOOR_WINDOW,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU Motion' => [
                        'moduleID' => GUID_BLU_MOTION,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU H&T' => [
                        'moduleID' => GUID_BLU_HT,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU H&T Display ZB' => [
                        'moduleID' => GUID_BLU_HT_DISPLAY_ZB,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLURemote Control ZB' => [
                        'moduleID' => GUID_BLU_REMOTE_CONTROL_ZB,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU H&T ZB' => [
                        'moduleID' => GUID_BLU_HT_ZB,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU Ecowitt WS90' => [
                        'moduleID' => GUID_BLU_ECOWITT_WS90,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
                    ],
                    'Shelly BLU Wall Switch 4' => [
                        'moduleID' => GUID_BLU_WALL_SWITCH4,
                        'info' => $BLUAddress,
                        'configuration' => [
                            'Topic' => self::topic . '/' . $BLUAddress,
                        ]
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
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_BUTTON1);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_BUTTON_TOUGH1);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_BUTTON_TOUGH1_ZB);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_DOOR_WINDOW);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_DOOR_WINDOW);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_HT);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_HT_DISPLAY_ZB);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_HT_ZB);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_MOTION);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_RC_BUTTON4);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_WALL_SWITCH);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_ECOWITT_WS90);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_WALL_SWITCH4);
        $InstanceIDs[] = IPS_GetInstanceListByModuleID(GUID_BLU_REMOTE_CONTROL_ZB);


        foreach ($InstanceIDs as $IDs) {
            foreach ($IDs as $id) {
                if (strtolower(IPS_GetProperty($id, 'Topic')) ==  $Topic) {
                    if (IPS_GetInstance($id)['ConnectionID'] === IPS_GetInstance($this->InstanceID)['ConnectionID']) {
                        return $id;
                    }
                }
            }
        }
        return 0;
    }
}
