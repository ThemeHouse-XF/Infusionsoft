<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getContactById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getContactFields();

        $contacts = $this->query('Contact', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($contacts);
    }

    public function getRecentlyUpdatedContacts()
    {
        $queryData = array();
        $selectedFields = $this->_getContactFields();

        return $this->query('Contact', 1000, 0, $queryData, $selectedFields, 'LastUpdated', false);
    }

    public function getContactsInRange($start, $limit)
    {
        $page = floor($start / 1000);

        $queryData = array();
        $selectedFields = $this->_getContactFields();

        $contacts = array();
        $query1 = $this->query('Contact', $limit, $page, $queryData, $selectedFields, 'Id');
        $i = 0;
        foreach ($query1 as $contact) {
            if ($i >= $start && $i < $limit + $start) {
                $contacts[$contact['Id']] = $contact;
                $i++;
            }
        }
        $query2 = array();
        if ($start + $limit > ($page + 1) * 1000) {
            $query2 = $this->query('Contact', $limit, $page + 1, $queryData, $selectedFields, 'Id');
        }
        foreach ($query2 as $contact) {
            if ($i < $limit + $start) {
                $contacts[$contact['Id']] = $contact;
                $i++;
            }
        }

        ksort($contacts);

        return $contacts;
    }

    public function getFullNameFromContact(array $contact)
    {
        $name = '';

        if (!empty($contact['FirstName'])) {
            $name = $contact['FirstName'] . ' ';
        }

        if ($name && !empty($contact['MiddleName'])) {
            $name .= $contact['MiddleName'] . ' ';
        }

        if (!empty($contact['LastName'])) {
            if (!$name && !empty($contact['Title'])) {
                $name = $contact['Title'] . ' ';
            }
            $name .= $contact['LastName'];
        }

        if (!$name && !empty($contact['Username'])) {
            $name = $contact['Username'];
        }

        return trim($name);
    }

    protected function _getContactFields()
    {
        return array(
            'Address1Type',
            'Address2Street1',
            'Address2Street2',
            'Address2Type',
            'Address3Street1',
            'Address3Street2',
            'Address3Type',
            'Anniversary',
            'AssistantName',
            'AssistantPhone',
            'BillingInformation',
            'Birthday',
            'City',
            'City2',
            'City3',
            'Company',
            'AccountId',
            'CompanyID',
            'ContactNotes',
            'ContactType',
            'Country',
            'Country2',
            'Country3',
            'CreatedBy',
            'DateCreated',
            'Email',
            'EmailAddress2',
            'EmailAddress3',
            'Fax1',
            'Fax1Type',
            'Fax2',
            'Fax2Type',
            'FirstName',
            'Groups',
            'Id',
            'JobTitle',
            'LastName',
            'LastUpdated',
            'LastUpdatedBy',
            'Leadsource',
            'LeadSourceId',
            'MiddleName',
            'Nickname',
            'OwnerID',
            'Password',
            'Phone1',
            'Phone1Ext',
            'Phone1Type',
            'Phone2',
            'Phone2Ext',
            'Phone2Type',
            'Phone3',
            'Phone3Ext',
            'Phone3Type',
            'Phone4',
            'Phone4Ext',
            'Phone4Type',
            'Phone5',
            'Phone5Ext',
            'Phone5Type',
            'PostalCode',
            'PostalCode2',
            'PostalCode3',
            'ReferralCode',
            'SpouseName',
            'State',
            'State2',
            'State3',
            'StreetAddress1',
            'StreetAddress2',
            'Suffix',
            'Title',
            'Username',
            'Validated',
            'Website',
            'ZipFour1',
            'ZipFour2',
            'ZipFour3'
        );
    }

    public function deleteContact($contactId)
    {
        return $this->delete('Contact', $contactId);
    }

    /**
     *
     * @param array $contacts
     * @param int $position
     */
    public function pullContacts(array $contacts, $position = 0, $targetRunTime = 0)
    {
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        $contactIds = XenForo_Application::arrayColumn($contacts, 'Id');

        if (!$contactIds) {
            return $position;
        }

        $contactUsers = $userModel->getUsersByContactIds($contactIds);

        $userContactIds = XenForo_Application::arrayColumn($contactUsers, 'infusionsoft_contact_id_th', 'user_id');

        $emails = XenForo_Application::arrayColumn($contacts, 'Email');
        $emails = array_unique(array_filter($emails));

        $fetchOptions = array(
            'join' => XenForo_Model_User::FETCH_USER_PROFILE
        );
        $emailUsers = $userModel->getUsersByEmails($emails, $fetchOptions);

        $userEmails = XenForo_Application::arrayColumn($emailUsers, 'email', 'user_id');

        $s = microtime(true);
        foreach ($contacts as $contactId => $contact) {
            $targetRunTime = $this->getTargetRunTime($targetRunTime);
            if ($targetRunTime && microtime(true) - $s > $targetRunTime) {
                break;
            }

            $position++;

            $user = in_array($contactId, $userContactIds) ? $contactUsers[array_search($contactId, $userContactIds)] : false;

            if (!$user && !empty($contact['Email'])) {
                $user = in_array($contact['Email'], $userEmails) ? $emailUsers[array_search($contact['Email'],
                    $userEmails)] : false;
                if ($user && !empty($user['infusionsoft_contact_id_th'])) {
                    continue;
                }
            }

            $this->pullContact($contact, $user);
        }

        return $position;
    }

    /**
     *
     * @param array $contact
     * @param array $user
     * @return array|boolean|null $user
     */
    public function pullContact(array $contact, $user = null)
    {
        $xenOptions = XenForo_Application::get('options');

        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        if ($user === null) {
            $user = $userModel->getUserByContactId($contact['Id']);
        }

        if (!$user && !empty($contact['Email'])) {
            $user = $userModel->getUserByEmail($contact['Email'],
                array(
                    'join' => XenForo_Model_User::FETCH_USER_PROFILE
                ));
            if ($user && !empty($user['infusionsoft_contact_id_th'])) {
                return;
            }
        }

        $lastUpdated = ThemeHouse_Infusionsoft_Helper_InfusionsoftApi::getDateAsTimestamp($contact['LastUpdated']);
        if ($user && !empty($user['infusionsoft_last_updated_th']) &&
             $user['infusionsoft_last_updated_th'] == $lastUpdated) {
            return $user;
        }

        if (!$user) {
            if (empty($contact['Groups'])) {
                return false;
            } else {
                $groups = explode(',', $contact['Groups']);
                if (!array_intersect($groups, $xenOptions->th_infusionsoftApi_importUserTags)) {
                    return false;
                }
            }
        }

        /* @var $writer XenForo_DataWriter_User */
        $writer = XenForo_DataWriter::create('XenForo_DataWriter_User');

        $writer->setOption(XenForo_DataWriter_User::OPTION_ADMIN_EDIT, true);

        if ($user) {
            $writer->setExistingData($user);
        }

        $writer->set('infusionsoft_contact_id_th', $contact['Id']);
        $writer->set('infusionsoft_last_updated_th', $lastUpdated);

        if (!$user) {
            if ($xenOptions->registrationDefaults) {
                $writer->bulkSet($xenOptions->registrationDefaults,
                    array(
                        'ignoreInvalidFields' => true
                    ));
            }

            $name = $this->getFullNameFromContact($contact);

            $input = array(
                'user_group_id' => XenForo_Model_User::$defaultRegisteredGroupId,
                'language_id' => XenForo_Visitor::getInstance()->get('language_id'),
                'user_state' => 'valid',
                'username' => $name
            );

            $i = 1;
            while ($userModel->getUserByName($input['username'])) {
                $input['username'] = $name . ' ' . $i;
                $i++;
            }

            $writer->bulkSet($input);

            $password = XenForo_Application::generateRandomString(8);
            $password = strtr($password,
                array(
                    'I' => 'i',
                    'l' => 'L',
                    '0' => 'O',
                    'o' => 'O'
                ));
            $password = trim($password, '_-');

            $writer->setPassword($password);
        }

        if (!empty($contact['Email'])) {
            $writer->set('email', $contact['Email']);
            if ($xenOptions->gravatarEnable && XenForo_Model_Avatar::gravatarExists($contact['Email'])) {
                $writer->set('gravatar', $contact['Email']);
            }
        } else {
            $writer->setOption(ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_User::OPTION_INFUSIONSOFT_API_IMPORT,
                true);
            $writer->set('email', '');
        }

        if (!empty($contact['Groups'])) {
            $writer->set('infusionsoft_contact_group_ids_th', $contact['Groups']);
        } else {
            $writer->set('infusionsoft_contact_group_ids_th', '');
        }

        $writer->save();

        $user = $writer->getMergedData();

        /* @var $promotionModel XenForo_Model_UserGroupPromotion */
        $promotionModel = $this->getModelFromCache('XenForo_Model_UserGroupPromotion');
        $promotionModel->updatePromotionsForUser($user);

        return $user;
    }

    public function processContact($contactId)
    {
        $contact = $this->getContactById($contactId);

        if (!$contact) {
            /* @var $userModel XenForo_Model_User */
            $userModel = $this->getModelFromCache('XenForo_Model_User');

            $user = $userModel->getUserByContactId($contactId);

            if ($user) {
                /* @var $dw XenForo_DataWriter_User */
                $dw = XenForo_DataWriter::create('XenForo_DataWriter_User', XenForo_DataWriter::ERROR_SILENT);
                if ($dw->setExistingData($user)) {
                    $dw->delete();
                }
            }

            return;
        }

        $this->pullContact($contact);

        if (XenForo_Application::$versionId > 1020000) {
            $addOns = XenForo_Application::get('addOns');
            $isInInstalled = !empty($addOns['ThemeHouse_Invoices']);
        } else {
            $isInInstalled = $this->getAddOnById('ThemeHouse_Invoices') ? true : false;
        }

        if ($isInInstalled) {
            /*
             * $invoiceModel
             * ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice
             */
            $invoiceModel = $this->getModelFromCache(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');

            $invoiceModel->processInvoicesForContact($contactId);
        }
    }
}