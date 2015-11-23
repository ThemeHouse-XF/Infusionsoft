<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_User extends XenForo_DataWriter_User
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_User extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_User
{

    const OPTION_INFUSIONSOFT_API_IMPORT = 'infusionsoftApiImport';

    /**
     *
     * @see XenForo_DataWriter_User::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();
        
        $fields['xf_user_profile']['infusionsoft_contact_id_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );
        
        $fields['xf_user_profile']['infusionsoft_last_updated_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );
        
        $fields['xf_user_profile']['infusionsoft_contact_group_ids_th'] = array(
            'type' => self::TYPE_STRING,
            'default' => ''
        );
        
        $fields['xf_user_profile']['infusionsoft_opt_in_th'] = array(
            'type' => self::TYPE_INT,
            'default' => 0
        );
        
        return $fields;
    }

    /**
     *
     * @see XenForo_DataWriter_User::_getDefaultOptions()
     */
    protected function _getDefaultOptions()
    {
        $defaultOptions = parent::_getDefaultOptions();
        
        $defaultOptions[self::OPTION_INFUSIONSOFT_API_IMPORT] = false;
        
        return $defaultOptions;
    }

    /**
     *
     * @see XenForo_DataWriter_User::_verifyEmail()
     */
    protected function _verifyEmail(&$email)
    {
        if ($this->getOption(self::OPTION_INFUSIONSOFT_API_IMPORT) && $email === '') {
            return true;
        }
        
        return parent::_verifyEmail($email);
    }

    protected function _preSave()
    {
        if (!empty($GLOBALS['XenForo_ControllerAdmin_User'])) {
            /* @var $controller XenForo_ControllerAdmin_User */
            $controller = $GLOBALS['XenForo_ControllerAdmin_User'];
            
            $input = $controller->getInput()->filter(
                array(
                    'infusionsoft_contact_id_th' => XenForo_Input::UINT,
                    'infusionsoft_contact_id_th_shown' => XenForo_Input::UINT
                ));
            
            if ($input['infusionsoft_contact_id_th_shown']) {
                $this->set('infusionsoft_contact_id_th', $input['infusionsoft_contact_id_th']);
                $this->set('infusionsoft_last_updated_th', 0);
                $this->set('infusionsoft_contact_group_ids_th', '');
            }
        }
        
        if (!empty($GLOBALS['XenForo_ControllerPublic_Account'])) {
            $optInController = $GLOBALS['XenForo_ControllerPublic_Account'];
        } elseif (!empty($GLOBALS['XenForo_ControllerPublic_Register'])) {
            $optInController = $GLOBALS['XenForo_ControllerPublic_Register'];
        }
        
        /* @var $optInController XenForo_ControllerPublic_Abstract */
        if (!empty($optInController)) {
            $input = $optInController->getInput()->filter(
                array(
                    'infusionsoft_opt_in_th' => XenForo_Input::UINT,
                    'infusionsoft_opt_in_th_shown' => XenForo_Input::UINT
                ));
            
            if ($input['infusionsoft_opt_in_th_shown']) {
                if ($this->get('infusionsoft_opt_in_th') && !$input['infusionsoft_opt_in_th']) {
                    $this->set('infusionsoft_opt_in_th', -1);
                } elseif (!$this->get('infusionsoft_opt_in_th') && $input['infusionsoft_opt_in_th']) {
                    $this->set('infusionsoft_opt_in_th', 1);
                }
            }
        }
        
        parent::_preSave();
    }

    /**
     *
     * @see XenForo_DataWriter_User::_postSave()
     */
    protected function _postSave()
    {
        $db = $this->_db;
        
        $xenOptions = XenForo_Application::get('options');
        
        /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
        $contactServiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');
        
        if (!$this->get('infusionsoft_contact_id_th')) {
            /* @var $userModel XenForo_Model_User */
            $userModel = $this->_getUserModel();
            
            $exportUserGroups = $xenOptions->th_infusionsoftApi_exportUserGroups;
            
            $isMemberOfUserGroup = $userModel->isMemberOfUserGroup($this->getMergedData(), $exportUserGroups);
            if (!$this->isInsert()) {
                $isExistingMemberOfUserGroup = $userModel->isMemberOfUserGroup($this->getMergedExistingData(), 
                    $exportUserGroups);
            }
            
            $hasJustOptedIn = $this->isChanged('infusionsoft_opt_in_th') &&
                 $this->get('infusionsoft_opt_in_th') &&
                 $xenOptions->th_infusionsoftApi_exportOptedInUsersOnly;
            
            if ($isMemberOfUserGroup && ($this->isInsert() || $hasJustOptedIn || !$isExistingMemberOfUserGroup)) {
                $contact = array(
                    'FirstName' => (string) $this->get('username'),
                    'Username' => (string) $this->get('username'),
                    'Email' => (string) $this->get('email')
                );
                
                $contact = $contactServiceModel->getDataFieldsFromUserDw($contact, $this);
                
                $contactId = $contactServiceModel->addContact($contact);
                
                $this->_setPostSave('infusionsoft_contact_id_th', $contactId);
                $db->update('xf_user_profile', 
                    array(
                        'infusionsoft_contact_id_th' => $contactId
                    ), 'user_id = ' . $db->quote($this->get('user_id')));
                
                $this->_updateInfusionSoftUserGroupTags();
            }
        } elseif ($this->isUpdate() && $this->get('infusionsoft_contact_id_th')) {
            $contactId = $this->get('infusionsoft_contact_id_th');
            
            $contact = array();
            
            if ($this->isChanged('infusionsoft_contact_id_th') || $this->isChanged('username')) {
                if (empty($xenOptions->th_infusionsoftApi_formFields['custom_field_First_Name'])) {
                    $contact['FirstName'] = (string) $this->get('username');
                }
                $contact['Username'] = (string) $this->get('username');
            }
            
            if ($this->isChanged('infusionsoft_contact_id_th') || $this->isChanged('email')) {
                $contact['Email'] = (string) $this->get('email');
            }
            
            $contact = $contactServiceModel->getDataFieldsFromUserDw($contact, $this, 
                !$this->isChanged('infusionsoft_contact_id_th'));
            
            if ($contact) {
                $contactId = $contactServiceModel->updateContact($contactId, $contact);
                if ($contactId != $this->get('infusionsoft_contact_id_th')) {
                    $contactServiceModel->updateUserContactId($this->getMergedData(), $contactId);
                    $this->_setPostSave('infusionsoft_contact_id_th', $contactId);
                }
            }
            
            $this->_updateInfusionSoftUserGroupTags(!$this->isChanged('infusionsoft_contact_id_th'));
        }
        
        if ($this->isChanged('email') || $this->isChanged('infusionsoft_opt_in_th')) {
            /* @var $emailServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_EmailService */
            $emailServiceModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_EmailService');
            if ($this->get('infusionsoft_opt_in_th') == 1) {
                $emailServiceModel->optIn($this->get('email'), 'Opted in via XenForo');
            } elseif ($this->isChanged('infusionsoft_opt_in_th') &&
                 $this->get('infusionsoft_opt_in_th') == -1) {
                $emailServiceModel->optOut($this->get('email'), 'Opted out via XenForo');
            }
        }
        
        parent::_postSave();
    }

    public function getUpdatedCustomFields()
    {
        return $this->_updateCustomFields;
    }

    protected function _updateInfusionSoftUserGroupTags($updatedOnly = false)
    {
        $xenOptions = XenForo_Application::get('options');
        
        /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
        $contactServiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');
        
        $addedUserGroupIds = explode(',', $this->get('secondary_group_ids'));
        $addedUserGroupIds[] = $this->get('user_group_id');
        
        $removedUserGroupIds = array();
        if ($updatedOnly) {
            $newUserGroupIds = $addedUserGroupIds;
            
            $oldUserGroupIds = explode(',', $this->getExisting('secondary_group_ids'));
            $oldUserGroupIds[] = $this->getExisting('user_group_id');
            
            $addedUserGroupIds = array_diff($newUserGroupIds, $oldUserGroupIds);
            $removedUserGroupIds = array_diff($oldUserGroupIds, $newUserGroupIds);
        }
        
        $userGroupTagOptions = $xenOptions->th_infusionsoftApi_userGroupTags;
        
        $contactId = $this->get('infusionsoft_contact_id_th');
        
        foreach ($userGroupTagOptions as $userGroupId => $contactGroupId) {
            if (!$contactGroupId) {
                continue;
            }
            if (in_array($userGroupId, $addedUserGroupIds)) {
                $contactServiceModel->addContactToGroup($contactId, $contactGroupId);
            } elseif (in_array($userGroupId, $removedUserGroupIds)) {
                $contactServiceModel->removeContactFromGroup($contactId, $contactGroupId);
            }
        }
    }

    protected function _postDelete()
    {
        $xenOptions = XenForo_Application::get('options');
        
        if ($this->get('infusionsoft_contact_id_th') && $xenOptions->th_infusionsoftApi_deleteContacts) {
            /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
            $contactServiceModel = XenForo_Model::create(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');
            
            $contactServiceModel->deleteContact($this->get('infusionsoft_contact_id_th'));
        }
        
        parent::_postDelete();
    }
}