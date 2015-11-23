<?php

class ThemeHouse_Infusionsoft_Option_Username
{

    /**
     * Renders username textbox.
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
        /* @var $userModel XenForo_Model_User */
        $userModel = XenForo_Model::create('XenForo_Model_User');

        if ($preparedOption['option_value']) {
            $user = $userModel->getUserById($preparedOption['option_value']);
            if ($user) {
                $preparedOption['option_value'] = $user['username'];
            } else {
                $preparedOption['option_value'] = '';
            }
        }

        $preparedOption['formatParams'] = array(
            'placeholder' => new XenForo_Phrase('user_name') . '...',
            'type' => 'search'
        );

        return XenForo_ViewAdmin_Helper_Option::renderOptionTemplateInternal(
            'th_option_list_option_user_infusionsoftapi', $view, $fieldPrefix, $preparedOption, $canEdit);
    }

    public static function verifyUsername(&$value, XenForo_DataWriter $dw, $fieldName)
    {
        if (!$value) {
            return true;
        }

        /* @var $userModel XenForo_Model_User */
        $userModel = XenForo_Model::create('XenForo_Model_User');

        $user = $userModel->getUserByName($value);

        if ($user) {
            $value = $user['user_id'];
            return true;
        }

        $value = '';
        return false;
    }
}