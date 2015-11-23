<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_InvoiceItem extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getInvoiceItemById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getInvoiceItemFields();

        $invoices = $this->query('InvoiceItem', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($invoices);
    }

    public function getInvoiceItemsForInvoiceId($invoiceId)
    {
        $queryData = array(
            'InvoiceId' => $invoiceId
        );
        $selectedFields = $this->_getInvoiceItemFields();

        return $this->query('InvoiceItem', 1, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getInvoiceItemFields()
    {
        return array(
            'Id',
            'InvoiceId',
            'OrderItemId',
            'InvoiceAmt',
            'Discount',
            'DateCreated',
            'Description',
            'CommissionStatus'
        );
    }
}