<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Account extends XenForo_ControllerPublic_Account
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Account extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_ControllerPublic_Account
{

    public function actionContactDetailsSave()
    {
        $GLOBALS['XenForo_ControllerPublic_Account'] = $this;
        
        return parent::actionContactDetailsSave();
    }

    /**
     *
     * @see ThemeHouse_UserUpgrades_Extend_XenForo_ControllerPublic_Account::actionPurchaseRedirect()
     */
    public function actionPurchaseRedirect()
    {
        $visitor = XenForo_Visitor::getInstance();
        $options = XenForo_Application::get('options');
        $upgrade = $this->getRequestedUpgrade();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId) {
            return parent::actionPurchaseRedirect();
        }
        
        if (!$upgrade['infusionsoft_product_id_th']) {
            return parent::actionPurchaseRedirect();
        }
        
        $this->_assertHasCreditCard($contactId, $upgrade);
        
        /* @var $invoiceServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService */
        $invoiceServiceModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService');
        
        $description = $upgrade['title'];
        $orderDate = XenForo_Application::$time;
        
        $invoiceId = $invoiceServiceModel->createBlankOrder($contactId, $description, $orderDate);
        
        if ($upgrade['length_unit'] && $upgrade['recurring']) {
            if ($upgrade['length_amount_trial'] && $upgrade['lengthUnitTrialPP']) {
                $upgrade['cost_amount'] = $upgrade['cost_amount_trial'];
            } elseif ($upgrade['cost_amount_trial'] + $upgrade['cost_amount']) {
                $upgrade['cost_amount'] = $upgrade['cost_amount_trial'] + $upgrade['cost_amount'];
            }
        }
        
        $xenOptions = XenForo_Application::get('options');
        
        $type = ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService::PRODUCT;
        
        if (!$invoiceServiceModel->addOrderItem($invoiceId, $upgrade['infusionsoft_product_id_th'], $type,
            $upgrade['cost_amount'], 1, $upgrade['title'])) {
            return parent::actionPurchaseRedirect();
        }
        
        /* @var $invoiceDataServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice */
        $invoiceDataServiceModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');
        
        $invoiceData = $invoiceDataServiceModel->getInvoiceById($invoiceId);
        
        $invoice = $invoiceDataServiceModel->pullInvoice($invoiceData);
        
        if ($upgrade['length_unit'] && $upgrade['recurring']) {
            $this->_createRecurringInvoiceForSubscription($upgrade);
        }
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
            XenForo_Link::buildPublicLink('account/pay-invoice-by-credit-card', array(), 
                array(
                    'invoice_id' => $invoice['invoice_id']
                )));
    }

    /**
     * Redirects to payment details page if no credit card.
     */
    protected function _assertHasCreditCard($contactId, array $upgrade)
    {
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCard = $creditCardModel->getCreditCardForContact($contactId);
        
        if (!$creditCard) {
            throw $this->responseException(
                $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
                    XenForo_Link::buildPublicLink('account/payment-details', array(), 
                        array(
                            'user_upgrade_id' => $upgrade['user_upgrade_id']
                        ))));
        }
    }

    protected function _createRecurringInvoiceForSubscription()
    {
        $upgrade = $this->getRequestedUpgrade();
        
        $visitor = XenForo_Visitor::getInstance();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        /* @var $contactModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact */
        $contactModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Contact');
        
        $contact = $contactModel->getContactById($contactId);
        if (!$contact) {
            return $this->responseNoPermission();
        }
        $toName = $contactModel->getFullNameFromContact($contact);
        
        list($userId, $username, $name) = $this->_getDefaultInvoiceUserDetails();
        
        $time = XenForo_Application::$time;
        switch ($upgrade['length_unit']) {
            case 'day':
                $time = mktime(0, 0, 0, date('m', $time), date('d', $time) + $upgrade['length_amount'], 
                    date('Y', $time));
                break;
            case 'month':
                $time = mktime(0, 0, 0, date('m', $time) + $upgrade['length_amount'], date('d', $time), 
                    date('Y', $time));
                break;
            case 'year':
                $time = mktime(0, 0, 0, date('m', $time), date('d', $time), 
                    date('Y', $time) + $upgrade['length_amount']);
                break;
        }
        
        $input = array(
            'user_id' => $userId,
            'username' => $username,
            'name' => $name,
            'to_user_id' => $visitor['user_id'],
            'to_username' => $visitor['username'],
            'to_name' => $toName,
            'invoice_date' => date("Y-m-d", $time),
            'length_unit' => $upgrade['length_unit'],
            'length_amount' => $upgrade['length_amount']
        );
        
        /* @var $dw ThemeHouse_Invoices_DataWriter_Recurring_Invoice */
        $dw = XenForo_DataWriter::create('ThemeHouse_Invoices_DataWriter_Recurring_Invoice');
        
        $dw->bulkSet($input);
        
        $invoiceItems[] = array(
            'description' => $upgrade['title'],
            'cost_amount' => $upgrade['cost_amount'],
            'quantity' => 1
        );
        $invoiceItemFields[][] = array(
            'field_name' => 'infusionsoft_product_id',
            'field_value' => $upgrade['infusionsoft_product_id_th']
        );
        
        $dw->setInvoiceItems($invoiceItems, $invoiceItemFields);
        $dw->save();
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

    public function actionPayInvoiceByCreditCard()
    {
        $visitor = XenForo_Visitor::getInstance();
        
        $invoiceId = $this->_input->filterSingle('invoice_id', XenForo_Input::INT);
        
        /* @var $invoiceModel ThemeHouse_Invoices_Model_Invoice */
        $invoiceModel = $this->_getInvoiceModel();
        $invoice = $invoiceModel->getInvoiceById($invoiceId);
        
        if (!$invoice) {
            return $this->responseError(new XenForo_Phrase('th_requested_invoice_not_found_invoices'), 404);
        }
        
        $infusionsoftInvoiceId = $invoice['infusionsoft_invoice_id_th'];
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId || !$infusionsoftInvoiceId) {
            return $this->responseNoPermission();
        }
        
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCards = $creditCardModel->getCreditCardsForContact($contactId);
        
        if (empty($creditCards)) {
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
                XenForo_Link::buildPublicLink('account/payment-details'));
        }
        
        $creditCard = reset($creditCards);
        
        /* @var $invoiceServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService */
        $invoiceServiceModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService');
        
        $return = $invoiceServiceModel->chargeInvoice($infusionsoftInvoiceId, '', $creditCard['Id']);
        
        if (in_array(strtolower($return['Code']), array(
            'approved',
            'skipped'
        ))) {
            /* @var $invoiceDataServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice */
            $invoiceDataServiceModel = $this->getModelFromCache(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');
            
            $invoiceData = $invoiceDataServiceModel->getInvoiceById($infusionsoftInvoiceId);
            
            /* @var $userModel XenForo_Model_User */
            $userModel = $this->getModelFromCache('XenForo_Model_User');
            
            $user = $userModel->getFullUserById($visitor['user_id']);
            
            /* @var $upgradeModel XenForo_Model_UserUpgrade */
            $upgradeModel = $this->getModelFromCache('XenForo_Model_UserUpgrade');
            
            $db = XenForo_Application::getDb();
            
            if ($db->update('invoice_th', array(
                'paid_state' => 'paid'
            ), 'infusionsoft_invoice_id_th = ' . $db->quote($infusionsoftInvoiceId))) {
                $db->update('invoice_th',
                    array(
                        'paid_date' => XenForo_Application::$time
                    ), 'infusionsoft_invoice_id_th = ' . $db->quote($infusionsoftInvoiceId));
                
                $db->beginTransaction();
                $upgrade = $invoiceDataServiceModel->processInvoice($invoiceData);
                
                $db->update('invoice_th',
                    array(
                        'payment_method' => 'infusionsoft'
                    ), 'infusionsoft_invoice_id_th = ' . $db->quote($infusionsoftInvoiceId));
                $db->commit();
            }
        }
        
        if (!empty($upgrade['redirect'])) {
            $redirect = XenForo_Link::buildPublicLink('account/upgrade-purchase', null, 
                array(
                    'upgrade_id' => $upgrade['user_upgrade_id']
                ));
        } elseif (!empty($upgrade)) {
            $redirect = XenForo_Link::buildPublicLink('account/upgrade-purchase');
        } else {
            $redirect = XenForo_Link::buildPublicLink('account/invoices');
        }
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirect);
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionPaymentDetails()
    {
        $visitor = XenForo_Visitor::getInstance();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId) {
            return $this->responseNoPermission();
        }
        
        $redirect = '';
        $userUpgradeId = $this->_input->filterSingle('user_upgrade_id', XenForo_Input::UINT);
        if ($userUpgradeId) {
            $redirect = XenForo_Link::buildPublicLink('account/purchase-confirm', array(), 
                array(
                    'upgrade_id' => $userUpgradeId
                ));
        }
        
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCards = $creditCardModel->getCreditCardsForContact($contactId);
        
        $viewParams = array(
            'creditCards' => $creditCards,
            
            'userUpgradeId' => $userUpgradeId,
            'redirect' => $redirect
        );
        
        return $this->_getWrapper('account', 'paymentDetails', 
            $this->responseView('ThemeHouse_Infusionsoft_ViewPublic_Account_PaymentDetails',
                'th_account_payment_details_infusionsoftapi', $viewParams));
    }

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionPaymentDetailsSave()
    {
        $this->_assertPostOnly();
        
        $visitor = XenForo_Visitor::getInstance();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId) {
            return $this->responseNoPermission();
        }
        
        $input = $this->_input->filter(
            array(
                'bill_name' => XenForo_Input::STRING,
                'bill_address1' => XenForo_Input::STRING,
                'bill_address2' => XenForo_Input::STRING,
                'bill_city' => XenForo_Input::STRING,
                'bill_state' => XenForo_Input::STRING,
                'bill_zip' => XenForo_Input::STRING,
                'bill_country' => XenForo_Input::STRING,
                'name_on_card' => XenForo_Input::STRING,
                'card_number' => XenForo_Input::STRING,
                'expiration_month' => XenForo_Input::STRING,
                'expiration_year' => XenForo_Input::STRING,
                'cvv2' => XenForo_Input::STRING,
                'card_type' => XenForo_Input::STRING
            ));
        
        $writer = XenForo_DataWriter::create('ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_CreditCard');
        $writer->bulkSet(
            array(
                'ContactId' => $contactId,
                'BillName' => $input['bill_name'],
                'BillAddress1' => $input['bill_address1'],
                'BillAddress2' => $input['bill_address2'],
                'BillCity' => $input['bill_city'],
                'BillState' => $input['bill_state'],
                'BillZip' => $input['bill_zip'],
                'BillCountry' => $input['bill_country'],
                'NameOnCard' => $input['name_on_card'],
                'CardNumber' => $input['card_number'],
                'ExpirationMonth' => $input['expiration_month'],
                'ExpirationYear' => $input['expiration_year'],
                'CVV2' => $input['cvv2'],
                'CardType' => $input['card_type']
            ));
        $writer->preSave();
        
        if ($dwErrors = $writer->getErrors()) {
            return $this->responseError($dwErrors);
        }
        
        $writer->save();
        
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCards = $creditCardModel->getCreditCardsForContact($contactId);
        
        if (empty($creditCards[$writer->get('Id')])) {
            return $this->responseError(new XenForo_Phrase('th_credit_card_invalid_or_expired_infusionsoftapi'));
        }
        
        $redirect = $this->_input->filterSingle('redirect', XenForo_Input::STRING);
        
        if (!$redirect && $this->_noRedirect()) {
            $viewParams = array(
                'creditCards' => $creditCards
            );
            
            return $this->responseView('ThemeHouse_Infusionsoft_ViewPublic_Account_PaymentDetails',
                'th_account_payment_details_infusionsoftapi',
                array(
                    'creditCards' => $creditCards
                ));
        }
        
        $redirect = $this->getDynamicRedirect(XenForo_Link::buildPublicLink('account/payment-details'));
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirect);
    }

    public function actionDeleteCreditCardConfirm()
    {
        $visitor = XenForo_Visitor::getInstance();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId) {
            return $this->responseNoPermission();
        }
        
        $id = $this->_input->filterSingle('id', XenForo_Input::UINT);
        
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCard = $creditCardModel->getCreditCardById($id);
        
        if (empty($creditCard) || $creditCard['ContactId'] != $contactId) {
            return $this->responseError(new XenForo_Phrase('th_requested_credit_card_not_found_infusionsoftapi'),
                404);
        }
        
        $viewParams = array(
            'creditCard' => $creditCard
        );
        
        return $this->_getWrapper('account', 'paymentDetails', 
            $this->responseView('XenForo_ViewPublic_Account_DeleteCreditCard', 
                'th_account_delete_credit_card_infusionsoftapi', $viewParams));
    }

    /**
     * Deletes the specified credit card
     *
     * @return XenForo_ControllerResponse_Redirect
     */
    public function actionDeleteCreditCard()
    {
        $this->_assertPostOnly();
        
        $visitor = XenForo_Visitor::getInstance();
        
        $contactId = $visitor['infusionsoft_contact_id_th'];
        
        if (!$contactId) {
            return $this->responseNoPermission();
        }
        
        $id = $this->_input->filterSingle('id', XenForo_Input::UINT);
        
        /* @var $creditCardModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard */
        $creditCardModel = $this->getModelFromCache(
            'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
        
        $creditCard = $creditCardModel->getCreditCardById($id);
        
        if (empty($creditCard) || $creditCard['ContactId'] != $contactId) {
            return $this->responseError(new XenForo_Phrase('th_requested_credit_card_not_found_infusionsoftapi'),
                404);
        }
        
        $creditCardModel->deleteCreditCard($id);
        
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, 
            XenForo_Link::buildPublicLink('account/payment-details'));
    }
}