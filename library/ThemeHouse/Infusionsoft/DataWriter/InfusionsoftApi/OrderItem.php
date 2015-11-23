<?php

class ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_OrderItem extends ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi
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
            'OrderItem' => array(
                'Id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'OrderId' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'ProductId' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'SubscriptionPlanId' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'ItemName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'Qty' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 1
                ),
                'CPU' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'PPU' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ItemDescription' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ItemType' => array(
                    'type' => self::TYPE_UINT,
                    'default' => 0
                ),
                'Notes' => array(
                    'type' => self::TYPE_STRING,
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
            'OrderItem' => $this->_getOrderItemModel()->getOrderItemById($id)
        );
    }

    /**
     *
     * @return ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_OrderItem
     */
    protected function _getOrderItemModel()
    {
        return $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_OrderItem');
    }
}