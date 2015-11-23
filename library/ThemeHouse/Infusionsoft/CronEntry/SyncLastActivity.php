<?php

class ThemeHouse_Infusionsoft_CronEntry_SyncLastActivity
{

    public static function runLastActivityCheck()
    {
        /* @var $userModel XenForo_Model_User */
        $userModel = XenForo_Model::create('XenForo_Model_User');

        $users = $userModel->getUsers(
            array(
                'last_activity' => array(
                    '>',
                    XenForo_Application::$time - 2 * 3600
                )
            ), array(
                'join' => XenForo_Model_User::FETCH_USER_PROFILE
            ));

        $xenOptions = XenForo_Application::get('options');

        $formFieldOptions = $xenOptions->th_infusionsoftApi_formFields;

        if (empty($formFieldOptions['last_activity'])) {
            return;
        }

        /* @var $dataFormFieldModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField */
        $dataFormFieldModel = XenForo_Model::create(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField');

        $dataFormField = $dataFormFieldModel->getDataFormFieldByName(substr($formFieldOptions['last_activity'], 1));

        if (!$dataFormField || !$dataFormField['Name']) {
            return;
        }

        $dataFormFieldName = '_' . $dataFormField['Name'];

        foreach ($users as $user) {
            $contact = array();

            $contactId = $user['infusionsoft_contact_id_th'];

            if (!$contactId) {
                continue;
            }

            switch ($dataFormField['DataType']) {
                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::DATE:
                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::DATE_TIME:
                    $contact[$dataFormFieldName] = new Zend_XmlRpc_Value_DateTime($user['last_activity']);
                    break;
                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::TEXT:
                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::TEXTAREA:
                    $contact[$dataFormFieldName] = (string) $user['last_activity'];
                    break;
                default:
                // do nothing
            }

            /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
            $contactServiceModel = XenForo_Model::create(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');

            if (!empty($contact)) {
                $newContactId = $contactServiceModel->updateContact($contactId, $contact);
                if ($contactId != $newContactId) {
                    $contactServiceModel->updateUserContactId($user, $newContactId);
                }
            }
        }
    }
}