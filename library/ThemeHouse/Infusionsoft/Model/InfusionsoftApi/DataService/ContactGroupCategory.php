<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_ContactGroupCategory extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getContactGroupCategoryById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getContactGroupCategoryFields();
        
        $contactGroupCategories = $this->query('ContactGroupCategory', 1, 0, $queryData, $selectedFields, 'Id', true);
        
        return reset($contactGroupCategories);
    }

    public function getContactGroupCategories()
    {
        $queryData = array();
        $selectedFields = $this->_getContactGroupCategoryFields();
        return $this->query('ContactGroupCategory', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getContactGroupCategoryFields()
    {
        return array(
            'CategoryDescription',
            'CategoryName',
            'Id'
        );
    }
}