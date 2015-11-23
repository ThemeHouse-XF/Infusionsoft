<?php

class ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_OrderItem extends ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService
{

    public function getOrderItemById($id)
    {
        $queryData = array(
            'Id' => $id
        );
        $selectedFields = $this->_getOrderItemFields();

        $invoices = $this->query('OrderItem', 1, 0, $queryData, $selectedFields, 'Id', true);

        return reset($invoices);
    }

    public function getOrderItemsForOrderId($invoiceId)
    {
        $queryData = array(
            'OrderId' => $invoiceId
        );
        $selectedFields = $this->_getOrderItemFields();

        return $this->query('OrderItem', 1, 0, $queryData, $selectedFields, 'Id', true);
    }

    protected function _getOrderItemFields()
    {
        return array(
            'Id',
            'OrderId',
            'ProductId',
            'SubscriptionPlanId',
            'ItemName',
            'Qty',
            'CPU',
            'PPU',
            'ItemDescription',
            'ItemType',
            'Notes'
        );
    }
}