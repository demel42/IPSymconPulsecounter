<?php

declare(strict_types=1);

trait PulsecounterLocalLib
{
    private function GetFormStatus()
    {
        $formStatus = $this->GetCommonFormStatus();

        return $formStatus;
    }

    public static $STATUS_INVALID = 0;
    public static $STATUS_VALID = 1;
    public static $STATUS_RETRYABLE = 2;

    private function CheckStatus()
    {
        switch ($this->GetStatus()) {
            case IS_ACTIVE:
                $class = self::$STATUS_VALID;
                break;
            default:
                $class = self::$STATUS_INVALID;
                break;
        }

        return $class;
    }

    public static $PULSECOUNTER_UNDEF = -1;
    public static $PULSECOUNTER_ELECTRICITY = 0;
    public static $PULSECOUNTER_GAS = 1;
    public static $PULSECOUNTER_WATER = 2;

    public function InstallVarProfiles(bool $reInstall = false)
    {
        if ($reInstall) {
            $this->SendDebug(__FUNCTION__, 'reInstall=' . $this->bool2str($reInstall), 0);
        }

        $this->CreateVarProfile('Pulsecounter.Wifi', VARIABLETYPE_INTEGER, ' dBm', 0, 0, 0, 0, 'Intensity', [], $reInstall);
        $this->CreateVarProfile('Pulsecounter.sec', VARIABLETYPE_INTEGER, ' s', 0, 0, 0, 0, 'Clock', [], $reInstall);

        $this->CreateVarProfile('Pulsecounter.KWh', VARIABLETYPE_FLOAT, ' KWh', 0, 0, 0, 1, '', [], $reInstall);
        $this->CreateVarProfile('Pulsecounter.KW', VARIABLETYPE_FLOAT, ' KW', 0, 0, 0, 1, '', [], $reInstall);
        $this->CreateVarProfile('Pulsecounter.m3_h', VARIABLETYPE_FLOAT, ' m3/h', 0, 0, 0, 1, '', [], $reInstall);
        $this->CreateVarProfile('Pulsecounter.m3', VARIABLETYPE_FLOAT, ' m3', 0, 0, 0, 1, '', [], $reInstall);
    }
}
