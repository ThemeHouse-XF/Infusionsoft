<?php

class ThemeHouse_Infusionsoft_Option_InvoicesInstalledOnly
{

    /**
     * Renders text box only if Invoices by ThemeHouse is installed.
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderTextBox(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        if (XenForo_Application::$versionId > 1020000) {
            $addOns = XenForo_Application::get('addOns');
            $isInvoicesInstalled = !empty($addOns['ThemeHouse_Invoices']);
        } else {
            $addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
            $isInvoicesInstalled = $addOnModel->getAddOnById('ThemeHouse_Invoices') ? true : false;
        }
        
        if ($isInvoicesInstalled) {
            return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal('option_list_option_textbox', $view, 
                $fieldPrefix, $preparedOption, $canEdit);
        }
    }

    /**
     * Renders username text box only if Invoices by ThemeHouse is installed.
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderUsernameTextBox(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        if (XenForo_Application::$versionId > 1020000) {
            $addOns = XenForo_Application::get('addOns');
            $isInvoicesInstalled = !empty($addOns['ThemeHouse_Invoices']);
        } else {
            $addOnModel = XenForo_Model::create('XenForo_Model_AddOn');
            $isInvoicesInstalled = $addOnModel->getAddOnById('ThemeHouse_Invoices') ? true : false;
        }
        
        if ($isInvoicesInstalled) {
            return ThemeHouse_Infusionsoft_Option_Username::renderUsernameTextBox($view, $fieldPrefix, $preparedOption,
                $canEdit);
        }
    }
}