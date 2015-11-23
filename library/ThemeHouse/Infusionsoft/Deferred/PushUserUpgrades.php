<?php

class ThemeHouse_Infusionsoft_Deferred_PushUserUpgrades extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 30
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        /* @var $userUpgradeModel XenForo_Model_UserUpgrade */
        $userUpgradeModel = XenForo_Model::create('XenForo_Model_UserUpgrade');

        /* @var $productModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product */
        $productModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product');

        $userUpgradeIds = $userUpgradeModel->getUserUpgradeIdsInRange($data['position'], $data['batch']);
        if (sizeof($userUpgradeIds) == 0) {
            return true;
        }

        $data['position'] = $productModel->syncProducts($userUpgradeIds);

        $actionPhrase = new XenForo_Phrase('th_synchronising_infusionsoftapi');
        $typePhrase = new XenForo_Phrase('th_user_upgrades_infusionsoftapi');
        $status = sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

        return $data;
    }

    public function canCancel()
    {
        return true;
    }
}