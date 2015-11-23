<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getCreditCardById($id)
    {
        $queryData = array(
            'Id' => $id,
            'Status' => 3
        );
        $selectedFields = $this->_getCreditCardFields();

        $creditCards = $this->query('CreditCard', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($creditCards);
    }

    public function getCreditCardsForContact($contactId)
    {
        $queryData = array(
            'ContactId' => $contactId,
            'Status' => 3
        );
        $selectedFields = $this->_getCreditCardFields();
        return $this->query('CreditCard', 1000, 0, $queryData, $selectedFields, 'Id', true);
    }

    public function getCreditCardForContact($contactId)
    {
        $queryData = array(
            'ContactId' => $contactId,
            'Status' => 3
        );
        $selectedFields = $this->_getCreditCardFields();
        $creditCards = $this->query('CreditCard', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($creditCards);
    }

    protected function _getCreditCardFields()
    {
        return array(
            'Id',
            'ContactId',
            'BillName',
            'FirstName',
            'LastName',
            'PhoneNumber',
            'Email',
            'BillAddress1',
            'BillAddress2',
            'BillCity',
            'BillState',
            'BillZip',
            'BillCountry',
            'ShipFirstName',
            'ShipMiddleName',
            'ShipLastName',
            'ShipCompanyName',
            'ShipPhoneNumber',
            'ShipAddress1',
            'ShipAddress2',
            'ShipCity',
            'ShipState',
            'ShipZip',
            'ShipCountry',
            'ShipName',
            'NameOnCard',
            'Last4',
            'ExpirationMonth',
            'ExpirationYear',
            'Status',
            'CardType',
            'StartDateMonth',
            'StartDateYear',
            'MaestroIssueNumber'
        );
    }

    public function deleteCreditCard($id)
    {
        /* @var $productServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ProductService */
        $productServiceModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ProductService');

        return $productServiceModel->deactivateCreditCard($id);
    }
}