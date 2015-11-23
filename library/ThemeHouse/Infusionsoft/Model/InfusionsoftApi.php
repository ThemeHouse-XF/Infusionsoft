<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi extends XenForo_Model
{

    /**
     * XML RPC client object
     *
     * @var Zend_XmlRpc_Client
     */
    protected $_xmlRpcClient = null;

    protected function _getXmlRpcClient()
    {
        if ($this->_xmlRpcClient === null) {
            $this->_xmlRpcClient = $this->_connect();
        }

        return $this->_xmlRpcClient;
    }

    protected function _connect()
    {
        $xenOptions = XenForo_Application::get('options');

        $appName = $xenOptions->th_infusionsoftApi_appName;

        if (!$appName) {
            return false;
        }

        $client = new Zend_XmlRpc_Client('https://' . $appName . '.infusionsoft.com/api/xmlrpc');

        $client->getHttpClient()->setConfig(
            array(
                'sslcert' => XenForo_Application::getInstance()->getRootDir() .
                     'library/ThemeHouse/InfusionsoftApi/infusionsoft.pem'
            ));

        return $client;
    }

    public function call($service, array $params = array())
    {
        $client = $this->_getXmlRpcClient();

        if (!$client) {
            return array();
        }

        if ($service != 'DataService.getTemporaryKey') {
            $key = XenForo_Application::get('options')->th_infusionsoftApi_key;
            array_unshift($params, $key);
        }

        try {
            $result = $client->call($service, $params);
        } catch (Zend_XmlRpc_Client_FaultException $e) {
            $message = $e->getMessage();

            preg_match('#^\[([A-z]*)\](.*)$#', $message, $matches);
            if ($matches) {
                return array(
                    'error' => array(
                        $matches[1] => $matches[2]
                    )
                );
            }
            XenForo_Error::logException($e, false);
            return array();
        }

        return $result;
    }

    public function getTargetRunTime($targetRunTime = null)
    {
        if ($targetRunTime === null) {
            $targetRunTime = XenForo_Application::getConfig()->rebuildMaxExecution;
        }

        if ($targetRunTime < 0) {
            $targetRunTime = 0;
        } else
            if ($targetRunTime > 0 && $targetRunTime < 2) {
                $targetRunTime = 2;
            }

        return $targetRunTime;
    }
}