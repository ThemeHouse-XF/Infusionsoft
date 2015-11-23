<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi
{

    public function addContact(array $contact)
    {
        $contact = array_filter($contact);
        
        if (!empty($contact)) {
            return $this->call('ContactService.add', array(
                $contact
            ));
        }
    }

    public function updateContact($contactId, array $contact)
    {
        $contact = array_filter($contact);
        
        if (!empty($contact)) {
            $contactId = $this->call('ContactService.update', 
                array(
                    new Zend_XmlRpc_Value_Integer($contactId),
                    $contact
                ));
            if (is_array($contactId) && !empty($contactId['error']) && !empty($contactId['error']['RecordNotFound'])) {
                return 0;
            }
        }
        
        return $contactId;
    }

    public function deleteContact($contactId)
    {
        /* @var $contactDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
        $contactDataModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');
        
        return $contactDataModel->deleteContact($contactId);
    }

    public function addContactToGroup($contactId, $contactGroupId)
    {
        return $this->call('ContactService.addToGroup', 
            array(
                $contactId,
                $contactGroupId
            ));
    }

    public function removeContactFromGroup($contactId, $contactGroupId)
    {
        return $this->call('ContactService.removeFromGroup', 
            array(
                $contactId,
                $contactGroupId
            ));
    }

    public function syncContacts(array $userIds)
    {
        $xenOptions = XenForo_Application::get('options');
        
        foreach ($userIds as $userId) {
            /* @var $userDw XenForo_DataWriter_User */
            $userDw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_SILENT);
            if ($userDw->setExistingData($userId)) {
                $contact = array(
                    'FirstName' => $userDw->get('username'),
                    'Username' => $userDw->get('username'),
                    'Email' => $userDw->get('email')
                );
                $contact = $this->getDataFieldsFromUserDw($contact, $userDw);
                if (!$userDw->get('infusionsoft_contact_id_th')) {
                    if (!$userDw->get('infusionsoft_opt_in_th') &&
                         $xenOptions->th_infusionsoftApi_exportOptedInUsersOnly) {
                        continue;
                    }
                    $userModel = $this->getModelFromCache('XenForo_Model_User');
                    $exportUserGroups = $xenOptions->th_infusionsoftApi_exportUserGroups;
                    $isMemberOfUserGroup = $userModel->isMemberOfUserGroup($userDw->getMergedData(), $exportUserGroups);
                    if (!$isMemberOfUserGroup) {
                        continue;
                    }
                    $contactId = $this->addContact($contact);
                    
                    if ($contactId) {
                        $userDw->set('infusionsoft_contact_id_th', $contactId);
                        $userDw->save();
                    }
                } else {
                    $contactId = $userDw->get('infusionsoft_contact_id_th');
                    
                    $newContactId = $this->updateContact($contactId, $contact);
                    
                    if ($contactId != $newContactId) {
                        if ($newContactId == 0) {
                            $newContactId = $this->addContact($contact);
                        }
                        $userDw->set('infusionsoft_contact_id_th', $newContactId);
                        $userDw->save();
                    }
                }
            }
        }
        
        return $userId;
    }

    public function getDataFieldsFromUserDw(array $contact, XenForo_DataWriter_User $dw, $updatedOnly = false)
    {
        $xenOptions = XenForo_Application::get('options');
        
        $formFieldOptions = $xenOptions->th_infusionsoftApi_formFields;
        
        $customFields = array();
        if ($updatedOnly) {
            $customFields = $dw->getUpdatedCustomFields();
        } elseif ($dw->get('custom_fields')) {
            $customFields = unserialize($dw->get('custom_fields'));
        }
        
        /* @var $dataFormFieldModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField */
        $dataFormFieldModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField');
        
        foreach ($formFieldOptions as $userFieldName => $dataFormFieldName) {
            if ($dataFormFieldName) {
                $value = '';
                $isUpdated = false;
                if (strlen($userFieldName) > strlen('custom_field_') &&
                     substr($userFieldName, 0, strlen('custom_field_')) == 'custom_field_') {
                    $fieldId = substr($userFieldName, strlen('custom_field_'));
                    if (isset($customFields[$fieldId])) {
                        $value = $customFields[$fieldId];
                        $isUpdated = true;
                    }
                } else {
                    $value = $dw->get($userFieldName);
                    $isUpdated = $dw->isChanged($userFieldName);
                }
                
                if ($updatedOnly && !$isUpdated) {
                    continue;
                }
                
                if (substr($dataFormFieldName, 0, 1) == '_') {
                    if (!isset($dataFormFields)) {
                        $dataFormFields = $dataFormFieldModel->getDataFormFieldsForForm(-1);
                    }
                    foreach ($dataFormFields as $dataFormField) {
                        if ($dataFormField['Name'] == substr($dataFormFieldName, 1)) {
                            switch ($dataFormField['DataType']) {
                                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::DATE:
                                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::DATE_TIME:
                                    $contact[$dataFormFieldName] = new Zend_XmlRpc_Value_DateTime($value);
                                    break;
                                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::TEXT:
                                case ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField::TEXTAREA:
                                    $contact[$dataFormFieldName] = (string) $value;
                                    break;
                                default:
                                // do nothing
                            }
                            break;
                        }
                    }
                } else {
                    $contact[$dataFormFieldName] = (string) $value;
                }
            }
        }
        
        return $contact;
    }

    public function updateUserContactId(array $user, $contactId)
    {
        $this->_getDb()->update('xf_user_profile', 
            array(
                'infusionsoft_contact_id_th' => $contactId
            ), 'user_id = ' . $this->_getDb()
                ->quote($user['user_id']));
    }
}