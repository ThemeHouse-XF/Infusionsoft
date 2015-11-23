<?php

class ThemeHouse_Infusionsoft_Deferred_PullInvoices extends XenForo_Deferred_Abstract
{

    public function execute(array $deferred, array $data, $targetRunTime, &$status)
    {
        $data = array_merge(array(
            'position' => 0,
            'batch' => 30
        ), $data);
        $data['batch'] = max(1, $data['batch']);

        /* @var $invoiceDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice */
        $invoiceDataModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');

        $invoices = $invoiceDataModel->getInvoicesInRange($data['position'], $data['batch']);
        if (sizeof($invoices) == 0) {
            return true;
        }

        $data['position'] = $invoiceDataModel->pullInvoices($invoices, $data['position']);

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