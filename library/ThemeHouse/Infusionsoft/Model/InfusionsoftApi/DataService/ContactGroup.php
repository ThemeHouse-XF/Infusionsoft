<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroup extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getContactGroupById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getContactGroupFields();

        $contactGroups = $this->query('ContactGroup', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($contactGroups);
    }

    public function getContactGroupsForCategory($groupCategoryId)
    {
        $queryData = array(
            'GroupCategoryId' => $groupCategoryId
        );
        $selectedFields = $this->_getContactGroupFields();
        return $this->query('ContactGroup', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getContactGroupFields()
    {
        return array(
            'GroupCategoryId',
            'GroupDescription',
            'GroupName',
            'Id'
        );
    }

    public function getContactGroupsForContact($contactId)
    {
        $queryData = array(
            'ContactId' => $contactId
        );
        $selectedFields = $this->_getContactGroupAssignFields();

        return $this->query('ContactGroupAssign', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getContactGroupAssignFields()
    {
        return array(
            'ContactGroup',
            'ContactId',
            'DateCreated',
            'GroupId'
        );
    }
}