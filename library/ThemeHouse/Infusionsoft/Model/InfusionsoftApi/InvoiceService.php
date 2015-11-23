<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi
{

    const SHIPPING = 1;

    const TAX = 2;

    const SERVICE_AND_MISC = 3;

    const PRODUCT = 4;

    const UPSELL_PRODUCT = 5;

    const FINANCE_CHARGE = 6;

    const SPECIAL = 7;

    const PROGRAM = 8;

    const SUBSCRIPTION_PLAN = 9;

    const SPECIAL_FREE_TRIAL_DAYS = 10;

    const SPECIAL_ORDER_TOTAL = 11;

    const SPECIAL_PRODUCT = 12;

    const SPECIAL_CATEGORY = 13;

    const SPECIAL_SHIPPING = 14;

    /**
     * Creates a one-time order with no added line items.
     *
     * @param int $contactId
     * @param string $description
     * @param int/string $orderDate
     * @param int $leadAffiliateId
     * @param int $saleAffiliateId
     * @return int $orderId
     */
    public function createBlankOrder($contactId, $description, $orderDate, $leadAffiliateId = 0, $saleAffiliateId = 0)
    {
        $order = array(
            $contactId,
            $description,
            new Zend_XmlRpc_Value_DateTime($orderDate),
            $leadAffiliateId,
            $saleAffiliateId
        );

        return $this->call('InvoiceService.createBlankOrder', $order);
    }

    /**
     * Adds a line item to an order.
     * This used to add a Product to an order as well as any other sort of
     * charge/discount.
     *
     * @param int $invoiceId
     * @param int $productId
     * @param int $type
     * @param string $price
     * @param int $quantity
     * @param string $description
     * @param string $notes
     * @return int $orderItemId
     */
    public function addOrderItem($invoiceId, $productId, $type, $price, $quantity, $description, $notes = '')
    {
        $orderItem = array(
            (int) $invoiceId,
            (int) $productId,
            (int) $type,
            (double) $price,
            (int) $quantity,
            $description,
            $notes
        );

        return $this->call('InvoiceService.addOrderItem', $orderItem);
    }

    /**
     * This will cause a credit card to be charged for the amount currently due
     * on an invoice.
     *
     * @param int $invoiceId
     * @param string $notes
     * @param int $creditCardId
     * @param number $merchantAccountId
     * @param string $bypassCommissions
     * @return array
     */
    public function chargeInvoice($invoiceId, $notes, $creditCardId, $merchantAccountId = null, $bypassCommissions = false)
    {
        $xenOptions = XenForo_Application::get('options');

        if (!$merchantAccountId) {
            $merchantAccountId = $xenOptions->th_infusionsoftApi_merchantAccountId;
        }

        $charge = array(
            (int) $invoiceId,
            (string) $notes,
            (int) $creditCardId,
            (int) $merchantAccountId,
            (boolean) $bypassCommissions
        );

        return $this->call('InvoiceService.chargeInvoice', $charge);
    }

    /**
     *
     * @param array $invoiceIds
     */
    public function pushInvoices(array $invoiceIds)
    {
        foreach ($invoiceIds as $invoiceId) {
            $this->pushInvoice($invoiceId);
        }

        return $invoiceId;
    }

    public function pushInvoice($invoiceId)
    {
        /* @var $invoiceItemModel ThemeHouse_Invoices_Model_Invoice_Item */
        $invoiceItemModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice_Item');

        /* @var $invoiceItemFieldModel ThemeHouse_Invoices_Model_Invoice_Item_Field */
        $invoiceItemFieldModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice_Item_Field');

        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        /* @var $invoiceDw ThemeHouse_Invoices_DataWriter_Invoice */
        $invoiceDw = XenForo_DataWriter::create('ThemeHouse_Invoices_DataWriter_Invoice',
            XenForo_DataWriter::ERROR_SILENT);
        if (!$invoiceDw->setExistingData($invoiceId)) {
            return;
        }
        if ($invoiceDw->get('infusionsoft_invoice_id_th')) {
            return;
        }

        $contactId = $userModel->getContactIdByUserId($invoiceDw->get('to_user_id'));
        $description = '';
        $orderDate = strtotime($invoiceDw->get('invoice_date'));

        $infusionsoftInvoiceId = $this->createBlankOrder($contactId, $description, $orderDate);

        if ($infusionsoftInvoiceId) {
            $invoiceDw->set('infusionsoft_invoice_id_th', $infusionsoftInvoiceId);
            $invoiceDw->save();

            $invoiceItems = $invoiceItemModel->getInvoiceItems(
                array(
                    'invoice_id' => $invoiceId
                ));

            foreach ($invoiceItems as $invoiceItemId => $invoiceItem) {
                $invoiceItemFields = $invoiceItemFieldModel->getInvoiceItemFields(
                    array(
                        'invoice_item_id' => $invoiceItemId
                    ));

                $productId = 0;
                foreach ($invoiceItemFields as $invoiceItemFieldId => $invoiceItemField) {
                    if ($invoiceItemField['field_name'] == 'infusionsoft_product_id') {
                        $productId = $invoiceItemField['field_value'];
                    }
                }

                if ($productId) {
                    $type = ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService::PRODUCT;
                } else {
                    $type = self::SERVICE_AND_MISC;
                }

                $dw = XenForo_DataWriter::create('ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_OrderItem');

                $dw->bulkSet(
                    array(
                        'OrderId' => $infusionsoftInvoiceId,
                        'ProductId' => $productId,
                        'ItemName' => $invoiceItem['description'],
                        'ItemType' => $type,
                        'PPU' => $invoiceItem['cost_amount'],
                        'Qty' => $invoiceItem['quantity']
                    ));
                $dw->save();

                $orderItemId = $dw->get('Id');

                /* @var $invoiceItemDw ThemeHouse_Invoices_DataWriter_Invoice_Item */
                $invoiceItemDw = XenForo_DataWriter::create('ThemeHouse_Invoices_DataWriter_Invoice_Item',
                    XenForo_DataWriter::ERROR_SILENT);
                $invoiceItemDw->setExistingData($invoiceItemId);
                $invoiceItemFields[] = array(
                    'field_name' => 'infusionsoft_order_item_id',
                    'field_value' => $orderItemId
                );
                $invoiceItemDw->setInvoiceItemFields($invoiceItemFields);
                $invoiceItemDw->save();
            }
        }
    }
}