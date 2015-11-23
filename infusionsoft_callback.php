<?php
$startTime = microtime(true);
$fileDir = dirname(__FILE__);

require ($fileDir . '/library/XenForo/Autoloader.php');
XenForo_Autoloader::getInstance()->setupAutoloader($fileDir . '/library');

XenForo_Application::initialize($fileDir . '/library', $fileDir);
XenForo_Application::set('page_start_time', $startTime);

$deps = new XenForo_Dependencies_Public();
$deps->preLoadData();

$input = new XenForo_Input(new Zend_Controller_Request_Http());
$contactId = $input->filterSingle('Id', XenForo_Input::UINT);

if ($contactId) {
    /* $contactDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
    $contactDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');

    $contactDataModel->processContact($contactId);
}