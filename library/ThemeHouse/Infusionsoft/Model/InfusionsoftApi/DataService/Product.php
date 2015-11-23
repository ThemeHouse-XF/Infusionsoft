<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getProductById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getProductFields();

        $creditCards = $this->query('Product', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($creditCards);
    }

    protected function _getProductFields()
    {
        return array(
            'Id',
            'ProductName',
            'ProductPrice',
            'Sku',
            'ShortDescription',
            'Taxable',
            'CountryTaxable',
            'StateTaxable',
            'CityTaxable',
            'Weight',
            'IsPackage',
            'NeedsDigitalDelivery',
            'Description',
            'HideInStore',
            'Status',
            'TopHTML',
            'BottomHTML',
            'ShippingTime',
            'LargeImage',
            'InventoryNotifiee',
            'InventoryLimit',
            'Shippable'
        );
    }

    public function syncProducts(array $userUpgradeIds)
    {
        foreach ($userUpgradeIds as $userUpgradeId) {
            /* @var $userUpgradeDw XenForo_DataWriter_UserUpgrade */
            $userUpgradeDw = XenForo_DataWriter::create('XenForo_DataWriter_UserUpgrade',
                XenForo_DataWriter::ERROR_SILENT);
            if ($userUpgradeDw->setExistingData($userUpgradeId)) {
                if (!$userUpgradeDw->get('infusionsoft_product_id_th')) {
                    $product = array(
                        'ProductName' => $userUpgradeDw->get('title'),
                        'ProductPrice' => $userUpgradeDw->get('cost_amount'),
                        'HideInStore' => true,
                        'Status' => true
                    );

                    /* @var $productDw ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_Product */
                    $productDw = XenForo_DataWriter::create(
                        'ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_Product', XenForo_DataWriter::ERROR_SILENT);
                    $productDw->bulkSet($product);
                    $productDw->save();

                    $productId = $productDw->get('Id');

                    if ($productId) {
                        $userUpgradeDw->set('infusionsoft_product_id_th', $productId);
                        $userUpgradeDw->save();
                    }
                }
            }
        }

        return $userUpgradeId;
    }
}