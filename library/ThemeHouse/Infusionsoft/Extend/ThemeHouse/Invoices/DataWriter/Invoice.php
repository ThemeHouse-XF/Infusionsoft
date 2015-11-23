<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_DataWriter_Invoice extends ThemeHouse_Invoices_DataWriter_Invoice
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_DataWriter_Invoice extends XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_DataWriter_Invoice
{

    /**
     *
     * @see ThemeHouse_Invoices_DataWriter_Invoice::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['invoice_th']['infusionsoft_invoice_id_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );

        return $fields;
    }

    /**
     *
     * @see ThemeHouse_Invoices_DataWriter_Invoice::_postSave()
     */
    protected function _postSave()
    {
        $db = $this->_db;

        if ($this->isInsert() && !$this->get('infusionsoft_invoice_id_th')) {
            /* @var $invoiceServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService */
            $invoiceServiceModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService');

            $invoiceServiceModel->pushInvoice($this->get('invoice_id'));
        }

        parent::_postSave();
    }
}