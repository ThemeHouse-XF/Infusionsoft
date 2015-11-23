<?php

class ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_CreditCard extends ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi
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
            'CreditCard' => array(
                'Id' => array(
                    'type' => self::TYPE_UINT,
                    'autoIncrement' => true
                ),
                'ContactId' => array(
                    'type' => self::TYPE_UINT,
                    'required' => true
                ),
                'BillName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'FirstName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'LastName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'PhoneNumber' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'Email' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillAddress1' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillAddress2' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillCity' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillState' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillZip' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'BillCountry' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipFirstName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipMiddleName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipLastName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipCompanyName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipPhoneNumber' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipAddress1' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipAddress2' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipCity' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipState' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipZip' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipCountry' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ShipName' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'NameOnCard' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'CardNumber' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ExpirationMonth' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'ExpirationYear' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'CVV2' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'CardType' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'StartDateMonth' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'StartDateYear' => array(
                    'type' => self::TYPE_STRING,
                    'default' => ''
                ),
                'MaestroIssueNumber' => array(
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
            'CreditCard' => $this->_getCreditCardModel()->getCreditCardById($id)
        );
    }

    /**
     *
     * @return ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard
     */
    protected function _getCreditCardModel()
    {
        return $this->getModelFromCache('ThemeHouse_Infusionsoft_Model_InfusionsoftApi_DataService_CreditCard');
    }
}