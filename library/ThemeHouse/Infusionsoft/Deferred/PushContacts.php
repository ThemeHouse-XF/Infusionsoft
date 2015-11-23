<?php

class ThemeHouse_Infusionsoft_Deferred_PushContacts extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 30
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        /* @var $userModel XenForo_Model_User */
        $userModel = XenForo_Model::create('XenForo_Model_User');

        /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
        $contactServiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');

        $userIds = $userModel->getUserIdsInRange($data['position'], $data['batch']);
        if (sizeof($userIds) == 0) {
            return true;
        }

        $data['position'] = $contactServiceModel->syncContacts($userIds);

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