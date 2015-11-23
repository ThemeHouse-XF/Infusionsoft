<?php

class ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_Product extends ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi
{

    /**
     * Gets the fields that are defined for the table.
     * See parent for explanation.
     *
     * @return array
     */
    protected function _getFields()
    {
        return array(
            'Product' => array(
                'Id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'ProductName' => array(
                    'type' => self::TYPE_STRING,
                    'required' => true
                ),
                'ProductPrice' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'Sku' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShortDescription' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'Taxable' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'CountryTaxable' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'StateTaxable' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'CityTaxable' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'Weight' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'IsPackage' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'NeedsDigitalDelivery' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'Description' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'HideInStore' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'Status' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'TopHTML' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BottomHTML' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShippingTime' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'LargeImage' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'InventoryNotifiee' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'InventoryLimit' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                ),
                'Shippable' => array(
                    'type' => self::TYPE_UINT,
                    'default' => ''
                )
            )
        );
    }

    /**
     * Gets the actual existing data out of data that was passed in.
     * See parent for explanation.
     *
     * @param mixed
     *
     * @return array false
     */
    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'Id')) {
            return false;
        }

        return array(
            'Product' => $this->_getProductModel()->getProductById($id)
        );
    }

    /**
     *
     * @return ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product
     */
    protected function _getProductModel()
    {
        return $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_Product');
    }
}