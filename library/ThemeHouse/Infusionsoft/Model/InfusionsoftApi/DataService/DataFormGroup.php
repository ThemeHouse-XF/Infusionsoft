<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormGroup extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getDataFormGroupsForTabs(array $tabIds)
    {
        $queryData = array(
            'TabId' => $tabIds,
        );
        $selectedFields = $this->_getDataFormGroupFields();
        return $this->query('DataFormGroup', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getDataFormGroupFields()
    {
        return array(
            'Id',
            'Name',
            'TabId'
        );
    }
}