<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php';
require_once __DIR__ . '/../libs/local.php';

class Pulsecounter extends IPSModule
{
    use Pulsecounter\StubsCommonLib;
    use PulsecounterLocalLib;

    public function __construct(string $InstanceID)
    {
        parent::__construct($InstanceID);

        $this->CommonContruct(__DIR__);
    }

    public function __destruct()
    {
        $this->CommonDestruct();
    }

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyInteger('counter_1', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_2', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_3', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_4', self::$PULSECOUNTER_UNDEF);

        $this->RegisterPropertyFloat('condensing_value', 0);

        $this->RegisterAttributeString('UpdateInfo', json_encode([]));
        $this->RegisterAttributeString('ModuleStats', json_encode([]));

        $this->InstallVarProfiles(false);

        $this->RequireParent('{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->MaintainReferences();

        if ($this->CheckPrerequisites() != false) {
            $this->MaintainStatus(self::$IS_INVALIDPREREQUISITES);
            return;
        }

        if ($this->CheckUpdate() != false) {
            $this->MaintainStatus(self::$IS_UPDATEUNCOMPLETED);
            return;
        }

        if ($this->CheckConfiguration() != false) {
            $this->MaintainStatus(self::$IS_INVALIDCONFIG);
            return;
        }

        $condensing_value = $this->ReadPropertyFloat('condensing_value');
        $vpos = 1;

        for ($i = 1; $i <= 4; $i++) {
            $ident_a = 'w_counter_' . $i;
            $ident_b = 'w_power_' . $i;
            $ident_c = 'w_aux_' . $i;

            $desc_a = '';
            $desc_b = '';
            $desc_c = '';

            $prof_a = '';
            $prof_b = '';
            $prof_c = '';

            $use_a = false;
            $use_b = false;
            $use_c = false;

            $type = $this->ReadPropertyInteger('counter_' . $i);
            switch ($type) {
                case self::$PULSECOUNTER_ELECTRICITY:
                    $desc_a = 'Electric meter';
                    $prof_a = 'Pulsecounter.KWh';
                    $use_a = true;

                    $desc_b = 'Electricity usage';
                    $prof_b = 'Pulsecounter.KW';
                    $use_b = true;
                    break;
                case self::$PULSECOUNTER_GAS:
                    $desc_a = 'Gas meter';
                    $prof_a = 'Pulsecounter.m3';
                    $use_a = true;

                    $desc_b = 'Gas consumption';
                    $prof_b = 'Pulsecounter.KW';
                    $use_b = true;

                    if ($condensing_value > 0) {
                        $desc_c = 'Gas energy';
                        $prof_c = 'Pulsecounter.KWh';
                        $use_c = true;
                    }
                    break;
                case self::$PULSECOUNTER_WATER:
                    $use = true;
                    $desc_a = 'Water meter';
                    $prof_a = 'Pulsecounter.m3';
                    $use_a = true;

                    $desc_b = 'Water consumption';
                    $prof_b = 'Pulsecounter.m3_h';
                    $use_b = true;
                    break;
                default:
                    break;
            }
            $this->MaintainVariable($ident_a, $this->Translate($desc_a), VARIABLETYPE_FLOAT, $prof_a, $vpos++, $use_a);
            $this->MaintainVariable($ident_b, $this->Translate($desc_b), VARIABLETYPE_FLOAT, $prof_b, $vpos++, $use_b);
            $this->MaintainVariable($ident_c, $this->Translate($desc_c), VARIABLETYPE_FLOAT, $prof_c, $vpos++, $use_c);
        }

        $vpos = 100;

        $this->MaintainVariable('LastMeasurement', $this->Translate('Last measurement'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);
        $this->MaintainVariable('LastUpdate', $this->Translate('Last update'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);
        $this->MaintainVariable('Uptime', $this->Translate('Uptime'), VARIABLETYPE_INTEGER, 'Pulsecounter.sec', $vpos++, true);
        $this->MaintainVariable('WifiStrength', $this->Translate('wifi-signal'), VARIABLETYPE_INTEGER, 'Pulsecounter.Wifi', $vpos++, true);

        $this->MaintainStatus(IS_ACTIVE);
    }

    private function GetFormElements()
    {
        $formElements = $this->GetCommonFormElements('Pulsecounter');

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            return $formElements;
        }

        $opts = [
            [
                'caption' => $this->Translate('unused'),
                'value'   => self::$PULSECOUNTER_UNDEF,
            ],
            [
                'caption' => $this->Translate('Electricity'),
                'value'   => self::$PULSECOUNTER_ELECTRICITY,
            ],
            [
                'caption' => $this->Translate('Gas'),
                'value'   => self::$PULSECOUNTER_GAS,
            ],
            [
                'caption' => $this->Translate('Water'),
                'value'   => self::$PULSECOUNTER_WATER,
            ],
        ];

        $formElements[] = [
            'type'    => 'Select',
            'options' => $opts,
            'name'    => 'counter_1',
            'caption' => 'Counter 1',
        ];
        $formElements[] = [
            'type'    => 'Select',
            'options' => $opts,
            'name'    => 'counter_2',
            'caption' => 'Counter 2',
        ];
        $formElements[] = [
            'type'    => 'Select',
            'options' => $opts,
            'name'    => 'counter_3',
            'caption' => 'Counter 3',
        ];
        $formElements[] = [
            'type'    => 'Select',
            'options' => $opts,
            'name'    => 'counter_4',
            'caption' => 'Counter 4',
        ];

        $formElements[] = [
            'type'    => 'NumberSpinner',
            'minimum' => 0,
            'digits'  => 4,
            'suffix'  => 'kWh/m3',
            'name'    => 'condensing_value',
            'caption' => 'Gas condensing value',
        ];

        return $formElements;
    }

    private function GetFormActions()
    {
        $formActions = [];

        if ($this->GetStatus() == self::$IS_UPDATEUNCOMPLETED) {
            $formActions[] = $this->GetCompleteUpdateFormAction();

            $formActions[] = $this->GetInformationFormAction();
            $formActions[] = $this->GetReferencesFormAction();

            return $formActions;
        }

        $formActions[] = [
            'type'      => 'ExpansionPanel',
            'caption'   => 'Expert area',
            'expanded'  => false,
            'items'     => [
                $this->GetInstallVarProfilesFormItem(),
                [
                    'type'      => 'Button',
                    'caption'   => '(Re-)build archive-data for "gas energie" from "gas counter"',
                    'onClick'   => 'IPS_RequestAction(' . $this->InstanceID . ', "RecalcGasEnergy", "");',
                    'confirm'   => 'This clears the values of "gas energy" and re-creates them new from "gas counter" and "Gas condensing value"',
                ],
            ],
        ];

        $formActions[] = $this->GetInformationFormAction();
        $formActions[] = $this->GetReferencesFormAction();

        return $formActions;
    }

    private function LocalRequestAction($ident, $value)
    {
        $r = true;
        switch ($ident) {
            case 'RecalcGasEnergy':
                $this->RecalcGasEnergy();
                break;
            default:
                $r = false;
                break;
        }
        return $r;
    }

    public function RequestAction($ident, $value)
    {
        if ($this->LocalRequestAction($ident, $value)) {
            return;
        }
        if ($this->CommonRequestAction($ident, $value)) {
            return;
        }
        switch ($ident) {
            default:
                $this->SendDebug(__FUNCTION__, 'invalid ident ' . $ident, 0);
                break;
        }
    }

    public function ReceiveData($msg)
    {
        $jmsg = json_decode($msg, true);
        $data = $jmsg['Buffer'];

        switch ((int) $jmsg['Type']) {
            case 0: /* Data */
                $this->SendDebug(__FUNCTION__, $jmsg['ClientIP'] . ':' . $jmsg['ClientPort'] . ' => received: ' . $data, 0);
                $rdata = $this->GetMultiBuffer('Data');
                if (substr($data, -1) == chr(4)) {
                    $ndata = $rdata . substr($data, 0, -1);
                } else {
                    $ndata = $rdata . $data;
                }
                break;
            case 1: /* Connected */
                $this->SendDebug(__FUNCTION__, $jmsg['ClientIP'] . ':' . $jmsg['ClientPort'] . ' => connected', 0);
                $ndata = '';
                break;
            case 2: /* Disconnected */
                $this->SendDebug(__FUNCTION__, $jmsg['ClientIP'] . ':' . $jmsg['ClientPort'] . ' => disonnected', 0);
                $rdata = $this->GetMultiBuffer('Data');
                if ($rdata != '') {
                    $jdata = json_decode($rdata, true);
                    if ($jdata == '') {
                        $this->SendDebug(__FUNCTION__, 'json_error=' . json_last_error_msg() . ', data=' . $rdata, 0);
                    } else {
                        $this->ProcessData($jdata);
                    }
                }
                $ndata = '';
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'unknown Type, jmsg=' . print_r($jmsg, true), 0);
                break;
        }
        $this->SetMultiBuffer('Data', $ndata);
    }

    private function ProcessData($jdata)
    {
        $this->SendDebug(__FUNCTION__, 'data=' . print_r($jdata, true), 0);

        $modultyp = $this->GetArrayElem($jdata, 'modultyp', '');

        $systeminfo = $this->GetArrayElem($jdata, 'Systeminfo', '');
        $this->SendDebug(__FUNCTION__, 'Systeminfo=' . print_r($systeminfo, true), 0);

        $s = $this->GetArrayElem($jdata, 'Systeminfo.zeitpunkt', '');
        if (preg_match('#^([0-9]+)\.([0-9]+)\.([0-9]+) /([0-9]+)h([0-9]+)$#', $s, $r)) {
            $tstamp = strtotime($r[1] . '-' . $r[2] . '-' . $r[3] . ' ' . $r[4] . ':' . $r[5] . ':00');
        } else {
            $this->SendDebug(__FUNCTION__, 'unable to decode date "' . $s . '"', 0);
            $tstamp = 0;
        }
        $this->SetValue('LastMeasurement', $tstamp);

        $uptime = $this->GetArrayElem($jdata, 'Systeminfo.sec_seit_reset', '');
        $this->SetValue('Uptime', $uptime);

        $rssi = $this->GetArrayElem($jdata, 'Systeminfo.WLAN_Signal_dBm', '');
        $this->SetValue('WifiStrength', $rssi);

        $this->SendDebug(__FUNCTION__, 'modultyp=' . $modultyp . ', measure=' . date('d.m.Y H:i:s', $tstamp) . ', rssi=' . $rssi . ', uptime=' . $uptime . 's', 0);

        $condensing_value = $this->ReadPropertyFloat('condensing_value');

        $vars = $this->GetArrayElem($jdata, 'vars', '');
        $this->SendDebug(__FUNCTION__, 'vars=' . print_r($vars, true), 0);
        foreach ($vars as $var) {
            $ident = $this->GetArrayElem($var, 'homematic_name', '');

            // Unterschied bei Firmware (Bsp) alt: w_counter_1 neu: w_counter1
            $ident = preg_replace('/w_(counter|power)(\d+)/i', 'w_${1}_${2}', $ident);

            $value = $this->GetArrayElem($var, 'value', '');

            $found = false;
            $skip = false;
            for ($i = 1; $i <= 4; $i++) {
                $ident_a = 'w_counter_' . $i;
                $ident_b = 'w_power_' . $i;
                $ident_c = 'w_aux_' . $i;

                $type = $this->ReadPropertyInteger('counter_' . $i);
                if ($type == self::$PULSECOUNTER_UNDEF) {
                    continue;
                }

                if (in_array($ident, [$ident_a, $ident_b])) {
                    if (in_array((string) $value, ['', 'inf', 'nan'])) {
                        $skip = true;
                    } else {
                        $this->SetValue($ident, $value);
                        if ($type == self::$PULSECOUNTER_GAS && $ident == $ident_a && $condensing_value > 0) {
                            $this->SetValue($ident_c, $value * $condensing_value);
                        }
                    }
                    $found = true;
                    break;
                }
            }
            if ($found) {
                if ($skip) {
                    $this->SendDebug(__FUNCTION__, 'skip ident=' . $ident . ', value=' . $value . ' => no value', 0);
                } else {
                    $this->SendDebug(__FUNCTION__, 'use ident=' . $ident . ', value=' . $value, 0);
                }
            } else {
                $this->SendDebug(__FUNCTION__, 'ignore ident=' . $ident . ', value=' . $value, 0);
            }
        }

        $this->SetValue('LastUpdate', time());
    }

    private function RecalcGasEnergy()
    {
        $archivID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
        $avars = AC_GetAggregationVariables($archivID, false);

        $condensing_value = $this->ReadPropertyFloat('condensing_value');

        $msg = '';

        for ($i = 1; $i <= 4; $i++) {
            $type = $this->ReadPropertyInteger('counter_' . $i);
            if ($type != self::$PULSECOUNTER_GAS) {
                continue;
            }

            $ident_src = 'w_counter_' . $i;
            @$varID_src = $this->GetIDForIdent($ident_src);
            if ($varID_src == false) {
                $s = 'missing source variable "' . $ident_src . '"';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }
            $this->SendDebug(__FUNCTION__, 'source variable "' . $ident_src . '" has id ' . $varID_src, 0);
            if (AC_GetLoggingStatus($archivID, $varID_src) == false || AC_GetAggregationType($archivID, $varID_src) != 1 /* Zähler */) {
                $s = 'missing source variable "' . $ident_src . '" isn\'t logged as counter';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }

            $ident_dst = 'w_aux_' . $i;
            @$varID_dst = $this->GetIDForIdent($ident_dst);
            if ($varID_dst == false) {
                $s = 'missing destination variable "' . $ident_dst . '"';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }
            $this->SendDebug(__FUNCTION__, 'destination variable "' . $ident_dst . '" has id ' . $varID_dst, 0);
            if (AC_GetLoggingStatus($archivID, $varID_dst) == false || AC_GetAggregationType($archivID, $varID_dst) != 1 /* Zähler */) {
                $s = 'missing destination variable "' . $ident_dst . '" isn\'t logged as counter';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }

            $this->SendDebug(__FUNCTION__, 'delete all values from destination variable "' . $ident_dst . '"', 0);
            $old_num = AC_DeleteVariableData($archivID, $varID_dst, 0, time());
            $msg .= 'deleted all (' . $old_num . ') from destination variable "' . $ident_dst . '"' . PHP_EOL;

            $firstTime = 0;
            $lastTime = 0;
            $dst_num = 0;
            foreach ($avars as $avar) {
                if ($avar['VariableID'] == $varID_src) {
                    $firstTime = $avar['FirstTime'];
                    $lastTime = $avar['LastTime'];
                    $recordCount = $avar['RecordCount'];
                    $this->SendDebug(__FUNCTION__, 'archiv=' . print_r($avar, true), 0);

                    $msg .= 'source variable "' . $ident_src . '" has ' . $recordCount . ' values from ' . date('d.m.Y H:i:s', $firstTime) . ' to ' . date('d.m.Y H:i:s', $lastTime) . PHP_EOL;

                    break;
                }
            }
            $dst_val = [];
            for ($start = $firstTime; $start < $lastTime; $start = $end + 1) {
                $end = $start + (24 * 60 * 60 * 30) - 1;

                $src_val = AC_GetLoggedValues($archivID, $varID_src, $start, $end, 0);
                foreach ($src_val as $val) {
                    $dst_val[] = [
                        'TimeStamp' => $val['TimeStamp'],
                        'Value'     => $val['Value'] * $condensing_value,
                    ];
                }
                $this->SendDebug(__FUNCTION__, 'start=' . date('d.m.Y H:i:s', $start) . ', end=' . date('d.m.Y H:i:s', $end) . ', count=' . count($src_val), 0);
            }
            $dst_num = count($dst_val);

            $this->SendDebug(__FUNCTION__, 'add ' . $dst_num . ' values', 0);
            if (AC_AddLoggedValues($archivID, $varID_dst, $dst_val) == false) {
                $s = 'add ' . $dst_num . ' values to destination variable "' . $ident_dst . '" failed';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }

            $msg .= 'added ' . $dst_num . ' values to destination variable "' . $ident_dst . '"' . PHP_EOL;

            $this->SendDebug(__FUNCTION__, 're-aggregate variable', 0);
            if (AC_ReAggregateVariable($archivID, $varID_dst) == false) {
                $s = 're-aggregate destination variable "' . $ident_dst . '" failed';
                $this->SendDebug(__FUNCTION__, $s, 0);
                $msg .= $s;
                continue;
            }

            $msg .= 'destination variable "' . $ident_dst . '" re-aggregated' . PHP_EOL;
        }
        $this->PopupMessage($msg);
    }
}
