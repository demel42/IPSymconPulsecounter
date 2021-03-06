<?php

declare(strict_types=1);

trait PulsecounterLocalLib
{
    public static $PULSECOUNTER_UNDEF = -1;
    public static $PULSECOUNTER_ELECTRICITY = 0;
    public static $PULSECOUNTER_GAS = 1;
    public static $PULSECOUNTER_WATER = 2;

    private function GetFormStatus()
    {
        $formStatus = [];
        $formStatus[] = ['code' => IS_CREATING, 'icon' => 'inactive', 'caption' => 'Instance getting created'];
        $formStatus[] = ['code' => IS_ACTIVE, 'icon' => 'active', 'caption' => 'Instance is active'];
        $formStatus[] = ['code' => IS_DELETING, 'icon' => 'inactive', 'caption' => 'Instance is deleted'];
        $formStatus[] = ['code' => IS_INACTIVE, 'icon' => 'inactive', 'caption' => 'Instance is inactive'];
        $formStatus[] = ['code' => IS_NOTCREATED, 'icon' => 'inactive', 'caption' => 'Instance is not created'];

        return $formStatus;
    }
}
