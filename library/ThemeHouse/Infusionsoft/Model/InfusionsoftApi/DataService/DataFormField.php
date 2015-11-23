<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormField extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    const CURRENCY = 3;

    const DATE = 13;

    const DATE_TIME = 14;

    const DAY_OF_WEEK = 9;

    const DRILLDOWN = 23;

    const EMAIL = 19;

    const MONTH = 8;

    const MULTI_SELECT = 17;

    const NAME = 10;

    const NUMBER = 12;

    const NUMBER_DECIMAL = 11;

    const PERCENT = 4;

    const PHONE_NUMBER = 1;

    const RADIO = 20;

    const DROPDOWN = 21;

    const SSN = 2;

    const STATE = 5;

    const TEXT = 15;

    const TEXTAREA = 16;

    const USER = 22;

    const WEBSITE = 18;

    const YEAR = 7;

    const YES_NO = 6;

    protected static $_dataFieldsForForms = array();

    public function getDataFormFieldByName($name)
    {
        $queryData = array(
            'Name' => $name
        );
        $selectedFields = $this->_getDataFormFieldFields();
        $fields = $this->query('DataFormField', 1, 0, $queryData, $selectedFields, 'Id', true);
        return reset($fields);
    }

    public function getDataFormFieldsForGroups(array $groupIds)
    {
        $queryData = array(
            'GroupId' => $groupIds
        );
        $selectedFields = $this->_getDataFormFieldFields();
        return $this->query('DataFormField', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    public function getDataFormFieldsForForm($formId, $useCache = true)
    {
        if ($useCache) {
            if (!empty(self::$_dataFieldsForForms[$formId])) {
                return self::$_dataFieldsForForms[$formId];
            }
        }
        
        $queryData = array(
            'FormId' => $formId
        );
        $selectedFields = $this->_getDataFormFieldFields();
        $fields = $this->query('DataFormField', 1000, 0, $queryData, $selectedFields, 'Id', true);
        
        if ($useCache) {
            self::$_dataFieldsForForms[$formId] = $fields;
        }
        
        return $fields;
    }

    protected function _getDataFormFieldFields()
    {
        return array(
            'DataType',
            'DefaultValue',
            'FormId',
            'GroupId',
            'Id',
            'Label',
            'ListRows',
            'Name',
            'Values'
        );
    }

    public function getFieldTitles()
    {
        $fieldTitles = array(
            'user_group_id' => new XenForo_Phrase('primary_user_group'),
            'secondary_group_ids' => new XenForo_Phrase('secondary_user_groups'),
            'state' => new XenForo_Phrase('user_state'),
            'last_activity' => new XenForo_Phrase('last_activity'),
            'infusionsoft_opt_in_th' => new XenForo_Phrase('th_marketing_opt_in_infusionsoftapi')
        );
        
        /* @var $userFieldModel XenForo_Model_UserField */
        $userFieldModel = XenForo_Model::create('XenForo_Model_UserField');
        
        $userFields = $userFieldModel->getUserFields();
        $userFields = $userFieldModel->prepareUserFields($userFields);
        
        foreach ($userFields as $fieldId => $field) {
            $fieldTitles['custom_field_' . $fieldId] = $field['title'];
        }
        
        return $fieldTitles;
    }
}