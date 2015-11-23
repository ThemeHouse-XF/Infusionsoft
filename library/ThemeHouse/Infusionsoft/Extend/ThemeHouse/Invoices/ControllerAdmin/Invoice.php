<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_ControllerAdmin_Invoice extends ThemeHouse_Invoices_ControllerAdmin_Invoice
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_ControllerAdmin_Invoice extends XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_ControllerAdmin_Invoice
{

    protected function _getDefaultInvoiceInputFields()
    {
        $inputFields = parent::_getDefaultInvoiceInputFields();

        $inputFields['infusionsoft_invoice_id_th'] = XenForo_Input::UINT;

        return $inputFields;
    }
}