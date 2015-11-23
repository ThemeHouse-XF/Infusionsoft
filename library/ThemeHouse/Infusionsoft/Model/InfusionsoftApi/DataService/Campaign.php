<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Campaign extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getCampaignById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getCampaignFields();
        
        $campaigns = $this->query('Campaign', 1, 0, $queryData, $selectedFields, 'Id', true);
        
        return reset($contactGroupCategories);
    }

    public function getCampaigns()
    {
        $queryData = array();
        $selectedFields = $this->_getCampaignFields();
        return $this->query('Campaign', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getCampaignFields()
    {
        return array(
            'Id',
            'Name',
            'Status'
        );
    }
    
    public function getCampaignsForContact($contactId)
    {
        $queryData = array(
            'ContactId' => $contactId
        );
        $queryData = array();
        $selectedFields = $this->_getCampaigneeFields();
    
        return $this->query('Campaignee', 1000, 0, $queryData, $selectedFields, 'CampaignId', true);
    }
    
    protected function _getCampaigneeFields()
    {
        return array(
            'Campaign',
            'CampaignId',
            'ContactId',
            'Status'
        );
    }
}