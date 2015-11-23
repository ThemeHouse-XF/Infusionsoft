<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_InvoicePayment extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getInvoicePaymentById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getInvoicePaymentFields();

        $invoices = $this->query('InvoicePayment', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($invoices);
    }

    public function getInvoicePaymentsForInvoiceId($invoiceId)
    {
        $queryData = array(
            'InvoiceId' => $invoiceId
        );
        $selectedFields = $this->_getInvoicePaymentFields();

        return $this->query('InvoicePayment', 1, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getInvoicePaymentFields()
    {
        return array(
            'Id',
            'InvoiceId',
            'Amt',
            'PayDate',
            'PayStatus',
            'PaymentId',
            'SkipCommission'
        );
    }
}