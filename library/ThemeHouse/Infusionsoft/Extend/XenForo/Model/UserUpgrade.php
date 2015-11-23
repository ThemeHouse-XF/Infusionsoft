<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_Model_UserUpgrade extends XenForo_Model_UserUpgrade
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_Model_UserUpgrade extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_Model_UserUpgrade
{

    protected static $_infusionsoftInvoiceId = 0;

    /**
     * Returns an array containing the user upgrade ids found from the complete
     * result given the range specified, along with the total number of user
     * upgrades found.
     *
     * @param integer Find user upgrades with user_upgrade_id greater than...
     * @param integer Maximum user upgrades to return at once
     *
     * @return array
     */
    public function getUserUpgradeIdsInRange($start, $limit)
    {
        $db = $this->_getDb();

        return $db->fetchCol(
            $db->limit(
                '
        			SELECT user_upgrade_id
        			FROM xf_user_upgrade
        			WHERE user_upgrade_id > ?
        			ORDER BY user_upgrade_id
        		', $limit), $start);
    }

    /**
     * Gets the specified upgrade.
     *
     * @param integer $id
     *
     * @return array false
     */
    public function getUserUpgradeIdByProductId($id)
    {
        return $this->_getDb()->fetchOne(
            '
			SELECT user_upgrade_id
			FROM xf_user_upgrade
			WHERE infusionsoft_product_id_th = ?
		', $id);
    }

    /**
     *
     * @see ThemeHouse_Invoices_Extend_XenForo_Model_UserUpgrade::_createUserUpgradeInvoice()
     */
    protected function _createUserUpgradeInvoice($userId, array $upgrade, array $upgradeRecord)
    {
        if (self::$_infusionsoftInvoiceId) {
            /* @var $invoiceModel ThemeHouse_Invoices_Model_Invoice */
            $invoiceModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice');

            $invoiceId = $invoiceModel->getInvoiceIdByInfusionsoftOrderId(self::$_infusionsoftInvoiceId);

            if (!$invoiceId) {
                return;
            }

            /* @var $invoiceItemModel ThemeHouse_Invoices_Model_Invoice_Item */
            $invoiceItemModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice_Item');

            $invoiceItem = $invoiceItemModel->getInvoiceItem(
                array(
                    'invoice_id' => $invoiceId
                ));

            if (!$invoiceItem) {
                return;
            }

            /* @var $invoiceItemFieldModel ThemeHouse_Invoices_Model_Invoice_Item_Field */
            $invoiceItemFieldModel = $this->getModelFromCache('ThemeHouse_Invoices_Model_Invoice_Item_Field');

            $invoiceItemFields = $invoiceItemFieldModel->getInvoiceItemFields(
                array(
                    'invoice_item_id' => $invoiceItem['invoice_item_id']
                ));

            $invoiceItemFields[] = array(
                'field_name' => 'user_upgrade_record_id',
                'field_value' => $upgradeRecord['user_upgrade_record_id']
            );
            $invoiceItemFields[] = array(
                'field_name' => 'end_date',
                'field_value' => $upgradeRecord['end_date']
            );

            /* @var $dw ThemeHouse_Invoices_DataWriter_Invoice_Item */
            $dw = XenForo_DataWriter::create('ThemeHouse_Invoices_DataWriter_Invoice_Item');
            $dw->setExistingData($invoiceItem['invoice_item_id']);
            $dw->setInvoiceItemFields($invoiceItemFields);
            $dw->save();

            return $invoiceId;
        }

        return parent::_createUserUpgradeInvoice($userId, $upgrade, $upgradeRecord);
    }

    /**
     *
     * @param integer $invoiceId
     */
    public function setInfusionsoftInvoiceId($invoiceId)
    {
        self::$_infusionsoftInvoiceId = $invoiceId;
    }
}