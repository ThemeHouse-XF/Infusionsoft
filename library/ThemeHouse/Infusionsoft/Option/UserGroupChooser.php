<?php

class ThemeHouse_Infusionsoft_Option_UserGroupChooser extends XenForo_Option_UserGroupChooser
{

    /**
     * Renders the user group chooser option as a <select>.
     *
     * @param XenForo_View $view View object
     * @param string $fieldPrefix Prefix for the HTML form field name
     * @param array $preparedOption Prepared option info
     * @param boolean $canEdit True if an "edit" link should appear
     *
     * @return XenForo_Template_Abstract Template object
     */
    public static function renderSelect(XenForo_View $view, $fieldPrefix, array $preparedOption, $canEdit)
    {
        return self::_render('th_option_list_option_multi_infusionsoftapi', $view, $fieldPrefix, $preparedOption,
            $canEdit);
    }
}