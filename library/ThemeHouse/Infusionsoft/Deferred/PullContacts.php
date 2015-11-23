<?php

class ThemeHouse_Infusionsoft_Deferred_PullContacts extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 30
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        /* @var $contactDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
        $contactDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');

        $contacts = $contactDataModel->getContactsInRange($data['position'], $data['batch']);
        if (sizeof($contacts) == 0) {
            return true;
        }

        $data['position'] = $contactDataModel->pullContacts($contacts, $data['position'], $targetRunTime);

        $actionPhrase = new XenForo_Phrase('th_synchronising_infusionsoftapi');
        $typePhrase = new XenForo_Phrase('th_contacts_infusionsoftapi');
        $status = sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

        return $data;
    }

    public function canCancel()
    {
        return true;
    }
}