<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getInvoiceById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getInvoiceFields();

        $invoices = $this->query('Invoice', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($invoices);
    }

    public function getInvoicesInRange($start, $limit)
    {
        $page = floor($start / 1000);

        $queryData = array();
        $selectedFields = $this->_getInvoiceFields();

        $invoices = array();
        $query1 = $this->query('Invoice', $limit, $page, $queryData, $selectedFields, 'Id');
        $i = 0;
        foreach ($query1 as $invoice) {
            if ($i >= $start && $i < $limit + $start) {
                $invoices[$invoice['Id']] = $invoice;
                $i++;
            }
        }
        $query2 = array();
        if ($start + $limit > ($page + 1) * 1000) {
            $query2 = $this->query('Invoice', $limit, $page + 1, $queryData, $selectedFields, 'Id');
        }
        foreach ($query2 as $invoice) {
            if ($i < $limit + $start) {
                $invoices[$invoice['Id']] = $invoice;
                $i++;
            }
        }

        ksort($invoices);

        return $invoices;
    }

    public function getPaidInvoices()
    {
        $queryData = array(
            'PayStatus' => 1
        );
        $selectedFields = $this->_getInvoiceFields();
        return $this->query('Invoice', 1000, 0, $queryData, $selectedFields, 'Id');
    }

    public function getPaidInvoicesForContact($contactId)
    {
        $queryData = array(
            'ContactId' => $contactId,
            'PayStatus' => 1
        );
        $selectedFields = $this->_getInvoiceFields();
        return $this->query('Invoice', 1000, 0, $queryData, $selectedFields, 'Id');
    }

    public function getUnprocessedInvoicesFromInvoiceIds(array $invoiceIds)
    {
        if (empty($invoiceIds)) {
            return array();
        }

        $db = $this->_getDb();

        $invoiceIds = $db->fetchCol(
            '
            SELECT infusionsoft_invoice_id_th
            FROM invoice_th
            WHERE infusionsoft_invoice_id_th IN (' . $db->quote($invoiceIds) . ')
                AND paid_state != \'paid\'
        ');

        return $invoiceIds;
    }

    public function processInvoicesForContact($contactId)
    {
        $invoices = $this->getPaidInvoicesForContact($contactId);

        $invoiceIds = array();
        foreach ($invoices as $invoice) {
            $invoiceIds[] = $invoice['Id'];
        }

        $unprocessedInvoiceIds = $this->getUnprocessedInvoicesFromInvoiceIds($invoiceIds);

        $db = $this->_getDb();

        foreach ($unprocessedInvoiceIds as $invoiceId) {
            $invoice = $invoices[$invoiceId];

            if (!$db->update('invoice_th', array(
                'paid_state' => 'paid'
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId))) {
                continue;
            }

            $db->update('invoice_th', array(
                'paid_date' => XenForo_Application::$time
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId));

            $db->beginTransaction();
            $this->processInvoice($invoice);

            $db->update('invoice_th', array(
                'payment_method' => 'infusionsoft'
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($invoiceId));
            $db->commit();
        }
    }

    public function processInvoice(array $invoice)
    {
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        if (empty($invoice['ContactId'])) {
            return false;
        }

        $userId = $userModel->getUserIdByContactId($invoice['ContactId']);

        $user = $userModel->getFullUserById($userId);

        if (empty($user)) {
            return false;
        }

        /* @var $upgradeModel XenForo_Model_UserUpgrade */
        $upgradeModel = $this->getModelFromCache('XenForo_Model_UserUpgrade');

        if (empty($invoice['ProductSold'])) {
            return false;
        }

        $userUpgradeId = $upgradeModel->getUserUpgradeIdByProductId($invoice['ProductSold']);

        $upgrade = $upgradeModel->getUserUpgradeById($userUpgradeId);

        if (empty($upgrade)) {
            return false;
        }

        $upgradeRecord = $upgradeModel->getActiveUserUpgradeRecord($user['user_id'], $upgrade['user_upgrade_id']);
        if ($upgradeRecord) {
            $upgradeRecordId = $upgradeRecord['user_upgrade_record_id'];
        }

        $paymentAmountPassed = (round($invoice['TotalPaid'], 2) == round($upgrade['cost_amount'], 2));

        if ($upgradeRecord && $upgradeRecord['extra']) {
            $extra = unserialize($upgradeRecord['extra']);
            $cost = $extra['cost_amount'];

            $paymentAmountPassed = $paymentAmountPassed || (round($invoice['TotalPaid'], 2) == round($cost, 2));
        }

        if (!$paymentAmountPassed) {
            return false;
        }

        $upgradeModel->setInfusionsoftInvoiceId($invoice['Id']);
        $upgradeModel->upgradeUser($user['user_id'], $upgrade);

        return $upgrade;
    }

    /**
     *
     * @param array $invoices
     * @param int $position
     */
    public function pullInvoices(array $invoices, $position)
    {
        list($userId, $username, $name) = $this->_getDefaultInvoiceUserDetails();

        foreach ($invoices as $invoiceId => $invoice) {
            $position++;

            $this->pullInvoice($invoice, $userId, $username, $name);
        }

        return $position;
    }

    protected function _getDefaultInvoiceUserDetails($userId = 0, $username = '', $name = '')
    {
        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        $xenOptions = XenForo_Application::get('options');

        if (!$userId) {
            $userId = $xenOptions->th_infusionsoftApi_invoiceUserId;
            $name = $xenOptions->th_infusionsoftApi_invoiceName;
            $username = '';
        }

        if ($userId && !$username || !$name) {
            /* @var $userModel XenForo_Model_User */
            $userModel = $this->getModelFromCache('XenForo_Model_User');
            $user = $userModel->getUserById($userId);
            if ($user) {
                if (!$name) {
                    $name = $user['username'];
                }
                $username = $user['username'];
            }
        }

        return array(
            $userId,
            $username,
            $name
        );
    }

    public function pullInvoice(array $invoice, $userId = 0, $username = '', $name = '')
    {
        list($userId, $username, $name) = $this->_getDefaultInvoiceUserDetails();

        $invoiceId = $invoice['Id'];

        /* @var $userModel XenForo_Model_User */
        $userModel = $this->getModelFromCache('XenForo_Model_User');

        /* @var $contactModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
        $contactModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');

        /* @var $invoiceModel ThemeHouse_Invoices_Model_Invoice */
        $invoiceModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice');

        /* @var $invoiceItemModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_InvoiceItem */
        $invoiceItemModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_InvoiceItem');

        $existingInvoiceId = $invoiceModel->getInvoiceIdByInfusionsoftOrderId($invoiceId);

        if ($existingInvoiceId) {
            return;
        }

        if (!$userId || !$name) {
            return;
        }

        $contact = $contactModel->getContactById($invoice['ContactId']);

        if (!$contact) {
            return;
        }

        $toName = $contactModel->getFullNameFromContact($contact);

        $toUser = $userModel->getUserByContactId($invoice['ContactId']);

        if (!$toUser) {
            return;
        }

        $input = array(
            'user_id' => $userId,
            'username' => $username,
            'name' => $name,
            'to_user_id' => $toUser['user_id'],
            'to_username' => $toUser['username'],
            'to_name' => $toName,
            'invoice_date' => date("Y-m-d", strtotime($invoice['DateCreated'])),
            'infusionsoft_invoice_id_th' => $invoice['Id']
        );

        $writer = XenForo_DataWriter::create('ThemeHouse_Invoices_DataWriter_Invoice');
        $writer->bulkSet($input);

        /* @var $orderItemModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_OrderItem */
        $orderItemModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_OrderItem');
        $orderItems = $orderItemModel->getOrderItemsForOrderId($invoiceId);

        $invoiceItems = array();
        $invoiceItemFields = array();
        foreach ($orderItems as $orderItemId => $orderItem) {
            $invoiceItems[] = array(
                'description' => $orderItem['ItemDescription'],
                'cost_amount' => $orderItem['PPU'],
                'quantity' => 1
            );
            $itemFields[] = array(
                'field_name' => 'infusionsoft_order_item_id',
                'field_value' => $orderItemId
            );
            if ($orderItem['ProductId']) {
                $itemFields[] = array(
                    'field_name' => 'infusionsoft_product_id',
                    'field_value' => $orderItem['ProductId']
                );
            }
            $invoiceItemFields[] = $itemFields;
        }

        $writer->setInvoiceItems($invoiceItems, $invoiceItemFields);

        $writer->save();

        return $writer->getMergedData();
    }

    protected function _getInvoiceFields()
    {
        return array(
            'Id',
            'ContactId',
            'JobId',
            'DateCreated',
            'InvoiceTotal',
            'TotalPaid',
            'TotalDue',
            'PayStatus',
            'CreditStatus',
            'RefundStatus',
            'PayPlanStatus',
            'AffiliateId',
            'LeadAffiliateId',
            'PromoCode',
            'InvoiceType',
            'Description',
            'ProductSold',
            'Synced'
        );
    }
}