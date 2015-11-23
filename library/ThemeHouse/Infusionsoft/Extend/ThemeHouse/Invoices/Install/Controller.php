<?php
if (false) {

    class XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Install_Controller extends ThemeHouse_Invoices_Install_Controller
    {
    }
}

class ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Install_Controller extends XFCP_ThemeHouse_Infusionsoft_Extend_ThemeHouse_Invoices_Install_Controller
{

    /**
     *
     * @see ThemeHouse_Invoices_Install_Controller::_getTables()
     */
    protected function _getTables()
    {
        $tables = parent::_getTables();

        $tables['invoice_th'] = array_merge($tables['invoice_th'],
            $this->_getTableChangesForAddOn('ThemeHouse_Invoices', 'invoice_th'));

        return $tables;
    }
}