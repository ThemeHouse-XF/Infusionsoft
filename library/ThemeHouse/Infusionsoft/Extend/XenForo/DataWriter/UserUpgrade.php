<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_UserUpgrade extends XenForo_DataWriter_UserUpgrade
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_UserUpgrade extends XFCP_ThemeHouse_Infusionsoft_Extend_XenForo_DataWriter_UserUpgrade
{

    /**
     *
     * @see XenForo_DataWriter_User::_getFields()
     */
    protected function _getFields()
    {
        $fields = parent::_getFields();

        $fields['xf_user_upgrade']['infusionsoft_product_id_th'] = array(
            'type' => self::TYPE_UINT,
            'default' => 0
        );

        return $fields;
    }

    /**
     *
     * @see XenForo_DataWriter_UserUpgrade::_postSave()
     */
    protected function _postSave()
    {
        $db = $this->_db;

        if ($this->isInsert() && !$this->get('infusionsoft_product_id_th')) {
            $product = array(
                'ProductName' => $this->get('title'),
                'ProductPrice' => $this->get('cost_amount'),
                'HideInStore' => true,
                'Status' => true
            );

            /* @var $productDw ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_Product */
            $productDw = XenForo_DataWriter::create('ThemeHouse_Infusionsoft_DataWriter_InfusionsoftApi_Product',
                XenForo_DataWriter::ERROR_SILENT);
            $productDw->bulkSet($product);
            $productDw->save();

            $productId = $productDw->get('Id');

            $this->_setPostSave('infusionsoft_product_id_th', $productId);
            $db->update('xf_user_upgrade',
                array(
                    'infusionsoft_product_id_th' => $productId
                ), 'user_upgrade_id = ' . $db->quote($this->get('user_upgrade_id')));
        }

        parent::_postSave();
    }
}