<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_DataFormTab extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getDataFormTabsForForm($formId)
    {
        $queryData = array(
        	'FormId' => $formId
        );
        $selectedFields = $this->_getDataFormTabFields();
        return $this->query('DataFormTab', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getDataFormTabFields()
    {
        return array(
            'FormId',
            'Id',
            'TabName'
        );
    }
}