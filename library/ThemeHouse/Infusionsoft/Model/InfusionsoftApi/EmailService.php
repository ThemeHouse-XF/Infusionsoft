<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_EmailService extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi
{

    public function optIn($email, $optInReason)
    {
        $query = array(
            $email,
            $optInReason
        );
        
        return $this->call('APIEmailService.optIn', $query);
    }

    public function optOut($email, $optOutReason)
    {
        $query = array(
            $email,
            $optOutReason
        );
        
        return $this->call('APIEmailService.optOut', $query);
    }
}