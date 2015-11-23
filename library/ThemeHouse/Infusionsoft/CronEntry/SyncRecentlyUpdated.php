<?php

class ThemeHouse_Infusionsoft_CronEntry_SyncRecentlyUpdated
{

    public static function pullContacts()
    {
        /* @var $contactDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
        $contactDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');

        $contacts = $contactDataModel->getRecentlyUpdatedContacts();

        if ($contacts) {
            $contactDataModel->pullContacts($contacts);
        }
    }
}