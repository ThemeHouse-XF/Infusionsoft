<?php

class ThemeHouse_Infusionsoft_Install_Controller extends ThemeHouse_Install
{

    protected $_resourceManagerUrl = 'http://xenforo.com/community/resources/infusionsoft-api-by-th.3176/';

    protected function _getTableChanges()
    {
        return array(
            'xf_user_profile' => array(
                'infusionsoft_contact_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'infusionsoft_last_updated_th' => 'int UNSIGNED NOT NULL DEFAULT 0',
                'infusionsoft_contact_group_ids_th' => 'varchar(255) NOT NULL DEFAULT \'\'',
                'infusionsoft_opt_in_th' => 'tinyint NOT NULL DEFAULT 0'
            ),
            'xf_user_upgrade' => array(
                'infusionsoft_product_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0'
            )
        );
    }

    protected function _getAddOnTableChanges()
    {
        return array(
            'ThemeHouse_Invoices' => array(
                'invoice_th' => array(
                    'infusionsoft_invoice_id_th' => 'int UNSIGNED NOT NULL DEFAULT 0'
                )
            )
        );
    }


    protected function _postInstall()
    {
        $addOn = $this->getModelFromCache('XenForo_Model_AddOn')->getAddOnById('YoYo_');
        if ($addOn) {
            $db->query("
                UPDATE xf_user_profile
                    SET infusionsoft_contact_id_th=infusionsoft_contact_id_waindigo, infusionsoft_last_updated_th=infusionsoft_last_updated_waindigo, infusionsoft_contact_group_ids_th=infusionsoft_contact_group_ids_waindigo, infusionsoft_opt_in_th=infusionsoft_opt_in_waindigo");
            $db->query("
                UPDATE xf_user_upgrade
                    SET infusionsoft_product_id_th=infusionsoft_product_id_waindigo");
        }
    }
}