<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/common.php'; // globale Funktionen
require_once __DIR__ . '/../libs/local.php';  // lokale Funktionen

class Pulsecounter extends IPSModule
{
    use PulsecounterCommonLib;
    use PulsecounterLocalLib;

    public function Create()
    {
        parent::Create();

        $this->RegisterPropertyInteger('counter_1', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_2', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_3', self::$PULSECOUNTER_UNDEF);
        $this->RegisterPropertyInteger('counter_4', self::$PULSECOUNTER_UNDEF);

        $this->CreateVarProfile('Pulsecounter.Wifi', VARIABLETYPE_INTEGER, ' dBm', 0, 0, 0, 0, 'Intensity');
        $this->CreateVarProfile('Pulsecounter.sec', VARIABLETYPE_INTEGER, ' s', 0, 0, 0, 0, 'Clock');

        $this->CreateVarProfile('Pulsecounter.KWh', VARIABLETYPE_FLOAT, ' KWh', 0, 0, 0, 1, '');
        $this->CreateVarProfile('Pulsecounter.KW', VARIABLETYPE_FLOAT, ' KW', 0, 0, 0, 1, '');
        $this->CreateVarProfile('Pulsecounter.m3', VARIABLETYPE_FLOAT, ' m3', 0, 0, 0, 1, '');
        $this->CreateVarProfile('Pulsecounter.m3_h', VARIABLETYPE_FLOAT, ' m3/h', 0, 0, 0, 1, '');

        $this->RequireParent('{8062CF2B-600E-41D6-AD4B-1BA66C32D6ED}');
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $status = IS_ACTIVE;

        $vpos = 1;

        for ($i = 1; $i <= 4; $i++) {
            $ident_a = 'w_counter_' . $i;
            $ident_b = 'w_power_' . $i;

            $desc_a = '';
            $desc_b = '';

            $prof_a = '';
            $prof_b = '';

            $use_a = false;
            $use_b = false;

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
        }

        $vpos = 100;

        $this->MaintainVariable('LastMeasurement', $this->Translate('Last measurement'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);
        $this->MaintainVariable('LastUpdate', $this->Translate('Last update'), VARIABLETYPE_INTEGER, '~UnixTimestamp', $vpos++, true);
        $this->MaintainVariable('Uptime', $this->Translate('Uptime'), VARIABLETYPE_INTEGER, 'Pulsecounter.sec', $vpos++, true);
        $this->MaintainVariable('WifiStrength', $this->Translate('wifi-signal'), VARIABLETYPE_INTEGER, 'Pulsecounter.Wifi', $vpos++, true);

        $this->SetStatus($status);
    }

    protected function GetFormElements()
    {
        $formElements = [];
        $formElements[] = ['type' => 'Label', 'caption' => 'Pulsecounter'];

        $opts = [];
        $opts[] = ['caption' => $this->Translate('unused'), 'value'   => self::$PULSECOUNTER_UNDEF];
        $opts[] = ['caption' => $this->Translate('Electricity'), 'value'   => self::$PULSECOUNTER_ELECTRICITY];
        $opts[] = ['caption' => $this->Translate('Gas'), 'value'   => self::$PULSECOUNTER_GAS];
        $opts[] = ['caption' => $this->Translate('Water'), 'value'   => self::$PULSECOUNTER_WATER];

        $formElements[] = [
            'type'    => 'Select',
            'name'    => 'counter_1',
            'caption' => 'Counter 1',
            'options' => $opts
        ];
        $formElements[] = [
            'type'    => 'Select',
            'name'    => 'counter_2',
            'caption' => 'Counter 2',
            'options' => $opts
        ];
        $formElements[] = [
            'type'    => 'Select',
            'name'    => 'counter_3',
            'caption' => 'Counter 3',
            'options' => $opts
        ];
        $formElements[] = [
            'type'    => 'Select',
            'name'    => 'counter_4',
            'caption' => 'Counter 4',
            'options' => $opts
        ];

        return $formElements;
    }

    protected function GetFormActions()
    {
        $formActions = [];

        return $formActions;
    }

    public function ReceiveData($msg)
    {
        $jmsg = json_decode($msg, true);
        $data = utf8_decode($jmsg['Buffer']);

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

        $vars = $this->GetArrayElem($jdata, 'vars', '');
        $this->SendDebug(__FUNCTION__, 'vars=' . print_r($vars, true), 0);
        foreach ($vars as $var) {
            $ident = $this->GetArrayElem($var, 'homematic_name', '');
            $value = $this->GetArrayElem($var, 'value', '');

            $found = false;
            $skip = false;
            for ($i = 1; $i <= 4; $i++) {
                $ident_a = 'w_counter_' . $i;
                $ident_b = 'w_power_' . $i;

                $type = $this->ReadPropertyInteger('counter_' . $i);
                if ($type == self::$PULSECOUNTER_UNDEF) {
                    continue;
                }

                if (in_array($ident, [$ident_a, $ident_b])) {
                    if (in_array((string) $value, ['', 'inf', 'nan'])) {
                        $skip = true;
                    } else {
                        $this->SetValue($ident, $value);
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
}
