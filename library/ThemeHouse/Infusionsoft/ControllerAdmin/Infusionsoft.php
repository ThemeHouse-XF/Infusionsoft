<?php

class ThemeHouse_Infusionsoft_ControllerAdmin_Infusionsoft extends XenForo_ControllerAdmin_Abstract
{

    public function actionIndex()
    {
        return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('infusionsoft/sync-contacts', null));
    }

    public function actionPushContacts()
    {
        $success = $this->_input->filterSingle('success', XenForo_Input::UINT);

        if ($success) {
            $viewParams = array();

            return $this->responseView('ThemeHouse_Infusionsoft_ViewAdmin_Infusionsoft_PushContacts',
                'th_sync_success_infusionsoftapi', $viewParams);
        }

        $data = array();

        $redirectTarget = XenForo_Link::buildAdminLink('infusionsoft/push-contacts', null,
            array(
                'success' => 1
            ));

        if (XenForo_Application::$versionId > 1020000) {
            XenForo_Application::defer('ThemeHouse_Infusionsoft_Deferred_PushContacts', $data, null, true);

            $this->_request->setParam('redirect', $redirectTarget);

            return $this->responseReroute('XenForo_ControllerAdmin_Tools', 'run-deferred');
        } else {
            /* @var $userModel XenForo_Model_User */
            $userModel = $this->getModelFromCache('XenForo_Model_User');

            $userIds = $userModel->getUserIdsInRange(0, 10000);

            /* @var $contactServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService */
            $contactServiceModel = $this->getModelFromCache(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_ContactService');

            $contactServiceModel->syncContacts($userIds);

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirectTarget);
        }
    }
    
    public function actionPullContacts()
    {
        $success = $this->_input->filterSingle('success', XenForo_Input::UINT);
    
        if ($success) {
            $viewParams = array();
    
            return $this->responseView('ThemeHouse_Infusionsoft_ViewAdmin_Infusionsoft_PullContacts',
                'th_sync_success_infusionsoftapi', $viewParams);
        }
    
        $data = array();
    
        $redirectTarget = XenForo_Link::buildAdminLink('infusionsoft/pull-contacts', null,
            array(
                'success' => 1
            ));
    
        if (XenForo_Application::$versionId > 1020000) {
            XenForo_Application::defer('ThemeHouse_Infusionsoft_Deferred_PullContacts', $data, null, true);
    
            $this->_request->setParam('redirect', $redirectTarget);
    
            return $this->responseReroute('XenForo_ControllerAdmin_Tools', 'run-deferred');
        } else {
            // TODO Add support for XenForo 1.1 and below
            
            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirectTarget);
        }
    }

    public function actionSyncUserUpgrades()
    {
        $success = $this->_input->filterSingle('success', XenForo_Input::UINT);

        if ($success) {
            $viewParams = array();

            return $this->responseView('ThemeHouse_Infusionsoft_ViewAdmin_Infusionsoft_SyncUserUpgrades',
                'th_sync_success_infusionsoftapi', $viewParams);
        }

        $redirectTarget = XenForo_Link::buildAdminLink('infusionsoft/sync-user-upgrades', null,
            array(
                'success' => 1
            ));

        if (XenForo_Application::$versionId > 1020000) {
            $data = array();

            XenForo_Application::defer('ThemeHouse_Infusionsoft_Deferred_PushUserUpgrades', $data, null, true);

            $this->_request->setParam('redirect', $redirectTarget);
        } else {
            /* @var $userModel XenForo_Model_UserUpgrade */
            $userUpgradeModel = $this->getModelFromCache('XenForo_Model_UserUpgrade');

            $userUpgradeIds = $userUpgradeModel->getUserUpgradeIdsInRange(0, 10000);

            /* @var $productModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product */
            $productModel = $this->getModelFromCache(
                'ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product');

            $productModel->syncProducts($userUpgradeIds);

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirectTarget);
        }

        return $this->responseReroute('XenForo_ControllerAdmin_Tools', 'run-deferred');
    }

    public function actionPushInvoices()
    {
        $this->_routeMatch->setSections('pushInvoices');

        $success = $this->_input->filterSingle('success', XenForo_Input::UINT);

        if ($success) {
            $viewParams = array();

            return $this->responseView('ThemeHouse_Infusionsoft_ViewAdmin_Infusionsoft_PushInvoices',
                'th_sync_success_infusionsoftapi', $viewParams);
        }

        $redirectTarget = XenForo_Link::buildAdminLink('infusionsoft/push-invoices', null,
            array(
                'success' => 1
            ));

        if (XenForo_Application::$versionId > 1020000) {
            $data = array();

            XenForo_Application::defer('ThemeHouse_Infusionsoft_Deferred_PushInvoices', $data, null, true);

            $this->_request->setParam('redirect', $redirectTarget);
        } else {
            /* @var $invoiceModel ThemeHouse_Invoices_Model_Invoice */
            $invoiceModel = XenForo_Model::create('ThemeHouse_Invoices_Model_Invoice');

            $invoiceIds = $invoiceModel->getInvoiceIdsInRange(0, 10000);

            /* @var $invoiceServiceModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService */
            $invoiceServiceModel = XenForo_Model::create('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_InvoiceService');

            $invoiceServiceModel->pushInvoices($invoiceIds);

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirectTarget);
        }

        return $this->responseReroute('XenForo_ControllerAdmin_Tools', 'run-deferred');
    }

    public function actionPullInvoices()
    {
        $this->_routeMatch->setSections('pullInvoices');

        $success = $this->_input->filterSingle('success', XenForo_Input::UINT);

        if ($success) {
            $viewParams = array();

            return $this->responseView('ThemeHouse_Infusionsoft_ViewAdmin_Infusionsoft_PullInvoices',
                'th_sync_success_infusionsoftapi', $viewParams);
        }

        $redirectTarget = XenForo_Link::buildAdminLink('infusionsoft/pull-invoices', null,
            array(
                'success' => 1
            ));

        if (XenForo_Application::$versionId > 1020000) {
            $data = array();

            XenForo_Application::defer('ThemeHouse_Infusionsoft_Deferred_PullInvoices', $data, null, true);

            $this->_request->setParam('redirect', $redirectTarget);
        } else {
            /* @var $invoiceDataModel ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice */
            $invoiceDataModel = $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Invoice');

            $invoices = $invoiceDataModel->getInvoicesInRange(0, 1000);

            $invoiceDataModel->pullInvoices($invoices);

            return $this->responseRedirect(XenForo_ControllerResponse_Redirect::SUCCESS, $redirectTarget);
        }

        return $this->responseReroute('XenForo_ControllerAdmin_Tools', 'run-deferred');
    }
}