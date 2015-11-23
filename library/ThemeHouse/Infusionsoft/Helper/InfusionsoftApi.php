<?php

class ThemeHouse_Infusionsoft_Helper_InfusionsoftApi
{
    
    public static function getDateAsTimestamp($date)
    {
        return strtotime($date . '-05:00');
    }
}