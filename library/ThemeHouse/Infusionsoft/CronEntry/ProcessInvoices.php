<?php

class ThemeHouse_Infusionsoft_CronEntry_ProcessInvoices
{

    public static function processInvoices()
    {
        if (XenForo_Application::$versionId > 1020000) {
            $addOns = XenForo_Application::get('addOns');
            $isInInstalled = !empty($addOns['ThemeHouse_Invoices']);
        } else {
            $isInInstalled = $this->getAddOnById('ThemeHouse_Invoices') ? true : false;
        }

        if (!$isInInstalled) {
            return;
        }

        /* @var $invoiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice */
        $invoiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');

        $invoices = $invoiceModel->getPaidInvoices();

        $db = XenForo_Application::getDb();

        $db->update('invoice_th', array(
            'paid_date' => XenForo_Application::$time
        ), 'paid_state = \'paid\' AND paid_date = \'\'');
        $db->update('invoice_th',
            array(
                'paid_date' => '',
                'paid_state' => 'uncleared'
            ),
            'paid_date < ' . (XenForo_Application::$time - 60 * 60) .
                 ' AND paid_state = \'paid\' AND payment_method = \'\'');

        $unprocessedInvoiceIds = $invoiceModel->getUnprocessedInvoicesFromInvoiceIds(array_keys($invoices));

        foreach ($unprocessedInvoiceIds as $invoiceId) {
            $invoice = $invoices[$invoiceId];

            if (!$db->update('invoice_th', array(
                'paid_state' => 'paid'
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId))) {
                continue;
            }

            $db->update('invoice_th',
                array(
                    'paid_date' => XenForo_Application::$time
                ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId));

            $db->beginTransaction();
            $invoiceModel->processInvoice($invoice);

            $db->update('invoice_th', array(
                'payment_method' => 'infusionsoft'
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId));
            $db->commit();
        }
    }
}