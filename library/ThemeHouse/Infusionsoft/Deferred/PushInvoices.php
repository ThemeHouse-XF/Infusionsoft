<?php

class ThemeHouse_Infusionsoft_Deferred_PushInvoices extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 30
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        /* @var $invoiceModel ThemeHouse_Invoices_Model_Invoice */
        $invoiceModel = XenForo_Model::create('ThemeHouse_Invoices_Model_Invoice');

        /* @var $invoiceServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService */
        $invoiceServiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService');

        $invoiceIds = $invoiceModel->getInvoiceIdsInRange($data['position'], $data['batch']);
        if (sizeof($invoiceIds) == 0) {
            return true;
        }

        $data['position'] = $invoiceServiceModel->pushInvoices($invoiceIds);

        $actionPhrase = new XenForo_Phrase('th_synchronising_infusionsoftapi');
        $typePhrase = new XenForo_Phrase('th_invoices_infusionsoftapi');
        $status = sprintf('%s... %s (%s)', $actionPhrase, $typePhrase, XenForo_Locale::numberFormat($data['position']));

        return $data;
    }

    public function canCancel()
    {
        return true;
    }
}