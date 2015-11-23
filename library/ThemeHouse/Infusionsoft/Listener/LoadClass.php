<?php

class ThemeHouse_Infusionsoft_Listener_LoadClass extends ThemeHouse_Listener_LoadClass
{

    protected function _getExtendedClasses()
    {
        return array(
            'ThemeHouse_Infusionsoft' => array(
                'datawriter' => array(
                    'XenForo_DataWriter_User',
                    'XenForo_DataWriter_UserUpgrade',
                    'ThemeHouse_Invoices_DataWriter_Invoice'
                ),
                'controller' => array(
                    'XenForo_ControllerPublic_Account',
                    'ThemeHouse_Invoices_ControllerAdmin_Invoice',
                    'XenForo_ControllerAdmin_User',
                    'XenForo_ControllerPublic_Register'
                ),
                'helper' => array(
                    'XenForo_ControllerHelper_Account'
                ),
                'model' => array(
                    'XenForo_Model_UserUpgrade',
                    'XenForo_Model_User',
                    'ThemeHouse_Invoices_Model_Invoice'
                ),
                'installer_th' => array(
                    'ThemeHouse_Invoices_Install_Controller'
                ),
            ),
        );
    }

    public static function loadClassDataWriter($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Infusionsoft_Listener_LoadClass', $class, $extend, 'datawriter');
    }

    public static function loadClassController($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Infusionsoft_Listener_LoadClass', $class, $extend, 'controller');
    }

    public static function loadClassHelper($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Infusionsoft_Listener_LoadClass', $class, $extend, 'helper');
    }

    public static function loadClassModel($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Infusionsoft_Listener_LoadClass', $class, $extend, 'model');
    }

    public static function loadClassInstallerThemeHouse($class, array &$extend)
    {
        $extend = self::createAndRun('ThemeHouse_Infusionsoft_Listener_LoadClass', $class, $extend, 'installer_th');
    }
}