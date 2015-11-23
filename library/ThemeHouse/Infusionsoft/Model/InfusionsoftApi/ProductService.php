<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ProductService extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi
{

    public function deactivateCreditCard($creditCardId)
    {
        return $this->call('ProductService.deactivateCreditCard', array(
            $creditCardId
        ));
    }
}